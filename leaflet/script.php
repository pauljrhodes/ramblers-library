<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Joomla\CMS\Component\ComponentHelper;
use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper;

class RLeafletScript {

    private $command = 'noDirectAction';
    private $dataObject = null;

    public function __construct() {
        
    }

    public function setCommand($command) {
        $this->command = $command;
    }

    public function setDataObject($value) {
        $this->dataObject = $value;
    }

    public function add($options) {
        $version = new JVersion();
        $jv = $version->getShortVersion();
        $document = JFactory::getDocument();
        $options->setLicenses();
        if ($this->command !== "noDirectAction") {
            echo "<div id='" . $options->divId . "'></div>" . PHP_EOL;
        }
        $text = "window.addEventListener('load', function () {" . PHP_EOL;
        $text .= "var mapOptions='" . addslashes(json_encode($options)) . "';" . PHP_EOL;
        // set data object for this command      
        if ($this->dataObject !== null) {
            $text .= "var data='" . addslashes(json_encode($this->dataObject)) . "';" . PHP_EOL;
        } else {
            $text .= "var data=null;" . PHP_EOL;
        }

        $text .= "ra.bootstrapper('" . $jv . "','" . $this->command . "',mapOptions,data);});" . PHP_EOL;
        $document->addScriptDeclaration($text, "text/javascript");

        $this->addScriptsandStyles($options);
    }

    private function addScriptsandStyles($options) {

        JHtml::_('jquery.framework');

        $document = JFactory::getDocument();

        RLoad::addScript("media/lib_ramblers/js/ra.js", array("type" => "text/javascript"));
        // Leaflet
        $document->addStyleSheet("https://unpkg.com/leaflet@1.9.4/dist/leaflet.css");
        RLoad::addScript("https://unpkg.com/leaflet@1.9.4/dist/leaflet.js");
        // RLoad::addScript("media/lib_ramblers/vendors/leaflet/leaflet.js", array("type" => "text/javascript"));
        RLoad::addScript("media/lib_ramblers/leaflet/ra.leafletmap.js");
        RLoad::addStyleSheet("media/lib_ramblers/leaflet/ramblersleaflet.css");
        RLoad::addScript("https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.15.0/proj4.js");
        RLoad::addScript("https://cdnjs.cloudflare.com/ajax/libs/proj4leaflet/1.0.2/proj4leaflet.min.js");

        $path = "media/lib_ramblers/vendors/Leaflet.fullscreen-1.0.2/dist/";
        RLoad::addScript($path . "Leaflet.fullscreen.min.js");
        RLoad::addStyleSheet($path . "leaflet.fullscreen.css");

        if ($options->displayElevation !== null) {
            // elevation
            $document->addScript("https://d3js.org/d3.v3.min.js", array("type" => "text/javascript"));
            $path = "media/lib_ramblers/vendors/Leaflet.Elevation-0.0.4-ra/";
            RLoad::addScript($path . "leaflet.elevation-0.0.4.src.js", array("type" => "text/javascript"));
            $document->addStyleSheet($path . "elevation.css", array("type" => "text/css"));
            RLoad::addScript("media/lib_ramblers/vendors/leaflet-gpx-1.3.1/gpx.js", array("type" => "text/javascript"));
        }

        if ($options->licenseKeys->OSkey !== null) {
            RLoad::addScript("https://cdn.jsdelivr.net/gh/OrdnanceSurvey/os-api-branding@0.3.1/os-api-branding.js");
            RLoad::addStyleSheet("https://unpkg.com/maplibre-gl@5.3.0/dist/maplibre-gl.css");
            RLoad::addScript("https://unpkg.com/maplibre-gl@5.3.0/dist/maplibre-gl.js");
            RLoad::addScript("https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.1.1/leaflet-maplibre-gl.js");
        }
        if ($options->licenseKeys->OSMVectorStyle !== null) {
            RLoad::addStyleSheet("https://unpkg.com/maplibre-gl@5.3.0/dist/maplibre-gl.css");
            RLoad::addScript("https://unpkg.com/maplibre-gl@5.3.0/dist/maplibre-gl.js");
            RLoad::addScript("https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.1.1/leaflet-maplibre-gl.js");
        }
        // clustering
        $path = "media/lib_ramblers/vendors/Leaflet.markercluster-1.5.3/dist/";
        RLoad::addStyleSheet($path . "MarkerCluster.Default.css");
        RLoad::addStyleSheet($path . "MarkerCluster.css");
        RLoad::addScript($path . "leaflet.markercluster.js");
        RLoad::addScript("media/lib_ramblers/vendors/Leaflet.FeatureGroup.SubGroup-1.0.2/src/subgroup.js");
        // subGroup used by Places.js
        RLoad::addScript("media/lib_ramblers/leaflet/L.Control.Places.js");
        RLoad::addScript("media/lib_ramblers/leaflet/L.Control.Mouse.js");
        RLoad::addStyleSheet("media/lib_ramblers/leaflet/L.Control.Mouse.css");

        RLoad::addScript("media/lib_ramblers/vendors/Leaflet.Control.Resizer-0.0.1/L.Control.Resizer.js");
        RLoad::addStyleSheet("media/lib_ramblers/vendors/Leaflet.Control.Resizer-0.0.1/L.Control.Resizer.css");

        if ($options->mouseposition !== null or $options->osgrid !== null or $options->rightclick !== null) {
            // grid ref to/from lat/long
            RLoad::addScript("media/lib_ramblers/vendors/geodesy/vector3d.js");
            RLoad::addScript("media/lib_ramblers/vendors/geodesy/latlon-ellipsoidal.js");
            RLoad::addScript("media/lib_ramblers/vendors/geodesy/osgridref.js");
        }


        $path = "media/lib_ramblers/vendors/leaflet.browser.print-1/dist/";
        RLoad::addScript($path . "leaflet.browser.print.js");
        //     RLoad::addScript($path . "leaflet.browser.print.sizes.js");
        //     RLoad::addScript($path . "leaflet.browser.print.utils.js");

        if ($options->calendar) {
            $path = "media/lib_ramblers/vendors/fullcalendar-6.1.9/";
            RLoad::addScript($path . "index.global.js");
            // RLoad::addStyleSheet($path . "main.css");
        }

        RLoad::addScript("media/lib_ramblers/js/ra.js");
        RLoad::addScript("media/lib_ramblers/js/ra.map.js");
        RLoad::addScript("media/lib_ramblers/js/ra.walk.js");
        RLoad::addScript("media/lib_ramblers/js/ra.tabs.js");
        RLoad::addScript("media/lib_ramblers/js/ra.paginatedDataList.js");
        RLoad::addStyleSheet("media/lib_ramblers/css/ra.paginatedDataList.css");
        RLoad::addScript("media/lib_ramblers/vendors/cvList/cvList.js");
        RLoad::addStyleSheet("media/lib_ramblers/vendors/cvList/cvList.css");
        RLoad::addStyleSheet("media/lib_ramblers/css/ra.tabs.css");

        if (ComponentHelper::isEnabled('com_ra_eventbooking')) {
            Ra_eventbookingHelper::loadScripts();
        }
        // my location start
        RLoad::addScript("media/lib_ramblers/leaflet/L.Control.MyLocation.js");
        $document->addScript("https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js");
        $document->addStyleSheet("https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css");
        // my location finish

        RLoad::addScript("media/lib_ramblers/leaflet/L.Control.RAContainer.js");
        RLoad::addScript("media/lib_ramblers/leaflet/L.Control.Tools.js");
        RLoad::addScript("media/lib_ramblers/leaflet/L.Control.Search.js");

        // settings
        //  RLoad::addStyleSheet("media/lib_ramblers/leaflet/L.Control.Settings.css");
        RLoad::addScript("media/lib_ramblers/leaflet/ra.map.settings.js");
        RLoad::addScript("media/lib_ramblers/js/ra.feedhandler.js");
        RLoad::addScript("media/lib_ramblers/vendors/FileSaver-js-1.3.8/src/FileSaver.js");
    }

    public static function registerWalks($walks) {
        // register walks from php methods into raWalks.js for display
        $data = new stdClass();
        $data->walks = $walks;
        //   $print = json_encode($walks, JSON_PRETTY_PRINT);
        //   echo "<pre>" . $print . "</pre>";
        $script = new RLeafletScript();
        $options = new RLeafletMapoptions();
        $script->setCommand("ra.walk.registerPHPWalks");
        $script->setDataObject($data);
        $script->add($options);
    }
}
