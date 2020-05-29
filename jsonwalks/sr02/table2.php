<?php

/**
 * Description of WalksDisplay
 *
 * @author Brian Smith
 * East Surrey Walkers
 * 18-mar-2019 add sandwich & beer icons
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksSr02Table2 extends RJsonwalksDisplaybase {

    private $tableClass = "sr02prog";
    private $walkClass = "walk";
    public $link = true;
    public $addDescription = true;
    public $addGroupName = false;
    public $addLocation = true;

    const BR = "<br />";

    function DisplayWalks($walks) {
        echo "</p>";
        $printOn = JRequest::getVar('print') == 1;
        if ($printOn) {
            $doc = JFactory::getDocument();
            $style = 'table { border-collapse: collapse;} table, td, th { border: 1px solid #657291;}td { padding: 5px;}';
            $doc->addStyleDeclaration($style);
        }
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        if ($this->tableClass != "") {
            echo "<table class='" . $this->tableClass . "'>" ;
        } else {
            echo "<table>";
        }
        echo RHtml::addTableHeader(array("Date/Time", "Leader/Contact", "Details", "Distance"));
		
        foreach ($items as $walk) {
 			if ($walk->status != "Cancelled") {
				$this->displayWalksProgramme($walk);
			}
        }

        echo "</table>" . PHP_EOL;
    }

    function setTableClass($class) {
        $this->tableClass = $class;
    }

    function setWalksClass($class) {
        $this->walksClass = $class;
    }

    function setWalkClass($class) {
        $this->walkClass = $class;
    }

    private function displayWalksProgramme($walk) {
        $group = "";
        if ($this->addGroupName) {
            $group =  self::BR . $walk->groupName;
        }
		/* date/time */
		$day = $walk->walkDate->format('l') ;
        $date = $day . self::BR . $walk->walkDate->format('d-M') . self::BR . $walk->startLocation->timeHHMMshort;

		/* Contact */
        $contact = "";
        if ($walk->contactName != "") {
            $contact .=  "<strong>" . $walk->contactName . "</strong>"  ;
        }
        if ($walk->email != "") {
            $contact .=  self::BR . $walk->email;
        }
        if ($walk->telephone1 != "") {
            $contact .=  self::BR . '<font size="-1">' . $walk->telephone1 . '</font>';
        }
        if ($walk->telephone2 != "") {
            $contact .=  self::BR . '<font size="-1">' . $walk->telephone2 . '</font>';
//            $contact .=  self::BR . $walk->telephone2;
        }
		
		
		/* Description */
        if ($this->link) {
            $text = "<a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $walk->title . "</a>";
        } else {
            $text = $walk->title;
        }
        $description = "<strong>" . $text . " </strong>" . self::BR . $walk->description;
        if ($walk->startLocation->description != "") {
			$description .= self::BR . "<i>" . $walk->startLocation->description . "</i>";
		}
		$description .= self::BR . "GridRef: <a href='http://www.streetmap.co.uk/loc/" . $walk->startLocation->gridref . "&z=120' target='_blank'>" . $walk->startLocation->gridref . "</a>"  ;
		$description .=  "  PostCode: " . $walk->startLocation->postcode . self::BR . $walk->additionalNotes ;
		
		
		/* Distance */
       $dist = $walk->distanceMiles . " miles " . self::BR . "(" . $walk->distanceKm . "km)";
	   $dist .= self::BR . strtoupper($walk->nationalGrade)  ;
		
		
		/* Grade/Style */
		$grade = $this->getEswClass($walk) ;

		/* Picnic or Pub icon */
		if (stristr($walk->additionalNotes, "picnic")) {
			$dist .= self::BR . '<img src="../../images/group/Sandwich-icon.png" title="Picnic Required" width="24" height="24" align="left"/>' ;
		}
		else { 
			if (stristr($walk->additionalNotes, "pub")) {
				$dist .= self::BR . '<img src="../../images/group/beer.png" title="Pub Lunch" width="24" height="24" align="left"/>' ;
			}
		}
		

		if ($grade != "") {
			echo RHtml::addTableRow(array($date, $contact, $description, $dist), $grade);
		}
		else {
			echo RHtml::addTableRow(array($date, $contact, $description, $dist));
		}
		
     }

    private function addLocation($location) {
        $text =  self::BR.$location->gridref . " / " . $location->postcode;
        return $text;
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

}
