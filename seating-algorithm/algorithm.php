<html><body>
<?php

/**
 *@author Jacob Zadnik <jacobszadnik@gmail.com>
 *@package com.pagr.server
 *@license proprietary
 *@version 0.3.0
 */

class Table {

    public $id;
    public $Size;
    public $Occupied = false;

    public function __construct($id,$Size) {
        $this->Size = $Size;
        $this->id = $id;
    }

    public function InUse() {
        $this->Occupied = true;
    }

    public function NotInUse() {
        $this-> Occupied = false;
    }

}

class TableGroup {

    public $Priority;
    public $Size;
    public $Occupied = false;
    public $Tables;
    public $TimeSeated;
    public $TimeLeft;
    public $TimeOccupied;

    public function __construct($Size, &$Tables) {
        $this->Priority = sizeof($Tables); 
        $this->Size = $Size;
        $this->Tables = $Tables;
    }

    public function InUse($CurrentTime) {
        $this->Occupied = true;
        $this->TimeSeated = $CurrentTime;
        foreach ($this->Tables as $CurrentTable) {
            $CurrentTable->InUse();
        }
    }

    public function NotInUse($CurrentTime) {
        $this->Occupied = false;
        $this->TimeLeft = $CurrentTime;
        foreach ($Tables as $CurrentTable) {
            $CurrentTable.NotInUse();
        }
    }
}

function TGPrioritySort($a,$b) {
    if ($a->Priority == $b->Priority) {
        return 0;
    }
    return ($a->Priority < $b->Priority) ? -1 : 1;
}

class Restaurant {
    public $TableGroups;
    public $PreviousTimes;

    public function __construct() {
        $this->TableGroups = array(array());
        $this->PreviousTimes = array(array());
    }

    public function addTableGroup($Size,$Tables) {
        $this->TableGroups[$Size][] = new TableGroup($Size,$Tables);
        usort($this->TableGroups[$Size],"TGPrioritySort");
    }
    /*
     *This is the working version of Restaurant::FindOpenGroup()
     *I am commenting it out to work on a new version that will 
     *incorporate the new Group class
     */
    /*
    public function FindOpenGroup($Size) {
        $TableSize = $Size;
        if ( !isset($this->TableGroups[$Size]) ) {
            $result = isset($this->TableGroups[++$TableSize]);
            while ( !$result ) {
                $result = isset($this->TableGroups[++$TableSize]);
            }
        }
        foreach ( $this->TableGroups[$TableSize] as $CurrentGroup ) {
            if ( $CurrentGroup->Occupied == false ) {
                foreach ( $CurrentGroup->Tables as $CurrentTable1 ) {
                    if ( $CurrentTable1->Occupied == true ) {
                        continue 2;
                    }
                }
                $CurrentGroup->InUse(time());
                echo date("H\:i\:s",$CurrentGroup->TimeSeated) . ": " . $Size . ' people were seated at table(s): ';
                foreach ( $CurrentGroup->Tables as $CurrentTable2 ) {
                    echo $CurrentTable2->id . ' ';
                }
                echo "<br>";
                return;
            }
        }
        //No way to seat. Must calculate next expected opening.
        echo date("H\:i\:s",$CurrentGroup->TimeSeated) . ": " . $Size . " people could not be seated! Must calculate next expected opening!" . "<br>";
    }*/
    public function FindOpenGroup($Party) {
        $TableSize = $Party->Size;
        if ( !isset($this->TableGroups[$Party->Size]) ) {
            $result = isset($this->TableGroups[++$TableSize]);
            while ( !$result ) {
                $result = isset($this->TableGroups[++$TableSize]);
            }
        }
        $HoldCurrentGroup;
        foreach ( $this->TableGroups[$TableSize] as $CurrentGroup ) {
            $HoldCurrentGroup = $CurrentGroup;
            if ( $CurrentGroup->Occupied == false ) {
                foreach ( $CurrentGroup->Tables as $CurrentTable1 ) {
                    if ( $CurrentTable1->Occupied == true ) {
                        continue 2;
                    }
                }
                $CurrentGroup->InUse(time());
                echo date("H\:i\:s",$CurrentGroup->TimeSeated) . ": " . $Party->Size . ' people were seated at table(s): ';
                foreach ( $CurrentGroup->Tables as $CurrentTable2 ) {
                    echo $CurrentTable2->id . ' ';
                }
                echo "<br>";
                return;
            }
        }
        //No way to seat. Must calculate next expected opening.
        echo date("H\:i\:s",$HoldCurrentGroup->TimeSeated) . ": " . $Party->Size . " people could not be seated! Must calculate next expected opening!" . "<br>";
    }
}

class Party {
    public $Size;
    public $PhoneNumber;

    public function __construct($Size,$PhoneNumber) {
        $this->Size = $Size;
        $this->PhoneNumber = $PhoneNumber;
    }
}

class Reservation extends Party {
    public $ReservationTime;
}

class WalkIn extends Party {
    public $TimePlaced;
    public $TimeSeated;
}

//Define Restaurant
$R1 = new Restaurant();

//Define Tables
$T1 = new Table(1,2);
$T2 = new Table(2,2);
$T3 = new Table(3,2);
$T4 = new Table(4,2);
$T5 = new Table(5,4);
$T6 = new Table(6,4);
$T7 = new Table(7,4);
$T8 = new Table(8,4);
$T9 = new Table(9,6);
$T10 = new Table(10,6);

//Define TableGroups
$R1->addTableGroup(2,array($T1));
$R1->addTableGroup(2,array($T2));
$R1->addTableGroup(2,array($T3));
$R1->addTableGroup(2,array($T4));
$R1->addTableGroup(4,array($T5));
$R1->addTableGroup(4,array($T6));
$R1->addTableGroup(4,array($T7));
$R1->addTableGroup(4,array($T8));
$R1->addTableGroup(6,array($T9));
$R1->addTableGroup(6,array($T10));
$R1->addTableGroup(4,array($T1,$T2));
$R1->addTableGroup(4,array($T2,$T3));
$R1->addTableGroup(4,array($T3,$T4));
$R1->addTableGroup(6,array($T5,$T6));
$R1->addTableGroup(6,array($T5,$T7));
$R1->addTableGroup(6,array($T6,$T8));
$R1->addTableGroup(6,array($T7,$T8));
$R1->addTableGroup(10,array($T9,$T10));
$R1->addTableGroup(6,array($T1,$T2,$T3));
$R1->addTableGroup(6,array($T2,$T3,$T4));

//Define Parties
$P1 = new Party(4,9005551111);
$P2 = new Party(6,9005552222);
$P3 = new Party(9,9005553333);
$P4 = new Party(2,9005554444);

//Attempt to seat Parties
$R1->FindOpenGroup($P1);
$R1->FindOpenGroup($P2);
$R1->FindOpenGroup($P3);
$R1->FindOpenGroup($P4);



?>
</body></html>
