<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksDe02Programme extends RJsonwalksDisplaybase {

    private $lastValue = "";
    private $walksClass = "de02walks";
    private $walkClass = "de02walk";
    public $addDescription = true;

    const BR = "<br />";

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
        
        $out = "<b>" . $walk->walkDate->format('l, jS') . "</b>";
        if ($walk->hasMeetPlace) {
            $out .= ", " . $walk->meetLocation->timeHHMMshort . " at " . $walk->meetLocation->description;
        }
        if ($walk->startLocation->exact) {
            $out .= ", " . $walk->startLocation->timeHHMMshort . " at " . $walk->startLocation->description;
            $out .= " [" . $walk->startLocation->gridref . "]";
        }

        if (!empty($walk->title)) {
            $out .= ", <b>" . $walk->title . "</b>";
        }
        if ($this->addDescription) {
            if ($walk->description != "") {
                $out.=", " . $walk->description;
            }
        }
        $out .= ", " . $walk->distanceMiles . "mi/" . $walk->distanceKm . "km";
        if ($walk->isLeader) {
            $out.=", Leader ";
        } else {
            $out.=", Contact ";
        }
        $out.= $walk->contactName;
        if ($walk->telephone1 != "") {
            $out.= " " . $walk->telephone1;
        } else {
            if ($walk->telephone2 != "") {
                $out.= " " . $walk->telephone2;
            }
        }
        $out = "<div class='" . $this->walkClass . $walk->status . "'>" . $out . "</div>";
        echo $out;
    }

//    private function getGradeImage($grade) {
//        $g = strtolower($grade);
//        $image = "";
//        switch ($g) {
//            case $this->contains_substr($g, "white"):
//                $image = "bootwhite.jpg";
//                break;
//            case $this->contains_substr($g, "gray"):
//                $image = "bootgray.jpg";
//                break;
//            case $this->contains_substr($g, "grey"):
//                $image = "bootgray.jpg";
//                break;
//            case $this->contains_substr($g, "black"):
//                $image = "bootblack.jpg";
//                break;
//            case $this->contains_substr($g, "two"):
//                $image = "boottwoblack.jpg";
//                break;
//        }
//        if ($image == "")
//            return $grade;
//        return '<img border="0" src="libraries/ramblers/jsonwalks/de02/images/' . $image . '" width="20" height="20">';
//    }
//    function contains_substr($mainStr, $str, $loc = false) {
//        if ($loc === false)
//            return (strpos($mainStr, $str) !== false);
//        if (strlen($mainStr) < strlen($str))
//            return false;
//        if (($loc + strlen($str)) > strlen($mainStr))
//            return false;
//        return (strcmp(substr($mainStr, $loc, strlen($str)), $str) == 0);
//    }
}
