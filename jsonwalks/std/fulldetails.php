<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdFulldetails extends RJsonwalksDisplaybase {

    private $walksClass = "walks";
    private $walkClass = "walk";

    const BR = "<br />";

    function DisplayWalks($walks) {
        $document = JFactory::getDocument();
        JHtml::_('jquery.framework');
        $document->addStyleSheet(JURI::base() . 'modules/mod_sp_accordion/style/style2.css');
        $document->addScript(JURI::base() . 'modules/mod_sp_accordion/js/sp-accordion.js', "text/javascript");
        echo "</p><h1>", "List of walks </h1>";
        $walks->sort(RJsonwalksWalks::SORT_DATE, NULL, NULL);
        $items = $walks->allWalks();

        echo "<div class='" . $this->walksClass . "' >" . PHP_EOL;
        echo ' <script type="text/javascript">' . PHP_EOL;
        echo 'jQuery(function($) {' . PHP_EOL;
        echo "$('#accordion_sp1_id007').spAccordion({" . PHP_EOL;
        echo 'hidefirst: 1 });' . PHP_EOL;
        echo '});' . PHP_EOL;
        echo '</script>' . PHP_EOL;
        echo '<div id="accordion_sp1_id007" class="sp-accordion sp-accordion-style2 ">';
        foreach ($items as $walk) {
            $thiscontact = $walk->contactName . "  " . $walk->telephone1;

            echo '<div class="sp-accordion-item">';
            echo '<div class="toggler">';
            echo '<span><span>';

            $this->displayWalkSummary($walk);
            echo '</span></span>';
            echo '</div>';
            echo '<div class="clr"></div>';
            echo '<div class="sp-accordion-container" style="display: none;">';
            echo '<div class="sp-accordion-inner">';
            $this->displayWalkDetails($walk);
            echo "<hr/>" . PHP_EOL;
            echo '</div></div>';

            echo "</div>" . PHP_EOL;
        }
        echo "</div>" . PHP_EOL;
        echo '</div></div>';
    }

    function setWalksClass($class) {
        $this->walksClass = $class;
    }

    function setWalkClass($class) {
        $this->walkClass = $class;
    }

    private function displayWalkSummary($walk) {

        $text = "<b>" . $walk->walkDate->format('l, jS F Y') . "</b>" . PHP_EOL;

        //    if ($walk->hasMeetingPlace) {
        //       $text .= ", " . $walk->meetingTime->format('ga') . " at " . $walk->meetingLocation->description;
        //   }
        //   if ($walk->startingPlaceExact) {
        //       $text .= ", " . $walk->startTime->format('ga') . " at " . $walk->startLocation->description;
        //   }

        $text .= ", " . $walk->title . " ";
        $text .= ", " . $walk->distanceMiles . "m / " . $walk->distanceKm . "km";
        //   if ($walk->isLeader) {
        //       $text.=", Leader " . $walk->contactName . " " . $walk->telephone1;
        //   } else {
        //       $text.=", Contact " . $walk->contactName . " " . $walk->telephone1;
        //   }
        //   echo "<div class='" . $this->walkClass . "' >" . PHP_EOL;
        echo $text . PHP_EOL;
        //  echo "</div>" . PHP_EOL;
    }

    private function displayWalkDetails($walk) {
        
        echo "<div class='walk'>";
        echo "<div class='description'><b>Description</b>: " . $walk->description . "</div>";
        echo "<div class='additionalnotes'><b>Additional Notes</b>: " . $walk->additionalNotes . "</div>";
        echo "<div class='distance'><b>Distance</b>: " . $walk->distanceMiles . "m / " . $walk->distanceKm . "km" . "</div>";

        if ($walk->hasMeetPlace) {
            echo  "<div class='meetingplace'><b>Meeting Place</b>";
            echo  "<div class='meetingtime'><b>Time</b>: " . $walk->meetTime->format('ga') . "</div>";
            $out=  $this->addLocationInfo($walk->meetLocation);
            echo $out;
            echo "</div>";
        } else {
            echo  "<div class='nomeetingplace'><b>No meeting place specified</b>";
            echo "</div>";
        }
        if ($walk->startPlaceExact) {
            echo  "<div class='startingplace'><b>Starting Place</b>: ";
            echo  "<div class='startingtime'><b>Time</b>: " . $walk->startTime->format('ga') . "</div>";
        } else {
            echo  "<div class='nostartplace'><b>No start place - Rough location only</b>: ";
        }
        echo  $this->addLocationInfo($walk->startLocation);

        echo "</div>";

        if ($walk->isLinear) {
            echo  "<div class='linearwalk'><b>Linear Walk</b>: ";
            echo  $this->addLocationInfo($walk->finishLocation);
            echo "</div>";
        } else {
            echo  "<div class='circularwalk'><b>Circular walk</b> ";
            echo "</div>";
        }
        echo  "<div class='dificulty'><b>Difficulty</b>: ";
        echo  "<div class='nationalgrade'><b>National Grade</b>: " . $walk->nationalGrade . "</div>";
        echo  "<div class='localgrade'><b>Local Grade</b>: " . $walk->localGrade . "</div>";
        echo  "<div class='pace'><b>Pace</b>: " . $walk->pace . "</div>";
        echo  "<div class='ascent'><b>Ascent</b>: " . $walk->ascentFeet . " ft " . $walk->ascentMetres . " ms</div>";
        echo  "</div>";
  
        echo  "<div class='Contact'><b>Contact</b>: ";
        echo  "<div class='contactname'><b>Name</b>: " . $walk->contactName . "</div>";
        echo  "<div class='email'><b>Email</b>: " . $walk->email . "</div>";
        echo  "<div class='telphone'><b>Telephone</b>: " . $walk->telephone1 ." ". $walk->telephone2. "</div>";
        
        echo  "</div>";
  
        echo  "</div>";


        
    }

    private function addLocationInfo($location) {

        $out = "<div class='place'><b>Place</b>: " . $location->description . "</div>";
        $out.= "<div class='gridref'><b>Grid Ref</b>: " . $location->gridref . "</div>";
        $out.= "<div class='logitude'><b>Logitude</b>: " . $location->longitude . "</div>";
        $out.= "<div class='latitude'><b>Latitude</b>: " . $location->latitude . "</div>";
        $out.= "<div class='postcode'><b>Postcode</b>: " . $location->postcode . "</div>";

        return $out;
    }

}
