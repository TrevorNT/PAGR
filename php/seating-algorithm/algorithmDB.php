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
/**
 *Purpose:
 *  To 'speed up' time elapsed between the page first
 *  loading and the current time.
 *
 *Precondition:
 *  A global variable that holds a UNIX
 *  time stamp obtained when the page is
 *  loaded.
 *
 *Postcondition:
 *  Returns the 'current time'
 *  base on how much time would have passed
 *  in 'accelerated time'
 */
    $dif = time() - $startTime;
    return ($startTime+($dif*$FACTOR));
}

class Table {

    public function makeOccupied($DB) {
        $result = $DB->query("UPDATE table_t SET isOccupied=1 WHERE table_id=".$this->ID.";");
    }

    public function makeUnoccupied($DB) {
        $result = $DB->query("UPDATE table_t SET isOccupied=0 WHERE table_id=".$this->ID.";");
    }
}

class TableGroup {

    public function makeOccupied($DB,$TABLEGROUP,$PARTY) {
        $result = $DB->query("UPDATE tablegroups_t SET is_occupied=1 WHERE tablegroup_id=".$TABLEGROUP.";");
        $result = $DB->query("UPDATE patron_tablegroup_mapping_t SET patron_id=".$PARTY." WHERE tablegroup_id=".$TABLEGROUP.";");
        $result = $DB->query("SELECT table_id FROM tablegroups_t, table_t WHERE tablegroups_id=".$TABLEGROUP.";");
        while ( $row = mysqli_fetch_array($result))
        {
            $resultA = $DB->query("UPDATE table_t SET isOccupied=1 WHERE table_id=".$row['table_id'].";");
        }
    }

    public function makeUnoccupied($DB,$TABLEGROUP) {
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

    public function addTableGroup ($ID,$SIZE,$TABLES) {
        $this->TABLEGROUPS[$SIZE][] = new TableGroup($ID,$SIZE,$TABLES);
        usort($this->TABLEGROUPS[$SIZE], "sortTG");
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
     echo "TEST B<br>";
        $TableGroupWithShortestWaitTime = NULL;
        $ShortestExpectedWait = 86400;
        $CurrentExpectedWait = NULL;

        $PartySize_t = $DB->query("SELECT party_size FROM patrons_t WHERE patron_id=$PATRONID;");
        $PartySize_r = mysqli_fetch_array($PartySize_t);
        $PartySize_v = $PartySize_r['party_size'];

        $TableSize = $PartySize_v;
        //see if there are TableGroups of size $TableSize
        while ( $TableSize<10 ) {
        echo "TEST c<br>";
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
        //associate the table group with the patron/party
       $result = $DB->query("INSERT INTO patron_tablegroup_mapping_t (patron_id, tablegroup_id, time_seated) VALUES ($PATRONID, $TABLEGROUP,".timeX(60).");");
        //occupy the table group (this also will occupy all tables in the group)
        $this->TableGroup->makeOccupied($DB,$TABLEGROUP,$PARTONID);
    }

    public function unseatGroupDB($DB,$PARTY) {
        //see what table group the party was seated at
        $SeatedAt_t = $DB->query("SELECT tablegroup_id FROM patron_tablegroup_mapping_t WHERE patron_id=$PARTY;");
        $SeatedAt_r = mysqli_fetch_array($SeatedAt_t);
        $SeatedAt_v = $SeatedAt_r['tablegroup_id'];

        //unseat the group
        $this->TableGroup->makeUnoccupied($DB,$SeatedAt_v);

        //remove the party-tablegroup association from parton_tablegroup_mapping_t
        $result = $DB->query("DELETE FROM patron_tablegroup_mapping_t WHERE patron_id=$PARTY;");
    }

    public function makeReservationDB($DB,$PARTY,$TIME) {
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
     *
     *
     */
        //get the party size given the party ID
        $PartySize_t = $DB->query("SELECT party_size FROM patrons_t WHERE patron_id=$PARTY;");
        $PartySize_r = mysqli_fetch_array($PartySize_t);
        $PartySize_v = $PartySize_r['party_size'];

        //now that we know the party size, lets estimate how long they will need to eat
        $ExpectedMealTime = avgMealTimeDB($DB,$PartySize_v); 

        //now we know the table group must be able to hold that many or more people
        //lets look for the table groups that can
        while ( $PartySize_v<=10 ) {
            //get the table groups of $PartySize
            $TableGroups_t = $DB->query("SELECT tablegroup_id FROM tablegroups_t WHERE size=$PartySize_v;");
            while ( $TableGroups_r = mysqli_fetch_array($TableGroups_t) ) {
                $TableGroups_v = $TableGroups_r['tablegroup_id'];
                //look at the reservations for that table group
                $Reservations_t = $DB->query("SELECT reservation_time, expected_finish_time FROM tablegroup_reservations_t WHERE tablegroup_id=$TableGroups_v AND expected_finish_time>=$TIME ORDER BY reservation_time;");
                $Reservations_r1 = mysqli_fetch_array($Reservations_t);
                while ( $Reservations_r2 = mysqli_fetch_array($Reservations_t) ) {
                    //get the expected end time for R1
                    $Reservations_v1 = $Reservations_r1['expected_finish_time'];
                    //get the start time for R2
                    $Reservations_v2 = $Reservations_r2['reservation_time'];

                    //let's see if the gap is big enough
                    if ( ($Reservations_v2-$Reservations_v1) > $ExpectedMealTime ) {
                        //we know we have enough time to make the reservation!
                        $query = $DB->query("INSERT INTO tablegroup_reservations_t (tablegroup_id, reservation_time, expected_finish_time) VALUES ($TableGroups_v, $TIME, ($TIME+$ExpectedMealTime));");
                        return 1;
                    }
                    $Reservations_r1 = $Reservations_r2;
                }
            }
            ++$PartySize_v;
        }
        return 0;
    }

    public function makeReservation(&$PARTY,&$TABLEGROUP) {
        if ( $TABLEGROUP == NULL ) {
            echo "COULD NOT MAKE RESERVATION!" . "<br>";
            return NULL;
        }
        $TABLEGROUP->RESERVATIONTIMES[] = $PARTY->RESERVATIONTIME;          
        $PARTY->TABLEGROUPRESERVED = $TABLEGROUP;
    }

    public function avgMealTimeDB($DB,$PARTYSIZE) {
        echo "in fun<br>";
        $NumRows = 0;
        $ReturnValue = 0;
        $WaitTimesForSize_t = $DB->query("SELECT wait_time FROM wait_times_t WHERE group_size=$PARTYSIZE;");
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

$R1 = new Restaurant();
$RET=99;
echo "TEST A<br>";
$R1->findBestTableGroupForSeatingDB($PAGR_database,1,$RET);
echo "I'll seat party 1 at TableGroup ".$RET."<br>";

?>
</body></html>
