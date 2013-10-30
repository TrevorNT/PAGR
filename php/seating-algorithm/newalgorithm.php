<html><body>
<?php

/**
 *@author Jacob Zadnik <jacobszadnik@gmail.com>
 *@package com.pagr.server
 *@license proprietary
 *@version 0.5.0
 */

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
            echo "Could not seat group!" . "<br>";
            return;
        }
        echo "Party " . $PARTY->ID . " was seated at tables: ";
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


//should be all available
printStatusOfTables($Tables);

$PartyID = 1;
$P1 = array();
$P1[] = new Party($PartyID++, 4);
$P1[] = new Party($PartyID++, 5);
$P1[] = new Party($PartyID++, 9);
$P1[] = new Party($PartyID++, 2);
$P1[] = new Party($PartyID++, 1);

foreach ( $P1 as $CURRENTPARTY ) {
    $R->seatGroup($CURRENTPARTY, $R->findBestTableGroup($CURRENTPARTY));
}

printStatusOfTables($Tables);

foreach ( $P1 as $CURRENTPARTY ) {
    if ( $CURRENTPARTY->SEATED == true ) {
        $R->unseatGroup($CURRENTPARTY->SEATEDAT);
    }
}


printStatusOfTables($Tables);

?>
</body></html>
