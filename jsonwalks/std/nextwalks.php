<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdNextwalks extends RJsonwalksDisplaybase {

    private $walkClass = "walk";
    private $feedClass = "walksfeed";

    function DisplayWalks($walks) {

        $walks->sort(RJsonwalksWalk::SORT_DATE, NULL, NULL);
        $items = $walks->allWalks();
        echo "<ul class='" . $this->feedClass . "' >" . PHP_EOL;

        foreach ($items as $walk) {

            $date = "<b>" . $walk->walkDate->format('D, jS F') . "</b>";
            $col2 = "<span itemprop=startDate content=" . $walk->walkDate->format(DateTime::ISO8601) . ">" . $date . "</span>";
            $col2 .= ", <span itemprop=name>" . $walk->title;
            $col2 .= ", " . $walk->distanceMiles . "m/" . $walk->distanceKm . "km</span>";
            $tag = $walk->placeTag;

            echo "<li> <div class='" . $this->walkClass . $walk->status . "' " . $walk->eventTag . "><a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $col2 . $tag . "</a></div>" . PHP_EOL;
            if ($walk->status == "Cancelled") {
                echo "CANCELLED: " . $walk->cancellationReason;
            }
        }

        echo "</ul>" . PHP_EOL;
    }

}
