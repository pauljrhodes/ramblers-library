<?php

/**
 * Output aimed a Tourist Information Centres
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksDe02Tic extends RJsonwalksDisplaybase {

    public $groupwebsite = "http://www.derbyramblers.org.uk";

    const BR = "<br />";

    function DisplayWalks($walks) {
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();

        foreach ($items as $walk) {
            If ($walk->isCancelled() == false) {
                $this->displayWalk($walk);
            }
        }
    }

    private function displayWalk($walk) {
        echo "<table>";
        $col1 = "<b>Event:</b>";
        $col2 = "<b>" . $walk->title . "</b>";
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Date:</b>";
        $col2 = $walk->walkDate->format('l, jS F Y');
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Venue:</b>";
        $col2 = "<ul>";
        if ($walk->hasMeetPlace) {
            $col2 .= "<li>Meet at: " . $walk->meetLocation->timeHHMMshort . " at " . $walk->meetLocation->description;
            $col2 .= " [" . $walk->meetLocation->gridref . "]</li>";
        }
        if ($walk->startLocation->exact) {
            $col2 .= "<li>Start at: " . $walk->startLocation->timeHHMMshort . " at " . $walk->startLocation->description;
            $col2 .= " [" . $walk->startLocation->gridref . "]</li>";
            if ($walk->additionalNotes != "") {
                $col2.=", " . $walk->additionalNotes;
            }
        }
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Times:</b>";
        $col2 = "see above";
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Prices:";
        $col2 = "Free - but you may be encouraged to join the Ramblers";
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Short description:</b>";
        $col2 = "Please join " . $walk->groupName . " Ramblers for a " . $walk->nationalGrade;
        $col2 .= " " . $walk->distanceMiles . " mile / " . $walk->distanceKm . " km walk";
        if ($walk->description != "") {
            $col2.=", " . $walk->description;
        }

        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Contact :</b>";
        $col2 = "";
        if ($walk->isLeader) {
            $col2.="Leader ";
        } else {
            $col2.="Contact ";
        }
        $col2.= $walk->contactName;
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Phone number:</b>";
        $col2 = "";
        if ($walk->telephone1 != "") {
            $col2.= " " . $walk->telephone1;
        } else {
            if ($walk->telephone2 != "") {
                $col2.= " " . $walk->telephone2;
            }
        }
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Website:</b>";
        $col2 = $this->groupwebsite . "<br />" . $walk->detailsPageUrl;
        echo RHtml::addTableRow(array($col1, $col2));

        $col1 = "<b>Email:</b>";
        if ($walk->getEmail() == "") {
            $col2 = "progsec@derbyramblers.org.uk";
        } else {
            $col2 = $walk->getEmail();
        }
        echo RHtml::addTableRow(array($col1, $col2));

        echo "</table>";
        echo "<br />";
        echo "<br />";
        echo "<br />";
        echo "<hr />";
        echo "<br />";
        echo "<br />";
        echo "<br />";
    }

}
