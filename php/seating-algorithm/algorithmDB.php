<html><body>

<?php
include "pagr_db.php";
$PAGR_database = get_pagr_db_connection();
if(mysqli_connect_errno($PAGR_database))
{
    $good = "Guest Connection Failed";
}
echo $good . "<br>";
/** 
 *@author Jacob Zadnik <jacobszadnik@gmail.com>
 *@package com.pagr.server
 *@license proprietary
 *@version 0.6.0
 */

$startTime = time();  //Global variable to hold a reference to the start time
            // used by timeX60() to calculate accelerated time

function timeX($FACTOR) {
    $dif = time() - $startTime;
    return ($startTime+($dif*$FACTOR));
}

class Table {

    public function makeOccupied($DB) {
    /**
     *PURPOSE:
     *  To set a table as occupied
     *
     *PRECONDITIONS:
     *  - The parameter DB is a reference to the database
     *
     *POSTCONDITIONS:
     *  No parameters or global variables are modified
     *
     *RETURN VALUES:
     *  Void
     *
     */
        $result = $DB->query("UPDATE table_t SET isOccupied=1 WHERE table_id=".$this->ID.";");
    }

    public function makeUnoccupied($DB) {
    /**
     *PURPOSE:
     *  to set a table as open
     *
     *PRECONDITIONS:
     *  - The parameter DB is a reference to the database
     *
     *POSTCONDITIONS:
     *  No parameters or global varables are modified
     *
     *RETURN VALUES:
     *  Void
     *
     */
        $result = $DB->query("UPDATE table_t SET isOccupied=0 WHERE table_id=".$this->ID.";");
    }
}

class TableGroup {

    public function makeOccupied($DB,$TABLEGROUP,$PARTY) {
    /**
     *PURPOSE:
     *  To set a table group and all tables in it as occupied
     *
     *PRECONDITIONS:
     *  - The parameter $DB is a reference to the database
     *  - The parameter TABLEGROUP is the table group you would like to make occupied
     *  - The parameter PARTY is the patron id for the party you are seating
     *
     *POSTCONDITIONS:
     *  No parameters or global variables are modified
     *
     *RETURN VALUES:
     *  Void
     *
     */
        $result = $DB->query("UPDATE tablegroups_t SET is_occupied=1 WHERE tablegroup_id=".$TABLEGROUP.";");
        $result = $DB->query("UPDATE patron_tablegroup_mapping_t SET patron_id=".$PARTY." WHERE tablegroup_id=".$TABLEGROUP.";");
        $result = $DB->query("SELECT table_id FROM tablegroups_t, table_t WHERE tablegroups_id=".$TABLEGROUP.";");
        while ( $row = mysqli_fetch_array($result))
        {
            $resultA = $DB->query("UPDATE table_t SET isOccupied=1 WHERE table_id=".$row['table_id'].";");
        }
    }

    public function makeUnoccupied($DB,$TABLEGROUP) {
    /**
     *PURPOSE:
     *  To set a table group and all tables in it as not occupied
     *
     *PRECONDITIONS:
     *  - The parameter DB is a reference to the database
     *  - The parameter TABLEGROUP is the table group id for the table group you are opening up
     *
     *POSTCONDITIONS:
     *  No parameters or global varables are modified
     *
     *RETURN VALUES:
     *  Void
     *
     */
        $result = $DB->query("UPDATE tablegroups_t SET is_occupied=0 WHERE tablegroup_id=".$TABLEGROUP.";");
        $result = $DB->query("SELECT table_id FROM tablegroup_table_mapping_t WHERE tablegroup_id=".$TABLEGROUP.";");
        while ( $row = mysqli_fetch_array($result))
        {
            $resultA = $DB->query("UPDATE table_t SET isOccupied=0 WHERE table_id=".$row['table_id'].";");
        }
    }
}

class Restaurant {
    public $Table;
    public $TableGroup;

    public function __construct() {
        $this->Table = new Table();
        $this->TableGroup = new TableGroup();
    }

    public function findBestTableGroupForSeatingDB($DB,$PATRONID,&$RETURNEDTABLEGROUP) {
    /**
     *PURPOSE:  To query the database and determine if a table group is available that
     *          can seat the party with a patron id equal to the parameter (int)$PATRONID.
     *          If there is no table group available for the party, we will determine what
     *          table group can fit the party and has the shortest expected wait time.
     *
     *PRECONDITIONS:    - The parameter DB is a ###### object referencing the database
     *                  - The parameter PATRONID is a positive integer
     *
     *POSTCONDITIONS:   - The parameter RETURNEDTABLEGROUP, passed by reference, will be
     *                    set to an integer value referencing the table group id found by 
     *                    the algorithm
     *                  - All other variables will remain unchanged
     *
     *RETURN VALUE:     0 : RETURNEDTABLEGROUP is set to a table group that is able to have
     *                      the party seated at it.
     *                  
     *                  NULL : The algorithm could not find a table group to seat the party at
     *                         and it could not determine an expected wait time. 
     *
     *                  Any positive integer: RETURNEDTABLEGROUP is set to a table group that
     *                                        has the shortest expected wait time. The value 
     *                                        returned is the wait value in seconds (because
     *                                        that is what UNIX timestamps use).
     *
     */
        $TableGroupWithShortestWaitTime = NULL;
        $ShortestExpectedWait = 86400;
        $CurrentExpectedWait = NULL;

        $PartySize_t = $DB->query("SELECT party_size FROM patrons_t WHERE patron_id=$PATRONID;");
        $PartySize_r = mysqli_fetch_array($PartySize_t);
        $PartySize_v = $PartySize_r['party_size'];

        $TableSize = $PartySize_v;
        //see if there are TableGroups of size $TableSize
        while ( $TableSize<10 ) {
            $TableGroups_t = $DB->query("SELECT tablegroup_id FROM tablegroups_t WHERE size=$TableSize;");
            while ( $TableGroups_r = mysqli_fetch_array($TableGroups_t) ) {
                $TableGroups_v = $TableGroups_r['tablegroup_id'];
                //check to see if the table group is occupied
                //if it is occupied, go to the next table group
                $TableGroupOccupied_t = $DB->query("SELECT is_occupied FROM tablegroups_t WHERE tablegroup_id=$TableGroups_v;");
                $TableGroupOccupied_r = mysqli_fetch_array($TableGroupOccupied_t);
                $TableGroupOccupied_v = $TableGroupOccupied_r['is_occupied'];

                if ( $TableGroupOccupied_v ) { //if the group is occupied lets calculate its expected wait time

                    //figure out how many people are sitting at the table group
                    $PartyAtTableGroup_t = $DB->query("SELECT patron_id FROM patron_tablegroup_mapping_t WHERE tablegroup_id=$TableGroups_v;");
                    $PartyAtTableGroup_r = mysqli_fetch_array($PartyAtTableGroup_t);
                    $PartyAtTableGroup_v = $PartyAtTableGroup_r['patron_id'];

                    $NumberOfPeople_t = $DB->query("SELECT party_size FROM patrons_t WHERE patron_id=$PartyAtTableGroup_v;");
                    $NumberOfPeople_r = mysqli_fetch_array($NumberOfPeople_t);
                    $NumberOfPeople_v = $NumberOfPeople_r['party_size'];

                    //calculate their expected meal end time
                    //first get the expected end time for the tablegroup
                    $MealStart_t = $DB->query("SELECT time_seated FROM patron_tablegroup_mapping_t WHERE tablegroup_id=$TableGroups_v;");
                    $MealStart_r = mysqli_fetch_array($MealStart_t);
                    $MealStart_v = $MealStart_r['time_seated'];

                    //calculate the average meal length for a party of the size of the one currently at the table group
                    $AverageLength = avgMealTimeDB($DB,$NumberOfPeople_v);

                    //find the difference between now and the expected meal end time
                    $CurrentExpectedWait = (($MealStart_v+$AverageLength)-timeX(60));

                    //if it is less than the current table group with the shortest wait time, make this
                    //the table group with the shortest wait time.
                    if ( $CurrentExpectedWait < $ShortestExpectedWait ) {
                        $ShortestExpectedWait = $CurrentExpectedWait;
                        $TableGroupWithShortestWaitTime = $TableGroups_v;
                    }
                    continue;
                }
                else { //if the table group is not occupied, start to look at its tables
                    $TablesInGroup_t = $DB->query("SELECT table_id FROM tablegroup_table_mapping_t WHERE tablegroup_id=$TableGroups_v;");
                    while ( $TablesInGroup_r = mysqli_fetch_array($TablesInGroup_t) ) {
                        $TablesInGroup_v = $TablesInGroup_r['table_id'];
                        //get the table information
                        $TableOccupied_t = $DB->query("SELECT isOccupied FROM table_t WHERE table_id=$TablesInGroup_v;");
                        $TableOccupied_r = mysqli_fetch_array($TableOccupied_t);
                        $TableOccupied_v = $TableOccupied_r['isOccupied'];
                        if ( $TableOccupied_v ) { //the table in the table group is occupied
                            continue 2;
                        }
                    }
                    //all tables in group must be open
                    //so lets see if there is enough time to eat before any reservations!
                    //TODO: Add this functionality!!!
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

    public function seatGroupDB($DB,$PATRONID,$TABLEGROUP) {
    /**
     *PURPOSE:
     *  To seat a party at a table group
     *
     *PRECONDITIONS:
     *  - The parameter DB is a reference to the database
     *  - The parameter PATRONID is a patron id for the party you would ike to seat
     *  - The parameter TABLEGROUP is the table group id for where you would like to seat the party
     *
     *POSTCONDITIONS:
     *  No parameters or global variables are modified
     *
     *RETURN VALUES:
     *  Void
     *
     */
        //associate the table group with the patron/party
       $result = $DB->query("INSERT INTO patron_tablegroup_mapping_t (patron_id, tablegroup_id, time_seated) VALUES ($PATRONID, $TABLEGROUP,".timeX(60).");");
        //occupy the table group (this also will occupy all tables in the group)
        $this->TableGroup->makeOccupied($DB,$TABLEGROUP,$PARTONID);
    }

    public function unseatGroupDB($DB,$PARTY) {
    /**
     *PURPOSE:
     *  To unseat a party (i.e., open up a table group)
     *
     *PRECONDITIONS:
     *  - The parameter DB is a reference to the database
     *  - The parameter PARTY is a patron id for the party that is being unseated
     *
     *POSTCONDITIONS:
     *  No parameters or global variables are changed
     *
     *RETURN VALUES:
     *  Void
     *
     */
        //see what table group the party was seated at
        $SeatedAt_t = $DB->query("SELECT tablegroup_id FROM patron_tablegroup_mapping_t WHERE patron_id=$PARTY;");
        $SeatedAt_r = mysqli_fetch_array($SeatedAt_t);
        $SeatedAt_v = $SeatedAt_r['tablegroup_id'];

        //unseat the group
        $this->TableGroup->makeUnoccupied($DB,$SeatedAt_v);

        //remove the party-tablegroup association from parton_tablegroup_mapping_t
        $result = $DB->query("DELETE FROM patron_tablegroup_mapping_t WHERE patron_id=$PARTY;");
    }

    public function makeReservationDB($DB,$PARTY) {
    /**
     *PURPOSE: 
     *  To make a reservation for a party at a table group
     *
     *PRECONDITIONS: 
     *  - parameter DB is a reference to the database
     *  - parameter PARTY is the ID for the party we are trying to make a reservation for
     *
     *POSTCONDITIONS:
     *
     *RETURN VALUES:
     *  0: The function was not able to make a reservation
     *
     *  1: The function was able to make a reservation
     */
        //get the party size given the party ID
        echo "TEST!!!<br>";
        $PartySize_t = $DB->query("SELECT party_size FROM patrons_t WHERE patron_id=$PARTY;");
        $PartySize_r = mysqli_fetch_array($PartySize_t);
        $PartySize_v = $PartySize_r['party_size'];

        //get the party's reservation time
        $ReservationTime_t = $DB->query("SELECT DISTINCT 
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
        echo "timestamp is $ReservationTime_v<br>";
        //now that we know the party size, lets estimate how long they will need to eat
        echo "before expected time calc<br>";
        $ExpectedMealTime = $this->avgMealTimeDB($DB,$PartySize_v); 
        echo "before while loop <br>";
        //now we know the table group must be able to hold that many or more people
        //lets look for the table groups that can
        while ( $PartySize_v<=10 ) {
            echo "Looking at party size $PartySize_v<br>";
            //get the table groups of $PartySize
            $TableGroups_t = $DB->query("SELECT tablegroup_id FROM tablegroups_t WHERE size=$PartySize_v;");
            while ( $TableGroups_r = mysqli_fetch_array($TableGroups_t) ) {
                echo "LOOKING!<br>";
                $TableGroups_v = $TableGroups_r['tablegroup_id'];
                //look at the reservations for that table group
                $Reservations_t = $DB->query("SELECT reservation_time, expected_finish_time FROM tablegroup_reservations_t WHERE tablegroup_id=$TableGroups_v AND expected_finish_time>=$TIME ORDER BY reservation_time;");
                if ( $Reservations_t->num_rows() == 0 ) {
                    
                }
                $Reservations_r1 = mysqli_fetch_array($Reservations_t);
                while ( $Reservations_r2 = mysqli_fetch_array($Reservations_t) ) {
                    //get the expected end time for R1
                    $Reservations_v1 = $Reservations_r1['expected_finish_time'];
                    //get the start time for R2
                    $Reservations_v2 = $Reservations_r2['reservation_time'];

                    //let's see if the gap is big enough
                    if ( ($Reservations_v2-$Reservations_v1) > $ExpectedMealTime ) {
                        //we know we have enough time to make the reservation!
                        echo "Making reservation<br>";
                        $query = $DB->query("INSERT INTO tablegroup_reservations_t (tablegroup_id, reservation_time, expected_finish_time) VALUES ($TableGroups_v, $ReservationTime_v, ($ReservationTime_v+$ExpectedMealTime));");
                        return 1;
                    }
                    $Reservations_r1 = $Reservations_r2;
                }
            }
            ++$PartySize_v;
        }
        return 0;
    }

    public function avgMealTimeDB($DB,$PARTYSIZE) {
    /**
     *PURPOSE:
     *  To calculate the average meal time for parties of a given size
     *
     *PRECONDITIONS:
     *  - The parameter DB must be a reference to the database
     *  - The parameter PARTYSIZE must be a positive integer referencing the size of the party
     *
     *POSTCONDITIONS:
     *  No parameters or global varables are modified
     *
     *RETURN VALUES:
     *  NULL: could not calculate the average meal time
     *
     *  A positive integer: the average meal time in seconds
     */
        echo "in fun<br>";
        $NumRows = 0;
        $ReturnValue = 0;
        $WaitTimesForSize_t = $DB->query("SELECT wait_time FROM wait_times_t WHERE group_size=$PARTYSIZE;");
        if ( !isset($WaitTimesForSize_t) ) {
            return NULL;
        }
        while ( $WaitTimesForSize_r = mysqli_fetch_array($WaitTimesForSize_t) ) {
            echo "in while!<br>";
            ++$NumRows;
            $WaitTimesForSize_v = $WaitTimesForSize_r['wait_time'];
            $ReturnValue+=$WaitTimesForSize_v;
        }
        //divide $ReturnValue by the number of values to get the average
        $ReturnValue = $ReturnValue/$NumRows;

        return $ReturnValue;
    }
}

echo "TEST!!!!!!<br>";
$R1 = new Restaurant();
$RET=9999;
echo "TEST A<br>";
$R1->findBestTableGroupForSeatingDB($PAGR_database,1,$RET);
echo "I'll seat party 1 at TableGroup ".$RET."<br>";




//lets look at reserving a table
$R1->makeReservationDB($PAGR_database,2)

?>
</body></html>
