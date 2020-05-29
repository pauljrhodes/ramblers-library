<?php

/**
 * Description of mapdraw
 *
 * @author Chris Vaughan
 */
class RLeafletMapdraw extends RLeafletMap {

    private $zoom = 10;
    private $lat = 52.89;
    private $long = -1.48;

    public function __construct() {
        parent::__construct();
    }

    public function setCenter($lat, $long, $zoom) {
        $this->lat = $lat;
        $this->long = $long;
        $this->zoom = $zoom;
    }

    public function display() {
        $this->help_page = "https://maphelp.ramblers-webs.org.uk/draw-walking-route.html";
        $this->options->fullscreen = true;
        $this->options->search = true;
        $this->options->locationsearch = true;
        $this->options->osgrid = true;
        $this->options->mouseposition = true;
        $this->options->postcodes = true;
        $this->options->fitbounds = true;
        $this->options->displayElevation = true;
        $this->options->cluster = false;
        $this->options->draw = true;
        $this->options->print = true;
        $this->options->ramblersPlaces = true;
        $text = "addDrawControl($this->lat,$this->long,$this->zoom)";
        parent::addContent($text);
        parent::display();
        echo "<br/>";
    }

}
