<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdNextwalks extends RJsonwalksDisplaybase {

    public $walkClass = "nextwalk";
    public $feedClass = "walksfeed";
    private $nowalks = 5;

    function DisplayWalks($walks) {
        $schemawalks = array();
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        $no = 0;
        if (!$this->displayGradesIcon) {
            echo "<ul class='" . $this->feedClass . "' >" . PHP_EOL;
        }
        foreach ($items as $walk) {
            $no+=1;
            if ($no > $this->nowalks) {
                break;
            }
            $date = "<b>" . $walk->walkDate->format('D, jS F') . "</b>";
            $desc = $date . ", " . $walk->title;
            if ($walk->distanceMiles > 0) {
                $desc .= ", " . $walk->distanceMiles . "mi/" . $walk->distanceKm . "km";
            }
            $out = "<span class='" . $this->walkClass . $walk->status . "' " . ">";
            $out .= $this->getWalkHref($walk, $desc);
            $out .= "</span>";

            if ($this->displayGradesIcon) {
                echo "<span class='nextWalksWithGrade'>" . $walk->getGradeSpan("left") . $out . "</span>\r\n";
            } else {
                echo "<li>" . $out . "</li>" . PHP_EOL;
            }
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

        if (!$this->displayGradesIcon) {
            echo "</ul>" . PHP_EOL;
        }
        $script = json_encode($schemawalks);
        $script = str_replace('"context":', '"@context":', $script);
        $script = str_replace('"type":', '"@type":', $script);
        $script = str_replace('\/', '/', $script);
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($script, "application/ld+json");
    }

    public function noWalks($no) {
        $this->nowalks = $no;
    }
}