<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdSimplelist extends RJsonwalksDisplaybase {

    private $lastValue = "";
    private $walksClass = "walks";
    private $walkClass = "walk";
    public $addGridRef = true;
    public $addStartGridRef = false;
    public $addDescription = false;

    // const BR = "<br />";

    function DisplayWalks($walks) {
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        echo "<div class='" . $this->walksClass . "' >" . PHP_EOL;
        foreach ($items as $walk) {
            $thismonth = $walk->walkDate->format('F');
            if ($thismonth <> $this->lastValue) {
                $this->lastValue = $thismonth;
                echo "<h2>" . $thismonth . "</h2>" . PHP_EOL;
            }

            $this->displayWalk($walk);
        }
        echo "</div>" . PHP_EOL;
    }

    function setWalksClass($class) {
        $this->walksClass = $class;
    }

    function setWalkClass($class) {
        $this->walkClass = $class;
    }

    private function displayWalk($walk) {

        $text = "<b>" . $walk->walkDate->format('l jS') . "</b>";
        if ($walk->hasMeetPlace) {
            $text .= ", " . $walk->meetLocation->timeHHMMshort . " at " . $walk->meetLocation->description;
            if ($this->addGridRef === true) {
                $text .= " [" . $walk->meetLocation->gridref . "]";
            }
        }
        if ($walk->startLocation->exact) {
            $text .= ", " . $walk->startLocation->timeHHMMshort . " at " . $walk->startLocation->description;
            if ($this->addGridRef === true or $this->addStartGridRef) {
                $text .= " [" . $walk->startLocation->gridref . "]";
            }
        } else {
            if (!empty($walk->startLocation->description)) {
                $text .= ", " . $walk->startLocation->description . " area";
            }
        }

        if (!empty($walk->title)) {
            $text .= ", <b>" . $walk->title . "</b>";
        }

        if ($this->addDescription) {
            if ($walk->description !== $walk->title) {
                if (!empty($walk->description)) {
                    $text .= ", " . $walk->description;
                }
            }
        }
        $text .= ", " . $walk->distanceMiles . "mi/" . $walk->distanceKm . "km";
           if ($walk->isLeader) {
            $text.=", Leader ";
        } else {
            $text.=", Contact ";
        }
        $text.= $walk->contactName;
        if ($walk->telephone1 != "") {
            $text.= " " . $walk->telephone1;
        } else {
            if ($walk->telephone2 != "") {
                $text.= " " . $walk->telephone2;
            }
        }
        echo "<div class='" . $this->walkClass . $walk->status . "' >" . PHP_EOL;
        echo "<p>" . $text . "</p>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    }

}
