<?php

/**
 * @version		0.0
 * @package		Simple JSON Feed reader
 * @author              Chris Vaughan Ramblers-webs.org.uk
 * @copyright           Copyright (c) 2014 Chris Vaughan. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class RJsonwalksFeed {

    private $walks;
    private $rafeedurl;
    private $displayLimit = 0;
    private $srfr;

    public function __construct($rafeedurl) {
        $this->rafeedurl = trim($rafeedurl);
        $this->walks = new RJsonwalksWalks(NULL);
        $CacheTime = 15; // minutes
        $time = getdate();
        if ($time["hours"] < 6) {
            $CacheTime = 7200; // 12 hours, rely on cache between midnight and 6am
        }
        $cacheLocation = $this->CacheLocation();
        $this->srfr = new RFeedhelper($cacheLocation, $CacheTime);
        $this->srfr->setReadTimeout(30);
        $this->readFeed($this->rafeedurl);
    }

    private function readFeed($rafeedurl) {
        $properties = array("id", "status", "difficulty", "strands", "linkedEvent", "festivals",
            "walkContact", "linkedWalks", "linkedRoute", "title", "description", "groupCode", "groupName",
            "additionalNotes", "date", "distanceKM", "distanceMiles", "finishTime", "suitability",
            "surroundings", "theme", "specialStatus", "facilities", "pace", "ascentMetres", "ascentFeet",
            "gradeLocal", "attendanceMembers", "attendanceNonMembers", "attendanceChildren", "cancellationReason",
            "dateUpdated", "dateCreated", "media", "points", "groupInvite", "isLinear", "url");

        $result = $this->srfr->getFeed($rafeedurl);
        $json = RErrors::checkJsonFeed($rafeedurl, "Walks", $result, $properties);
        If ($json !== null) {
            $this->walks = new RJsonwalksWalks($json);
            unset($json);
        }
    }

    public function setNewWalks($days) {
        $this->walks->setNewWalks($days);
    }

    public function setDisplayLimit($no) {
        $this->displayLimit = $no;
    }

    public function filterDistanceFrom($easting, $northing, $distanceKm) {
        $this->walks->filterDistanceFrom($easting, $northing, $distanceKm);
    }

    public function filterDistanceFromLatLong($lat, $lon, $distanceKm) {
        $this->walks->filterDistanceFromLatLong($lat, $lon, $distanceKm);
    }

    public function filterGroups($filter) {
        $this->walks->filterGroups($filter);
    }

    public function filterStrands($filter) {
        $this->walks->filterStrands($filter);
    }

    public function filterTitle($filter, $option = 'remove') {
        $this->walks->filterTitle($filter, $option);
    }

    public function filterTitleContains($filter, $option = 'remove') {
        $this->walks->filterTitleContains($filter, $option);
    }

    public function filterNationalGrade($grades) {
        $this->walks->filterNationalGrade($grades);
    }

    public function filterFestivals($filter) {
        $this->walks->filterFestivals($filter);
    }

    public function filterDateRange($datefrom, $dateto) {
        if (!is_a($datefrom, 'DateTime')) {
            echo "filterDateRange: first parameter is NOT Datetime";
            return;
        }
        if (!is_a($dateto, 'DateTime')) {
            echo "filterDateRange: second parameter is NOT Datetime";
            return;
        }
        $dateto->setTime(0, 0, 0);
        $datefrom->setTime(0, 0, 0);
        $this->walks->filterDateRange($datefrom, $dateto);
    }

    public function filterDayofweek($days) {
        $this->walks->filterDayofweek($days);
    }

    public function noWalks($no) {
        $this->walks->noWalks($no);
    }

    public function numberWalks() {
        return count($this->walks->allWalks());
    }

    public function walksInFuture($period) {
        $this->walks->walksInFuture($period);
    }

    public function noCancelledWalks() {
        $number = 0;
        $items = $this->walks->allWalks();
        foreach ($items as $walk) {
            if ($walk->isCancelled()) {
                $number+=1;
            }
        }
        return $number;
    }

    public function display($displayclass) {
        if ($this->walks == null) {
            echo "No walks found";
            return;
        }
        if ($this->walks->totalWalks() == 0) {
           // echo "No walks found";
          //  return;
        }
        $document = JFactory::getDocument();
        $document->addStyleSheet('libraries/ramblers/jsonwalks/css/ramblerswalks.css');
        $document->addScript("libraries/ramblers/js/ramblerswalks.js", "text/javascript");
        $printOn = JRequest::getVar('print') == 1;
        if ($printOn) {
            $style = 'BODY {color: #000000;}';
            $document->addStyleDeclaration($style);
        }
        $folder = "ramblersBase.folderbase='" . JURI::base(true) . "';";
        $out = "window.addEventListener('load', function(event) {
            ramblersBase = new RamblersBase();" . $folder .
                "  });";
        $document->addScriptDeclaration($out, "text/javascript");
        if ($this->displayLimit == 0 OR $printOn) {
            $displayclass->DisplayWalks($this->walks);
        } else {
            $groups = $this->createGroupsOfWalks();
            $numItems = count($groups);
            $i = 1;
            $blockId = 1;
            $block = "ra_block" . $blockId;
            foreach ($groups as $walks) {
                if ($i == 1) {
                    echo "<div id='" . $block . "' style='display: block'>" . PHP_EOL;
                } else {
                    echo "<div id='" . $block . "' style='display: none'>" . PHP_EOL;
                }
                $displayclass->DisplayWalks($walks);
                if ($i === $numItems) {
                    echo "<p><b>End of list</b></p>";
                } else {
                    $more = "ra_more" . $blockId;
                    $blockId+=1;
                    $block = "ra_block" . $blockId;
                    echo "<div class='ra_walks_more' id='" . $more . "' ><p></p><a " . $this->getTogglePair($block, $more) . " >Display more walks ...</a><p>&nbsp;</p><p>&nbsp;</p></div>";
                }
                echo "</div>" . PHP_EOL;
                $i+=1;
            }
        }
    }

    public function displayIcsDownload($name, $pretext, $linktext, $posttext) {
        $events = new REventGroup(); // create a group of events
        $events->addWalks($this); // add walks to the group of events
        $display = new REventDownload();
        $display->setPreText($pretext);
        $display->setLinkText($linktext);
        $display->setPostText($posttext);
        $display->Display("de02walks", $events); // display walks information
// is this line correct and is function used
    }

    private function getTogglePair($one, $two) {
        return ' onclick="ra_toggle_visibilities(\'' . $one . '\',\'' . $two . '\')"';
    }

    public function getWalks() {
        return $this->walks;
    }

    public function clearCache() {
        $this->srfr->clearCache();
// reread feed
        $this->readFeed($this->rafeedurl);
    }

    private function CacheLocation() {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        return 'cache' . DS . 'ra_feed';
    }

    private function createGroupsOfWalks() {
        $groups = array();
        $allwalks = $this->walks->allWalks();
        $no = 0;
        $walks = new RJsonwalksWalks(null);
        $groups[] = $walks;
        foreach ($allwalks as $walk) {
            $no +=1;
            $walks->addWalk($walk);
            if ($no >= $this->displayLimit) {
                $no = 0;
                $walks = new RJsonwalksWalks(null);
                $groups[] = $walks;
            }
        }
        return $groups;
    }

    
    public function filterFeed($filter) { // filter by component filter subform
        if ($filter->titlecontains !== '') {
            $this->filterTitleContains($filter->titlecontains, "keep");
        }
        if ($filter->titledoesntcontains !== '') {
            $this->filterTitleContains($filter->titledoesntcontains, "remove");
        }
        if ($filter->titleequals !== '') {
            $this->filterTitle($filter->titleequals, "keep");
        }
        if ($filter->titledoesntequal !== '') {
            $this->filterTitle($filter->titledoesntequal, "remove");
        }
        $limit = intval($filter->limit);
        if ($limit > 0) {
            $this->noWalks($limit);
        }
        $dist = floatval($filter->filterdistance);
        if ($dist > 0) {
            $this->filterDistanceFromLatLong(floatval($filter->filtercentrelatitude), floatval($filter->filtercentrelongitude), $dist);
        }
        if (count($filter->filternationalgrades) > 0) {
            $this->filterNationalGrade($filter->filternationalgrades);
        }
        if (count($filter->filterdaysofweek) > 0) {
            $this->filterDayofweek($filter->filterdaysofweek);
        }
        if ($filter->filterstrand !== '') {
            $this->filterStrands($filter->filterstrand);
        }
    }

}
