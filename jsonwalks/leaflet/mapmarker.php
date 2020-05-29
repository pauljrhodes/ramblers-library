<?php

/**
 * Description of mapmarker
 *
 * @author Chris Vaughan
 */
class RJsonwalksLeafletMapmarker extends RJsonwalksDisplaybase {

    private $map;
    private $walkClass = "walk";
    private $legendposition = "top";
    public $displayGradesSidebar = true;

    public function __construct() {
        $this->map = new RLeafletMap;
        $this->map->help_page = "https://maphelp.ramblers-webs.org.uk/";
        $options = $this->map->options;
        $options->cluster = true;
        $options->displayElevation = true;
        $options->fullscreen = true;
        $options->search = true;
        $options->locationsearch = true;
        $options->osgrid = true;
        $options->mouseposition = true;
        $options->postcodes = true;
        $options->fitbounds = true;
        $options->print = true;
        //    RLicense::BingMapKey(false);
    }

    public function getMap() {
        return $this->map;
    }

    public function mapHeight($height) {
        $this->map->mapHeight = $height;
    }

    public function mapWidth($width) {
        $this->map->mapWidth = $width;
    }

    public function setLegend($position) {
        $this->legendposition = $position;
    }

    public function DisplayWalks($walks) {
        $legend = '<p><strong>Zoom</strong> in to see where our walks are going to be. <strong>Click</strong> on a walk to see details.</p>
<p><img src="libraries/ramblers/images/marker-start.png" alt="Walk start" height="26" width="16">&nbsp; Start locations&nbsp; <img src="libraries/ramblers/images/marker-cancelled.png" alt="Cancelled walk" height="26" width="16"> Cancelled walk&nbsp; <img src="libraries/ramblers/images/marker-area.png" alt="Walking area" height="26" width="16"> Walk in that area.</p>';
        
        if (isset($this->map)) {
            if (strpos($this->legendposition, "top") !== false) {
                echo $legend;
            }
            $items = $walks->allWalks();
            $text = "";
            foreach ($items as $walk) {
                $marker = $this->addMarker($walk);
                $text.=$marker . PHP_EOL;
            }
            $this->map->addContent($text);
            $this->map->addBounds();
            $this->map->display();
            if (strpos($this->legendposition, "bottom") !== false) {
                echo $legend;
            }
        }
    }

    private function addMarker($walk) {

        $date = $walk->walkDate->format('l, jS F');
        $title = addslashes($walk->title);
        $dist = $walk->distanceMiles . "mile / " . $walk->distanceKm . "km";
        $gr = $walk->startLocation->gridref;
        $long = $walk->startLocation->longitude;
        $lat = $walk->startLocation->latitude;
        $desc = "<b>" . $date . "<br/>" . $title . "<br/>" . $dist . " " . $walk->nationalGrade . "</b>";
        $url = $this->getWalkMapHref($walk, $desc);
        if ($walk->startLocation->exact) {
            $icon = "ramblersMap.markerStart";
        } else {
            $icon = "ramblersMap.markerArea";
        }
        if ($walk->isCancelled()) {
            $icon = "ramblersMap.markerCancelled";
        }
        $class = $this->walkClass . $walk->status;
        $grade = $walk->getGradeImage();
        $grade = "<img src='" . JURI::base() . $grade . "' alt='" . $walk->nationalGrade . "' width='30px'>";
        //  $details = "<div class='" . $class . "'>" . $grade . "<b><a href=&quot;javascript:walkdetails('" . $url . "')&quot; >" . $date . "<br/>" . $title . "<br/>" . $dist . " " . $walk->nationalGrade . "</a></b></div>";
        $details = "<div class='" . $class . "'>" . $grade . $url . "</div>";
        $map = "<a href=&quot;javascript:streetmap('" . $gr . "')&quot; >[OS Map]</a>";
        $directions = "<a href=&quot;javascript:directions(" . $lat . "," . $long . ")&quot; >[Directions]</a>";
        //  var $directions = "<a href='https://maps.google.com?saddr=Current+Location&daddr=" + $lat + "," + $long + "' target='_blank'>[Directions]</a>";
        $popup = $details . $map . $directions;
        // $popup = str_replace('"', "&quot;", $popup);
        $marker = 'addMarker("' . $popup . '", ' . $lat . ', ' . $long . ', ' . $icon . ');';
        //     $marker = "addWalk(ramblersMap.markerList,'" . $this->walkClass . $walk->status . "', '" . $date . "', '" . $title . "', '" . $dist . "', '" . $gr . "', " . $lat . ", " . $long . ", '" . $url . "', " . $icon . ");";
        return $marker;
    }

}
