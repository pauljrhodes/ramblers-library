<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 *
 * Modified for ESW by Brian Smith
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksSr02Programme extends RJsonwalksDisplaybase {

    private $lastValue = "";
    private $walksClass = "sr02prog";
    private $walkClass = "walk";
	private $grade="";
    public $addDescription = true;

    const BR = "<br />";

    function DisplayWalks($walks) {
      $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
         $items = $walks->allWalks();
//        echo "<div class='" . $this->walksClass . "' >" . PHP_EOL;   ** BSS disable walkNew class
        echo "<div class='walkPublished' >" . PHP_EOL;
        foreach ($items as $walk) {
            $thismonth = $walk->walkDate->format('F');
            if ($thismonth <> $this->lastValue) {
                if ($this->lastValue <> "") {
                    echo "</table>" . PHP_EOL;
                }
                $this->lastValue = $thismonth;
                echo "<h2>" . $thismonth . "</h2>" . PHP_EOL;
                echo "<table class='sr02prog'>" . PHP_EOL;
            }

            $this->displayWalk($walk);
        }
        echo "</table>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    }

    function setWalksClass($class) {
        $this->walksClass = $class;
    }

    function setWalkClass($class) {
        $this->walkClass = $class;
    }

    private function displayWalk($walk) {
//        $col1 = $this->getGradeImage($walk->localGrade);  ** BSS use national grade switch
        $col1 = $this->getGradeImage($walk->nationalGrade); 

        $col2 = "<b>" . $walk->walkDate->format('l, jS') . "</b>";
        if ($walk->hasMeetPlace) {
            $col2 .= ", " . $walk->meetLocation->timeHHMMshort . " at " . $walk->meetLocation->description;
        }
        if ($walk->startLocation->exact) {
            $col2 .= ", " . $walk->startLocation->timeHHMMshort . " at " . $walk->title;
            $col2 .= " [" . $walk->startLocation->gridref . "]";
			$col2 .= " PC: " . $walk->startLocation->postcode . "." ;
			}

//        $col2 .= ", " . $walk->title;
        if ($this->addDescription) {
            if ($walk->description != "") {
                $col2.=", ".$walk->description;
            }
        }
        $col2 .= ", <strong>" . $walk->distanceMiles . "mi/" . $walk->distanceKm . "km</strong>, ";
		$col2 .=  $walk->nationalGrade ;

        if ($walk->isLeader) {
            $col2.=", Leader " ;
        } else {
            $col2.=", Contact " ;
        }
         $col2.= $walk->contactName ;
         if ($walk->telephone1 != "") {
            $col2.= " " . $walk->telephone1;
        }
        if ($walk->telephone2 != "") {
             $col2.= ", " . $walk->telephone2;
        }
        $col2 = "<div class='walkPublished'>" . $col2 . "</div>";
		
		$grade = $this->getEswClass($walk) ;
 
        echo RHtml::addTableRow(array($col1, $col2), $grade);
    }

    private function getGradeImage($grade) {
         return "<strong>&#9733;</strong>";
    }
	
	private function getEswClass($walk) {
	   $class = "leisurely" ;
	   $day="";
	
	   $day = $walk->walkDate->format('l') ;
		
       if (($walk->isLinear) && ($day == "Wednesday")) {
			$class = "linear" ;
		}
		else {
			switch ($walk->nationalGrade) {
				case "Easy" : $class = "easy" ;
				break ;
				case "Leisurely" : $class = "leisurely" ;
				break ;
				case "Moderate" : $class = "moderate" ;
				break ;
				case "Strenuous" : $class = "strenuous" ;
				break ;
			}
		}
		return $class ;
	}
	
    function contains_substr($mainStr, $str, $loc = false) {
        if ($loc === false)
            return (strpos($mainStr, $str) !== false);
        if (strlen($mainStr) < strlen($str))
            return false;
        if (($loc + strlen($str)) > strlen($mainStr))
            return false;
        return (strcmp(substr($mainStr, $loc, strlen($str)), $str) == 0);
    }

}
