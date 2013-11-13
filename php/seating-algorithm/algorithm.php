<html><body>
<?php

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

function sortTG($A, $B) {
/**
 *Purpose:
 *
 *
 *Precondition:
 *
 *
 *Postcondition:
 *
 *
 */
    $SIZEA = sizeof($A->TABLES);
    $SIZEB = sizeof($B->TABLES);
    if ( $SIZEA == $SIZEB ) {
        return 0;
    }
    return ($SIZEA < $SIZEB) ? -1 : 1;
}

class Table {
    public $ID;
    public $SIZE;
    public $OCCUPIED;

    public function __construct($ID,$SIZE) {
        $this->ID = $ID;
        $this->SIZE = $SIZE;
        $this->OCCUPIED = false;
        $this->RESERVATIONTIMES = array();
    }

    public function makeOccupied() {
        $this->OCCUPIED = true;
    }

    public function makeUnoccupied() {
        $this->OCCUPIED = false;
    }
}

class TableGroup {
    public $ID;
    public $SIZE;
    public $OCCUPIED;
    public $TABLES;
    public $RESERVATIONTIMES;

    public function __construct($ID,$SIZE,$TABLES) {
        $this->ID = $ID;
        $this->SIZE = $SIZE;
        $this->OCCUPIED = false;
        $this->TABLES = $TABLES;
        $this->RESERVATIONTIMES = array();
    }

    public function makeOccupied() {
        $this->OCCUPIED = true;
        foreach ($this->TABLES as $CURRENTTABLE) {
            $CURRENTTABLE->makeOccupied();
        }
    }

    public function makeUnoccupied() {
        $this->OCCUPIED = false;
        foreach ($this->TABLES as $CURRENTTABLE) {
            $CURRENTTABLE->makeUnoccupied();
        }
    }
}

class Restaurant {
    public $TABLEGROUPS; //map with (key,value)=(table group size, array of table groups with that size)
    public $MEALTIMESTATS; //map with (key,value)=(party size, array of meal times for parties of that size)

    public function __construct($TABLES) {
        $this->TABLEGROUPS = array(array());
        $this->MEALTIMESTATS = array(array());
    }

    public function addTableGroup ($ID,$SIZE,$TABLES) {
        $this->TABLEGROUPS[$SIZE][] = new TableGroup($ID,$SIZE,$TABLES);
        usort($this->TABLEGROUPS[$SIZE], "sortTG");
    }

    public function findBestTableGroup ($PARTY) {

        $FASTESEXPECTED;//this will be set to the table that is expected to be cleared the fastest.

        $TABLESIZE = $PARTY->SIZE;

        while ( !isset($this->TABLEGROUPS[$TABLESIZE]) ) {
            ++$TABLESIZE;
        }
        foreach ( $this->TABLEGROUPS[$TABLESIZE] as $CURRENTGROUP ) {
            if ( $CURRENTGROUP->OCCUPIED == false ) {
                foreach ( $CURRENTGROUP->TABLES as $CURRENTTABLE ) {
                    if ( $CURRENTTABLE->OCCUPIED == true ) {
                        continue 2;
                    }
                }
                //found group
                //now check for reservations
                if ( isset($CURRENTGROUP->RESERVATIONTIMES) ) { //if the table has a reservation
                    foreach ( $CURRENTGROUP->RESERVATIONTIMES as $TIME ) {
                        if ( $TIME > time() ) {//if the reservation time is in the future
                            if ( ($TIME-timeX(60)) > ($this->avgMealTime($PARTY->SIZE)+(600)) ) {   //if the reservation is far enough in the future have 
                                                                                                    //the table open by then if a group is seated now
                                return $CURRENTGROUP;
                            }
                            continue 2;
                        }
                    }
                }
                //if no reservations
                return $CURRENTGROUP;
            }
        }
        return NULL;
    }

    public function seatGroup($PARTY,&$TABLEGROUP) {
        if ( $TABLEGROUP == NULL ) {
            echo "Party " . $PARTY->ID . " with " . $PARTY->SIZE . " people was not able to be seated." . "<br>";
            //calculate average meal time for parties of this size
            echo "Average meal time for parties of this size is: " . $this->avgMealTime($PARTY->SIZE) . "<br>";
            return;
        }
        echo "Party " . $PARTY->ID . " with " . $PARTY->SIZE . " people was seated at tables: ";
        foreach ($TABLEGROUP->TABLES as $CURRENTTABLE ) {
            echo $CURRENTTABLE->ID . " ";
        }
        echo "<br>";
        $PARTY->SEATEDAT = $TABLEGROUP;
        $PARTY->SEATED = true;
        $PARTY->STARTEATINGTIME = timeX(60);
        $TABLEGROUP->makeOccupied();
    }

    public function unseatGroup($PARTY) {
        $TABLEGROUP = $PARTY->SEATEDAT;
        $TABLEGROUP->makeUnoccupied();
        $PARTY->ENDEATINGTIME = timeX(60);
        $this->MEALTIMESTATS[$PARTY->SIZE][] = ($PARTY->ENDEATINGTIME - $PARTY->STARTEATINGTIME);
    }

    public function makeReservation($PARTY,&$TABLEGROUP) {
       $TABLEGROUP->RESERVATIONTIMES[] = $PARTY->RESERVATIONTIME;          
    }

    public function avgMealTime($PARTYSIZE) {
            $sum = 0;
            foreach ( $this->MEALTIMESTATS[$PARTYSIZE] as $CURRENTSTAT )
            {
                $sum = $sum + $CURRENTSTAT;
            }
            $sum = $sum/count($this->MEALTIMESTATS[$PARTYSIZE]);

            return $sum;
    }

}



class Party {
    public $ID;
    public $SIZE;
    public $SEATED;
    public $SEATEDAT;
    public $STARTEATINGTIME;
    public $ENDEATINGTIME;

    public function __construct($ID,$SIZE) {
        $this->ID = $ID;
        $this->SIZE = $SIZE;
        $this->SEATED = false;
    }
}

class WalkIn extends Party {
    public $CHECKINTIME;

    public function __construct($ID,$SIZE,$CHECKINTIME) {
        $this->ID = $ID;
        $this->SIZE = $SIZE;
        $this->SEATED = false;
        $this->CHECKINTIME = $CHECKINTIME;
    }

}

class Reservation extends Party {
    public $RESERVATIONTIME;

    public function __construct($ID,$SIZE,$RESERVATIONTIME) {
        $this->ID = $ID;
        $this->SIZE = $SIZE;
        $this->SEATED = false;
        $this->RESERVATIONTIME = $RESERVATIONTIME;
    }
}

//functions for testing
function printStatusOfTables($TABLES) {
    foreach ($TABLES as $CURRENTTABLE ) {
        echo "Table " . $CURRENTTABLE->ID . ": ";
        if ($CURRENTTABLE->OCCUPIED==true) {
            echo "occupied" . "<br>";
        }
        else {
            echo "available" . "<br>";
        }
    }
    echo "<br>";
}

function printMealTimeStats($RESTAURANT) {
    echo "Meal time statistics:" . "<br>";
    foreach ( $RESTAURANT->MEALTIMESTATS as $i => $PARTYSIZE ) {
        echo "Parties with " . $i . " people:" . "<br>";
        foreach ( $PARTYSIZE as $MEALTIME ) {
            echo "        " . $MEALTIME . "<br>";
        }
    }
    echo "<br>";
}

//Make an array of tables for our mock up restaurant
$Tables = array();
$TableID = 1;
$Tables[] = new Table($TableID++, 2);
$Tables[] = new Table($TableID++, 2);
$Tables[] = new Table($TableID++, 2);
$Tables[] = new Table($TableID++, 2);
$Tables[] = new Table($TableID++, 4);
$Tables[] = new Table($TableID++, 4);
$Tables[] = new Table($TableID++, 4);
$Tables[] = new Table($TableID++, 4);
$Tables[] = new Table($TableID++, 6);
$Tables[] = new Table($TableID++, 6);

$R = new Restaurant();
$TableGroupID = 1;
$R->addTableGroup($TableGroupID++,2,array($Tables[0]));
$R->addTableGroup($TableGroupID++,2,array($Tables[1]));
$R->addTableGroup($TableGroupID++,2,array($Tables[2]));
$R->addTableGroup($TableGroupID++,2,array($Tables[3]));
$R->addTableGroup($TableGroupID++,4,array($Tables[4]));
$R->addTableGroup($TableGroupID++,4,array($Tables[5]));
$R->addTableGroup($TableGroupID++,4,array($Tables[6]));
$R->addTableGroup($TableGroupID++,4,array($Tables[7]));
$R->addTableGroup($TableGroupID++,6,array($Tables[8]));
$R->addTableGroup($TableGroupID++,6,array($Tables[9]));
$R->addTableGroup($TableGroupID++,4,array($Tables[0],$Tables[1]));
$R->addTableGroup($TableGroupID++,4,array($Tables[1],$Tables[2]));
$R->addTableGroup($TableGroupID++,4,array($Tables[2],$Tables[3]));
$R->addTableGroup($TableGroupID++,6,array($Tables[4],$Tables[5]));
$R->addTableGroup($TableGroupID++,6,array($Tables[4],$Tables[6]));
$R->addTableGroup($TableGroupID++,6,array($Tables[5],$Tables[7]));
$R->addTableGroup($TableGroupID++,6,array($Tables[6],$Tables[7]));
$R->addTableGroup($TableGroupID++,10,array($Tables[8],$Tables[9]));
$R->addTableGroup($TableGroupID++,6,array($Tables[0],$Tables[1],$Tables[2]));
$R->addTableGroup($TableGroupID++,6,array($Tables[1],$Tables[2],$Tables[3]));

//make some mock meal time statistics
//party size of 2
$R->MEALTIMESTATS[2][] = 25;
//party size of 3
$R->MEALTIMESTATS[3][] = 35;
//party size of 4
$R->MEALTIMESTATS[4][] = 45;
//party size of 5
$R->MEALTIMESTATS[5][] = 55;
//party size of 6
$R->MEALTIMESTATS[6][] = 65;
//party size of 7
$R->MEALTIMESTATS[7][] = 75;
//party size of 9
$R->MEALTIMESTATS[9][] = 95;





//should be all available
printStatusOfTables($Tables);

//Make parties
$PartyID = 1;
$P1 = array();
$P1[] = new Party($PartyID++, 6);//party 1
$P1[] = new Party($PartyID++, 6);//party 2
$P1[] = new Party($PartyID++, 6);//party 3
$P1[] = new Party($PartyID++, 6);//party 4
$P1[] = new Party($PartyID++, 1);//party 5
$P1[] = new Party($PartyID++, 2);//party 6
$P1[] = new Party($PartyID++, 6);//party 7
$P1[] = new Party($PartyID++, 1);//party 8


printMealTimeStats($R);

//Seat all of the parties
foreach ( $P1 as $CURRENTPARTY ) {
    $R->seatGroup($CURRENTPARTY, $R->findBestTableGroup($CURRENTPARTY));
}

//Should have tables occupied
printStatusOfTables($Tables);

//unseat all of the parties
// that are seated
foreach ( $P1 as $CURRENTPARTY ) {
    if ( $CURRENTPARTY->SEATED == true ) {
        $R->unseatGroup($CURRENTPARTY);
        sleep( rand(1,2) );
    }
}

//Should have all available
printStatusOfTables($Tables);

printMealTimeStats($R);

?>
</body></html>
