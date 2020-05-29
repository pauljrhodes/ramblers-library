<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdWalktable extends RJsonwalksDisplaybase {

    private $tableClass = "walksTable";
    private $walkClass = "walk";
    public $link = true;
    public $addDescription = true;
    public $addGroupName = false;
    public $addLocation = true;

    const BR = "<br />";

    function DisplayWalks($walks) {
        echo "</p>";
//        if ($this->displayGradesSidebar) {
//            RJsonwalksWalk::gradeSidebar();
//        }
        $printOn = JRequest::getVar('print') == 1;
        if ($printOn) {
            $doc = JFactory::getDocument();
            $style = 'table { border-collapse: collapse;} table, td, th { border: 1px solid #657291;}td { padding: 5px;}';
            $doc->addStyleDeclaration($style);
        }
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        echo "<table class='gradeTable $this->tableClass'>";
        if ($walks->hasMeetPlace()) {
            echo RHtml::addTableHeader(array("Date", "Meet", "Start", "Title", "Distance", "Grade", "Contact"));
            foreach ($items as $walk) {
                $this->displayWalkForProgrammeTable($walk, true);
            }
        } else {
            echo RHtml::addTableHeader(array("Date", "Start", "Title", "Distance", "Grade", "Contact"));
            foreach ($items as $walk) {
                $this->displayWalkForProgrammeTable($walk, false);
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

    private function displayWalkForProgrammeTable($walk, $hasMeet) {
        $group = "";
        if ($this->addGroupName) {
            $group = self::BR . $walk->groupName;
        }

        $date = "<b>" . $walk->walkDate->format('l, jS F') . "</b>";
        if ($this->printOn) {
            $date = "<div class='" . $this->walkClass . $walk->status . " printon'>" . $date . $group . "</div>";
        } else {
            $date = "<div class='" . $this->walkClass . $walk->status . "'>" . $date . $group . "</div>";
        }
        if ($walk->hasMeetPlace) {
            $meet = $walk->meetLocation->timeHHMMshort . " at " . $walk->meetLocation->description;
            if ($this->addLocation) {
                $meet .= $this->addLocation($walk->meetLocation);
            }
        } else {
            $meet = ".";
        }

        if ($walk->startLocation->exact) {
            $start = $walk->startLocation->timeHHMMshort . " at " . $walk->startLocation->description;
            if ($this->addLocation) {
                $start .= $this->addLocation($walk->startLocation);
            }
        } else {
            $start = ".";
        }
        if ($this->link) {
            if ($this->printOn) {
                $text = $walk->title;
            } else {
                $text = "<a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $walk->title . "</a>";
            }
        } else {
            $text = $walk->title;
        }
        $text = "<strong>" . $text . "</strong>";
        $title = "<div class='" . $walk->status . "'>" . $text . " </div>";
        if ($this->addDescription) {
            $title .= $walk->description;
        }

        $dist = $walk->distanceMiles . "mi / " . $walk->distanceKm . "km";
        $contact = "";
        if ($walk->isLeader) {
            $contact = "Leader";
        } else {
            $contact = "Contact";
        }
        if ($walk->contactName <> "") {
            $contact .= self::BR . "<strong>" . $walk->contactName . "</strong>";
        }
        if ($walk->getEmail() <> "") {
            $contact .= self::BR . $walk->getEmail($this->emailDisplayFormat);
        }
        if ($walk->telephone1 <> "") {
            $contact .= self::BR . $walk->telephone1;
        }
        if ($walk->telephone2 <> "") {
            $contact .= self::BR . $walk->telephone2;
        }
        $grade = $walk->nationalGrade . self::BR . $walk->localGrade;
        if ($this->displayGradesIcon AND ! $this->printOn) {
            $grade = "<div>".$walk->getGradeSpan("middle"). $walk->nationalGrade . self::BR . $walk->localGrade."</div>" ;
        }
        $class = $this->tableClass;
        if ($hasMeet) {
            echo RHtml::addTableRow(array($date, $meet, $start, $title, $dist, $grade, $contact), $class);
        } else {
            echo RHtml::addTableRow(array($date, $start, $title, $dist, $grade, $contact), $class);
        }
    }

    private function addLocation($location) {
        $text = self::BR . $location->gridref . " / " . $location->postcode;
        return $text;
    }

}
