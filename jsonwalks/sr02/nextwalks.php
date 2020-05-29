<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
   Modified Brian Smith Easy Surrey Walkers
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksSr02Nextwalks extends RJsonwalksDisplaybase {

    public $walkClass = "nextwalk";
    public $feedClass = "walksfeed";
    private $nowalks = 5;

    function DisplayWalks($walks) {
        $schemawalks = array();
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        $no = 0;
        echo "<ul class='" . $this->feedClass . "' >" . PHP_EOL;

        foreach ($items as $walk) {
            $no+=1;
            if ($no > $this->nowalks) {
                break;
            }
            $date = "<b>" . $walk->walkDate->format('l, jS F') . "</b>:";
            $desc = $date . "<br /> " . $walk->title;
            if ($walk->distanceMiles > 0) {
                $desc .= ", " . $walk->distanceMiles . "mi<i>(" . $walk->distanceKm . "km)</i>";
            }

            echo "<li> <div class='" . $this->walkClass . $walk->status . "' " . "><a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $desc . "</a></div>" . PHP_EOL;
            if ($walk->isCancelled()) {
                echo "CANCELLED: " . $walk->cancellationReason;
            } else {
                $performer = new RJsonwalksStructuredperformer($walk->groupName);
                $location = new RJsonwalksStructuredlocation($walk->startLocation->description, $walk->startLocation->postcode);
                $schemawalk = new RJsonwalksStructuredevent($performer, $location);
                if ($walk->description == "") {
                    $schemawalk->description = $desc;
                } else {
                    $schemawalk->description = $walk->description;
                }
                $schemawalk->enddate = $walk->walkDate->format('Y-m-d');
                $schemawalk->image = "http://www.ramblers-webs.org.uk/images/ra-images/logos/standard/logo92.png";
                $schemawalk->name = $desc;
                $schemawalk->startdate = $schemawalk->enddate;
                $schemawalk->url = $walk->detailsPageUrl;

                $schemawalks[] = $schemawalk;
            }
        }

        echo "</ul>" . PHP_EOL;
        $script = json_encode($schemawalks);
        $script = str_replace('"context":', '"@context":', $script);
        $script = str_replace('"type":', '"@type":', $script);
        $script = str_replace('\/', '/', $script);
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($script, "application/ld+json");
    }

    function noWalks($no) {
        $this->nowalks = $no;
    }

}
