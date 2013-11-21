<html>
<body>
<?php
/**
 *@author Jacob Zadnik <jacobszadnik@gmail.com>
 *@packate com.pagr.server
 *@license Proprietary
 *@version 1.2.0
 */

include_once(dirname(__FILE__))."/../pagr_db.php";

$STARTTIME = time();

function timeX60() {
    $ACTUALTIMEPASSED = time() - $STARTTIME;
    $NEWTIME = ($STARTTIME+($ACTUALTIMEPASSED*60));

    return $NEWTIME;
}

class Table {    
    public function makeOccupied($DATABASE,$TABLEID) {
        $DATABASE->query("UPDATE table_t 
                          SET isOccupied=1
                          WHERE table_id=$TABLEID;");
    }

    public function makeUnoccupied($DATABASE,$TABLEID) {
        $DATABASE->query("UPDATE table_t 
                          SET isOccupied=0
                          WHERE table_id=$TABLEID;");
    }
}

class TableGroup {
    public $TABLE;

    public function __construct() {
        $this->TABLE = new Table();
    }

    public function makeOccupied($DATABASE,$TABLEGROUPID) {
        $DATABASE->query("UPDATE tablegroups_t
                          SET is_occupied=1
                          WHERE tablegroup_id=$TABLEGROUPID;");
        $TABLESINGROUP_T = $DATABASE->query("SELECT table_id 
                                           FROM tablegroup_table_mapping_t
                                           WHERE tablegroup_id=$TABLEGROUPID;");
        while ( $TABLESINGROUP_R = mysqli_fetch_array($TABLESINGROUP_T) ) {
             $TABLESINGROUP_V = $TABLESINGROUP_R['table_id'];
             $this->TABLE->makeOccupied($DATABASE,$TABLESINGROUP_V);
        }
    }
    public function makeUnoccupied($DATABASE,$TABLEGROUPID) {
        $DATABASE->query("UPDATE tablegroups_t
                          SET is_occupied=0
                          WHERE tablegroup_id=$TABLEGROUPID;");
        $TABLESINGROUP_T = $DATABASE->query("SELECT table_id 
                                           FROM tablegroup_table_mapping_t
                                           WHERE tablegroup_id=$TABLEGROUPID;");
        while ( $TABLESINGROUP_R = mysqli_fetch_array($TABLESINGROUP_T) ) {
             $TABLESINGROUP_V = $TABLESINGROUP_R['table_id'];
             $this->TABLE->makeUnoccupied($DATABASE,$TABLESINGROUP_V);
        }
    }
}

class Restaurant {
    public $DATABASE; public $TABLE;
    public $TABLEGROUP;

    public function __construct() {
        $this->DATABASE = get_pagr_db_connection();
        $this->TABLE = new Table();
        $this->TABLEGROUP = new TableGroup();
    }

    public function avgMealTimeDB($PARTYSIZE) {
        $NumRows = 0;
        $ReturnValue = 0;
        $WaitTimesForSize_t = $this->DATABASE->query("SELECT wait_time FROM wait_times_t WHERE group_size=$PARTYSIZE;");


        while ( $WaitTimesForSize_r = mysqli_fetch_array($WaitTimesForSize_t) ) {
            ++$NumRows;
            $WaitTimesForSize_v = $WaitTimesForSize_r['wait_time'];
            $ReturnValue+=$WaitTimesForSize_v;
        }
        //divide $ReturnValue by the number of values to get the average
        $ReturnValue = $ReturnValue/$NumRows;

        return $ReturnValue;
    }

    public function seatGroupDB($PARTYID,$TABLEGROUPID) {
        //associate the table group with the party
        $this->DATABASE->query("INSERT INTO patron_tablegroup_mapping_t
                                       (patron_id, tablegroup_id, time_seated)
                                VALUES ($PARTYID, $TABLEGROUPID,100);");
        $this->TABLEGROUP->makeOccupied($this->DATABASE, $TABLEGROUPID);
    }

    public function unseatGroupDB($PARTYID) {
        //see where the party was seated
        $TABLEGROUPID_T = $this->DATABASE->query("SELECT tablegroup_id
                                                  FROM patron_tablegroup_mapping_t
                                                  WHERE patron_id=$PARTYID;");
        $TABLEGROUPID_R = mysqli_fetch_array($TABLEGROUPID_T);
        $TABLEGROUPID_V = $TABLEGROUPID_R['tablegroup_id'];

        //unseat the group
        $this->TABLEGROUP->makeUnoccupied($this->DATABASE, $TABLEGROUPID_V);

        //remove the association between the table and the group
        $this->DATABASE->query("DELETE FROM `pagr_s`.`patron_tablegroup_mapping_t` WHERE `patron_id`=$PARTYID;");
    }

    public function findBestTableGroupForSeatingDB($PARTYID,&$RETURNEDTABLEGROUP) {       
        $TableGroupWithShortestWaitTime = NULL;
        $ShortestExpectedWait = 86400;
        $CurrentExpectedWait = NULL;

        $PartySize_t = $this->DATABASE->query("SELECT party_size FROM patrons_t WHERE patron_id=$PARTYID;");
        $PartySize_r = mysqli_fetch_array($PartySize_t);
        $PartySize_v = $PartySize_r['party_size'];
        $TableSize = $PartySize_v;
        //see if there are TableGroups of size $TableSize
        while ( $TableSize<10 ) {
            $TableGroups_t = $this->DATABASE->query("SELECT tablegroup_id FROM tablegroups_t WHERE size=$TableSize;");
            while ( $TableGroups_r = mysqli_fetch_array($TableGroups_t) ) {
                $TableGroups_v = $TableGroups_r['tablegroup_id'];
                //check to see if the table group is occupied
                //if it is occupied, go to the next table group
                $TableGroupOccupied_t = $this->DATABASE->query("SELECT is_occupied FROM tablegroups_t WHERE tablegroup_id=$TableGroups_v;");
                $TableGroupOccupied_r = mysqli_fetch_array($TableGroupOccupied_t);
                $TableGroupOccupied_v = $TableGroupOccupied_r['is_occupied'];
                if ( $TableGroupOccupied_v ) { //if the group is occupied lets calculate its expected wait time
                    //figure out how many people are sitting at the table group
                    $PartyAtTableGroup_t = $this->DATABASE->query("SELECT patron_id FROM patron_tablegroup_mapping_t WHERE tablegroup_id=$TableGroups_v;");
                    $PartyAtTableGroup_r = mysqli_fetch_array($PartyAtTableGroup_t);
                    $PartyAtTableGroup_v = $PartyAtTableGroup_r['patron_id'];
                  
                    $NumberOfPeople_t = $this->DATABASE->query("SELECT party_size FROM patrons_t WHERE patron_id=$PartyAtTableGroup_v;");
                    $NumberOfPeople_r = mysqli_fetch_array($NumberOfPeople_t);
                    $NumberOfPeople_v = $NumberOfPeople_r['party_size'];
                    
                    //calculate their expected meal end time
                    //first get the expected end time for the tablegroup
                    $MealStart_t = $this->DATABASE->query("SELECT time_seated FROM patron_tablegroup_mapping_t WHERE tablegroup_id=$TableGroups_v;");
                    $MealStart_r = mysqli_fetch_array($MealStart_t);
                    $MealStart_v = $MealStart_r['time_seated'];
                    //calculate the average meal length for a party of the size of the one currently at the table group
                    $AverageLength = $this->avgMealTimeDB($NumberOfPeople_v);
                    //find the difference between now and the expected meal end time
                    $CurrentExpectedWait = (($MealStart_v+$AverageLength)-150);
                    //if it is less than the current table group with the shortest wait time, make this
                    //the table group with the shortest wait time.
                    if ( $CurrentExpectedWait < $ShortestExpectedWait ) {
                        $ShortestExpectedWait = $CurrentExpectedWait;
                        $TableGroupWithShortestWaitTime = $TableGroups_v;
                    }
                    continue;
                }
                else { //if the table group is not occupied, start to look at its tables
                    $TablesInGroup_t = $this->DATABASE->query("SELECT table_id FROM tablegroup_table_mapping_t WHERE tablegroup_id=$TableGroups_v;");
                    while ( $TablesInGroup_r = mysqli_fetch_array($TablesInGroup_t) ) {
                        $TablesInGroup_v = $TablesInGroup_r['table_id'];
                        //get the table information
                        $TableOccupied_t = $this->DATABASE->query("SELECT isOccupied FROM table_t WHERE table_id=$TablesInGroup_v;");
                        $TableOccupied_r = mysqli_fetch_array($TableOccupied_t);
                        $TableOccupied_v = $TableOccupied_r['isOccupied'];
                        if ( $TableOccupied_v ) { //the table in the table group is occupied
                            continue 2;
                        }
                    }
                    //all tables in group must be open
                    //so lets see if there is enough time to eat before any reservations!

                    //return the ID for the TableGroup
                    $RETURNEDTABLEGROUP=$TableGroups_v;
                    return 0;
                }
            }
            ++$TableSize;
        }
        if ( isset($TableGroupWithShortestWaitTime) ) {
            $RETURNEDTABLEGROUP = $TableGroupWithShortestWaitTime;
            return $ShortestExpectedWait;
        }
        else {
            return NULL;
        }
    }

    public function makeReservationDB($PARTY) {
        //get the party size given the party ID
        $PartySize_t = $this->DATABASE->query("SELECT party_size FROM patrons_t WHERE patron_id=$PARTY;");
        $PartySize_r = mysqli_fetch_array($PartySize_t);
        $PartySize_v = $PartySize_r['party_size'];
        //get the party's reservation time
        $ReservationTime_t = $this->DATABASE->query("SELECT DISTINCT 
                                        EXTRACT(YEAR from reservation_time) AS RES_YEAR, 
                                        EXTRACT(MONTH from reservation_time) AS RES_MONTH, 
                                        EXTRACT(DAY from reservation_time) AS RES_DAY, 
                                        EXTRACT(HOUR from reservation_time) AS RES_HOUR, 
                                        EXTRACT(MINUTE from reservation_time) AS RES_MINUTE 
                                        FROM patrons_t WHERE patron_id=$PARTY;");

        $ReservationTime_r = mysqli_fetch_array($ReservationTime_t);
        $MINUTE = $ReservationTime_r["RES_MINUTE"];
        $HOUR = $ReservationTime_r["RES_HOUR"];
        $MONTH = $ReservationTime_r["RES_MONTH"];
        $DAY = $ReservationTime_r["RES_DAY"];
        $YEAR = $ReservationTime_r["RES_YEAR"];

        $MINUTE = (int) $MINUTE;
        $HOUR = (int) $HOUR;
        $MONTH = (int) $MONTH;
        $DAY = (int) $DAY;
        $YEAR = (int) $YEAR;

        $ReservationTime_v = mktime($HOUR,$MINUTE,0,$MONTH,$DAY,$YEAR);
        //now that we know the party size, lets estimate how long they will need to eat
        $ExpectedMealTime = $this->avgMealTimeDB($PartySize_v); 
        //now we know the table group must be able to hold that many or more people
        //lets look for the table groups that can
        while ( $PartySize_v<=10 ) {
            //get the table groups of $PartySize
            $TableGroups_t = $this->DATABASE->query("SELECT tablegroup_id FROM tablegroups_t WHERE size=$PartySize_v;");
            while ( $TableGroups_r = mysqli_fetch_array($TableGroups_t) ) {
                $TableGroups_v = $TableGroups_r['tablegroup_id'];
                //look at the reservations for that table group
                $Reservations_t = $this->DATABASE->query("SELECT reservation_time, expected_finish_time 
                                                FROM tablegroup_reservations_t 
                                                WHERE tablegroup_id=$TableGroups_v AND expected_finish_time>=$ReservationTime_v 
                                                ORDER BY reservation_time;");
                if ( $Reservations_t->num_rows != 0 ) {
                    $Reservations_r1 = mysqli_fetch_array($Reservations_t);
                    while ( $Reservations_r2 = mysqli_fetch_array($Reservations_t) ) {
                        //get the expected end time for R1
                        $Reservations_v1 = $Reservations_r1['expected_finish_time'];
                        //get the start time for R2
                        $Reservations_v2 = $Reservations_r2['reservation_time'];

                        //let's see if the gap is big enough
                        if ( ($Reservations_v2-$Reservations_v1) > $ExpectedMealTime ) {
                            //we know we have enough time to make the reservation!
                            $FinishTime = $ReservationTime_v+$ExpectedMealTime;
                            $query = $this->DATABASE->query("INSERT INTO tablegroup_reservations_t (tablegroup_id, reservation_time, expected_finish_time) 
                                               VALUES ($TableGroups_v, $ReservationTime_v, $FinishTime);");
                            return 1;
                        }
                        $Reservations_r1 = $Reservations_r2;
                    }
                }
                else {
                    $FinishTime = $ReservationTime_v+$ExpectedMealTime;
                    $query = $this->DATABASE->query("INSERT INTO tablegroup_reservations_t (tablegroup_id, reservation_time, expected_finish_time) 
                                        VALUES ($TableGroups_v, $ReservationTime_v, $FinishTime);");
                    return 1;
                }
            }
            ++$PartySize_v;
        }
        return 0;
    }
}

$RestaurantObject = new Restaurant();

?>
</body>
</html>
