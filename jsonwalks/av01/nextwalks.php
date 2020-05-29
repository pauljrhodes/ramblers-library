<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan modified by Tony Parsons
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksAv01Nextwalks extends RJsonwalksDisplaybase {

    public $walkClass = "walk";
    public $feedClass = "walksfeed";
    private $nowalks = 10;

    function DisplayWalks($walks) {

        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        $no = 0;
        echo "<ul class='" . $this->feedClass . "' >" . PHP_EOL;

        foreach ($items as $walk) {
            $string = $walk->title;
            $string2 = $walk->additionalNotes;
            $string3 = $walk->description;
//echo "%".$string."<br />";
//echo "%%".$string2."<br />";
//echo "%%%".$string3."<br />";   
            $string1 = strchr($string, "essex");
            if (!($string1 == "")) {
                $walk->title = str_replace("Wessex Wanderer Railway Walk -", "", $walk->title);
                $walk->title = $walk->title . "<span style='color: green';> [WW] </span>";
            }
            $stringx = $string1;
            $string2 = strchr($string3, "Joint walk with Bristol Ramblers");
            if (!($string2 == "")) {
                $walk->description = str_replace("Bristol", "East Devon", $walk->description);
                $stringx = $string2;
            }
            if ($walk->groupName == "Bristol") {
                $stringx = "B";
            }
            if (!($stringx == "")) {
                $no+=1;
                if ($no > $this->nowalks) {
                    break;
                }
                $date = "<b>" . $walk->walkDate->format('D, jS F') . "</b>";
                $col2 = "<span itemprop=startDate content=" . $walk->walkDate->format(DateTime::ISO8601) . ">" . $date . "</span>";
                $col2 .= ", <span itemprop=name>" . $walk->title;
//            if (!($walk->localGrade=="")) {
//            $col2 .=" ".$walk->localGrade ;
//}
                if ($walk->distanceMiles > 0) {
                    $col2 .= ", " . $walk->distanceMiles . "mi/" . $walk->distanceKm . "km";
                }
                if (substr($walk->additionalNotes, 3, 1) == "+") {
                    $col2 .="<span style='color: red';> EXTRA WALK </span>";
                }
                if (substr($walk->additionalNotes, 3, 1) == "*") {



                    $col2 .="<span style='color: red';> MODIFIED WALK ";
                    $iii = strpos($walk->additionalNotes, "*", 5);
                    $sss = substr($walk->additionalNotes, 3, $iii - 2);
                    if ($iii > 4) {
                        $col2 .= $sss;
                    }
                    $col2 .="</span>";
                }
                if (substr($walk->additionalNotes, 3, 1) == "/") {
                    $col2 .="<span style='color: red';> REPLACEMENT WALK </span>";
                }
                $col2 .= "</span>";
//#            $tag = $walk->placeTag;
                $tag = "";
//# $ev=$walk->eventTag;
                $ev = "";
                echo "<li> <div class='" . $this->walkClass . $walk->status . "' " . $ev . "><a href='" . $walk->detailsPageUrl . "' target='_blank' >" . $col2 . $tag . "</a></div>" . PHP_EOL;
                if ($walk->isCancelled()) {
                    echo "CANCELLED: " . $walk->cancellationReason;
                }
            }
        }

        echo "</ul>" . PHP_EOL;
    }

    function noWalks($no) {
        $this->nowalks = $no;
    }

}
