<?php

/**
 * Description of RJsonwalksStdWalktable3col
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksSx05Programme extends RJsonwalksDisplaybase {

    private $lastValue = "";
    private $tableClass = "";
    private $walkClass = "walk";
    public $link = true;
    public $addDescription = true;
    public $addLocation = true;

    const BR = "<br />";

    //public $PLAN_WALKS_SUM = "        TIME TO START PLANNING YOUR WALKS FOR THE SUMMER PROGRAMME";
    //   public $PLAN_WALKS_WIN = "        TIME TO START PLANNING YOUR WALKS FOR THE WINTER PROGRAMME";
    //  public $SUBMIT_WALKS_SUM = "        TIME TO SUBMIT YOUR WALKS FOR THE SUMMER PROGRAMME";
    //   public $SUBMIT_WALKS_WIN = "        TIME TO SUBMIT YOUR WALKS FOR THE WINTER PROGRAMME";


    function DisplayWalks($walks) {
        echo "</p>";
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();

        foreach ($items as $walk) {
            $thismonth = $walk->walkDate->format('F');
            if ($thismonth <> $this->lastValue) {
                if ($this->lastValue <> "") {
                    echo "</table>" . PHP_EOL;
                }
                $this->lastValue = $thismonth;
                switch ($thismonth) {
                    case "December":
                        $outdate = "<h2>";
                        $outdate.= $thismonth . "<h4>" . "        TIME TO START PLANNING YOUR WALKS FOR THE SUMMER PROGRAMME";
                        $outdate.= "</h2>" . PHP_EOL;
                        echo $outdate;
                        break;
                    case "June":
                        $outdate = "<h2>";
                        $outdate.= $thismonth . "</h2>" . "<h4>" . "        TIME TO START PLANNING YOUR WALKS FOR THE WINTER PROGRAMME";
                        $outdate.= "</h4>" . PHP_EOL;
                        echo $outdate;
                        break;
                    case "July":
                        $outdate = "<h2>";
                        $outdate.= $thismonth . "</h2>" . "<h4>" . "        TIME TO SUBMIT YOUR WALKS FOR THE WINTER PROGRAMME";
                        $outdate.= "</h4>" . PHP_EOL;
                        echo $outdate;

                        break;
                    case "January":
                        $outdate = "<h2>";
                        $outdate.= $thismonth . "</h2>" . "<h4>" . "        TIME TO SUBMIT YOUR WALKS FOR THE SUMMER PROGRAMME";
                        $outdate.= "</h4>" . PHP_EOL;
                        echo $outdate;
                        break;
                    default:
                        echo "<h2>" . $thismonth . "</h2>" . PHP_EOL;
                        break;
                }
                echo "<table width='95%'>" . PHP_EOL;
            }

            $this->displayWalkForProgrammeTable($walk);
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

    private function displayWalkForProgrammeTable($walk) {

        $date = "<div class='" . $this->walkClass . $walk->status . "'><b>" . $walk->walkDate->format('l, jS') . "</b>";
        $date.= $this->addLocation($walk->startLocation);
        $date.=self::BR . $walk->distanceMiles . " miles</div>";

        $description = "";
        if ($walk->hasMeetPlace) {
            $description .= $walk->meetLocation->timeHHMMshort . ". " . $walk->meetLocation->description;
        }
        if ($description != "") {
            $description.=", ";
        }
        if ($walk->startLocation->exact) {
            $description .= $walk->startLocation->description . " " . $walk->startLocation->timeHHMMshort;
        }
        if ($this->link) {
            $text = "<a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $walk->title . "</a>";
        } else {
            $text = $walk->title;
        }
        //  $title = "<div class='" . $walk->status . "'>" . $text . " </div>";
        if ($this->addDescription) {
            $description .= self::BR . $walk->description;
        }


        $contact = "";
        if ($walk->contactName <> "") {
            $contact .= $walk->contactName . self::BR;
        }
        if ($walk->telephone1 <> "") {
            $contact .= $walk->telephone1 . self::BR;
        }
        if ($walk->telephone2 <> "") {
            $contact .= $walk->telephone2 . self::BR;
        }
        $out = "<tr>";
        $out.="<td>" . $date . "</td>";
        $out.="<td>" . $description . "</td>";
        $out.="<td nowrap>" . $contact . "</td>";

        $out.="</tr>" . PHP_EOL;
        echo $out;


        //   echo RHtml::addTableRow(array($date, $description, $contact));
    }

    private function addLocation($location) {
        $text = self::BR . $location->gridref . " / " . $location->postcode;
        return $text;
    }

}
