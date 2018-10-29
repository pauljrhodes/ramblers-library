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
        if ($this->displayGradesSidebar) {
            RJsonwalksWalk::gradeSidebar();
        }
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
            $out = "<span class='" . $this->walkClass . $walk->status . "' " . "><a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $desc . "</a></span>";

            if ($this->displayGradesIcon) {
                $image = $walk->getGradeImage();
                $tooltip = "<span class='ntooltiptext'>" . $walk->nationalGrade . "</span>";
                echo "<div class='nextWalksWithGrade ntooltip'><img src=\"" . $image . "\" alt=\"" . $walk->nationalGrade . "\" />" . $tooltip . $out . "</div>" . PHP_EOL;
            } else {
                echo "<li> " . $out . "</li>" . PHP_EOL;
            } 
            if ($walk->isCancelled()) {
                echo "CANCELLED: " . $walk->cancellationReason;
            }  
        }

        if (!$this->displayGradesIcon) {
            echo "<ul class='" . $this->feedClass . "' >" . PHP_EOL;
        }
        echo "</ul>" . PHP_EOL;
    }

    public function noWalks($no) {
        $this->nowalks = $no;
    }

}
