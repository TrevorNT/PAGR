<html><body>
<?php

/**
 *@author Jacob Zadnik <jacobszadnik@gmail.com>
 *@package com.pagr.server
 *@license proprietary
 *@version 0.5.0
 */

function sortTG($A, $B) {
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

    public function __construct($ID,$SIZE,$TABLES) {
        $this->ID = $ID;
        $this->SIZE = $SIZE;
        $this->OCCUPIED = false;
        $this->TABLES = $TABLES;
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
                return $CURRENTGROUP;
            }
        }
        return NULL;
    }

    public function seatGroup($PARTY,&$TABLEGROUP) {
        if ( $TABLEGROUP == NULL ) {
            echo "Party " . $PARTY->ID . " was not able to be seated." . "<br>";
            //calculate average meal time for parties of this size
            $sum = 0;
            foreach ( $this->MEALTIMESTATS[$PARTY->SIZE] as $CURRENTSTAT )
            {
                $sum = $sum + $CURRENTSTAT;
            }
            $sum = $sum/count($this->MEALTIMESTATS[$PARTY->SIZE]);

            echo "Average meal time for parties of this size is: " . $sum . "<br>";
            return;
        }
        echo "Party " . $PARTY->ID . " with " . $PARTY->SIZE . " people was seated at tables: ";
        foreach ($TABLEGROUP->TABLES as $CURRENTTABLE ) {
            echo $CURRENTTABLE->ID . " ";
        }
        echo "<br>";
        $PARTY->SEATEDAT = $TABLEGROUP;
        $PARTY->SEATED = true;
        $TABLEGROUP->makeOccupied();
    }

    public function unseatGroup(&$TABLEGROUP) {
        $TABLEGROUP->makeUnoccupied();
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
}

class Reservation extends Party {
    public $RESERVATIONTIME;
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
$R->MEALTIMESTATS[2][] = 25;
$R->MEALTIMESTATS[2][] = 25;
$R->MEALTIMESTATS[2][] = 25;
$R->MEALTIMESTATS[2][] = 25;
$R->MEALTIMESTATS[2][] = 25;
//party size of 3
$R->MEALTIMESTATS[3][] = 35;
$R->MEALTIMESTATS[3][] = 25;
$R->MEALTIMESTATS[3][] = 35;
$R->MEALTIMESTATS[3][] = 45;
$R->MEALTIMESTATS[3][] = 35;
//party size of 4
$R->MEALTIMESTATS[4][] = 45;
$R->MEALTIMESTATS[4][] = 45;
$R->MEALTIMESTATS[4][] = 45;
$R->MEALTIMESTATS[4][] = 55;
$R->MEALTIMESTATS[4][] = 75;
//party size of 5
$R->MEALTIMESTATS[5][] = 35;
$R->MEALTIMESTATS[5][] = 45;
$R->MEALTIMESTATS[5][] = 45;
$R->MEALTIMESTATS[5][] = 45;
$R->MEALTIMESTATS[5][] = 35;
//party size of 6
$R->MEALTIMESTATS[6][] = 55;
$R->MEALTIMESTATS[6][] = 55;
$R->MEALTIMESTATS[6][] = 65;
$R->MEALTIMESTATS[6][] = 55;
$R->MEALTIMESTATS[6][] = 45;
//party size of 7
$R->MEALTIMESTATS[7][] = 75;
$R->MEALTIMESTATS[7][] = 75;
//party size of 9
$R->MEALTIMESTATS[9][] = 90;
$R->MEALTIMESTATS[9][] = 85;





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
        $R->unseatGroup($CURRENTPARTY->SEATEDAT);
    }
}

//Should have all available
printStatusOfTables($Tables);

?>
</body></html>
