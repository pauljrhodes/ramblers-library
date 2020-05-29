<?php

/**
 * Description of mapdraw
 *
 * @author Chris Vaughan
 */
class RLeafletMapdraw2 extends RLeafletMap {

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
        $text = "addDrawControl($this->lat,$this->long,$this->zoom);control=L.Routing.control({
            waypointMode: 'snap',
            routeWhileDragging: true,
            profile: 'walking',
             router: L.Routing.mapbox('pk.eyJ1IjoiY2hyaXN2YXVnaGFuIiwiYSI6ImNrMGZkMHdmejAwZXEzY253eTV1Znd2YncifQ.kCx-9Kq-SFA0UOsHvIMDMg'),
            waypoints: [L.latLng(52.93315, -1.498884),L.latLng(52.9504131,-1.578026),L.latLng(52.869304, -1.533357691),L.latLng(52.93315, -1.498884)]
            }
            ).addTo(ramblersMap.map);control.hide();";
        parent::addContent($text);
        parent::display();
            $document = JFactory::getDocument();
        // Leaflet
        $document->addStyleSheet("libraries/ramblers/vendors/leaflet-routing-machine-3.2.12/dist/leaflet-routing-machine.css", "text/css");
        $document->addScript("libraries/ramblers/vendors/leaflet-routing-machine-3.2.12/dist/leaflet-routing-machine.js", "text/javascript");
   
        echo "<br/>";
    }

}
