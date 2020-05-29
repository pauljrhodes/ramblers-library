<?php

/**
 * Description of RLeafletMap
 *   Not called by users
 *
 * @author Chris Vaughan
 */
class RLeafletMap {

    private $map;
    public $debugoptions = false;
    public $defaultMap = "";
    public $mapStyle;
    public $mapHeight;
    public $mapWidth;
    public $options;
    public $help_page = "";
    public $leafletLoad = true;

  //  const COMBINED_JS = "cache/ra_leaflet/ra-leafet";
  //  const FOLDER = "cache/ra_leaflet";

    function __construct() {
        $template = "libraries/ramblers/leaflet/mapTemplate.js";
        $this->map = new RHtmlTemplate($template);
        $this->mapStyle = " #leafletmap { height: 500px; width:100%;}";
        $this->mapHeight = "500px";
        $this->mapWidth = "100%";
        $this->options = new RLeafletMapoptions();
    }

    public function addContent($text) {
        $document = JFactory::getDocument();
        $out = "function addContent(ramblersMap) {" . $text . "}";
        $document->addScriptDeclaration($out, "text/javascript");
        $document->addScript("libraries/ramblers/js/ramblerswalks.js", "text/javascript");
    }

    public function addBounds() {
        $this->map->replaceString("// [FitBounds]", "ramblersMap.map.fitBounds(ramblersMap.markersCG.getBounds());");
    }

    public function display() {
        $options = $this->options;
        if ($this->defaultMap == "Topo") {
            $options->topoMapDefault = true;
        }
        JHtml::_('jquery.framework');

        $document = JFactory::getDocument();
        // Leaflet
        $document->addStyleSheet("libraries/ramblers/vendors/leaflet1.6/leaflet.css", "text/css");
        $document->addScript("libraries/ramblers/vendors/leaflet1.6/leaflet.js", "text/javascript");
        $document->addScript("libraries/ramblers/leaflet/ramblersleaflet.js", "text/javascript");
        $document->addStyleSheet("libraries/ramblers/leaflet/ramblersleaflet.css", "text/css");
        // add script for Google Plus codes https://plus.codes
        $document->addScript("https://cdnjs.cloudflare.com/ajax/libs/openlocationcode/1.0.3/openlocationcode.js", "text/javascript");
        if ($options->search) {
            $path = "libraries/ramblers/vendors/leaflet-control-geocoder-1.5.8/dist/";
            $document->addStyleSheet($path . "Control.Geocoder.css", "text/css");
            $document->addScript($path . "Control.Geocoder.js", "text/javascript");
        }
        if ($options->locationsearch) {
            $document->addStyleSheet("libraries/ramblers/leaflet/Control.Locationsearch.css", "text/css");
            $document->addScript("libraries/ramblers/leaflet/Control.Locationsearch.js", "text/javascript");
        }
        if ($options->fullscreen) {
            $path = "libraries/ramblers/vendors/Leaflet.fullscreen-1.0.2/dist/";
            $document->addScript($path . "Leaflet.fullscreen.min.js", "text/javascript");
            $document->addStyleSheet($path . "leaflet.fullscreen.css", "text/css");
        }

        if ($options->postcodes) {
            // Mapcodes        
            $document->addScript("libraries/ramblers/vendors/mapcode-js-2.4.1/mapcode.js", "text/javascript");
            $document->addScript("libraries/ramblers/vendors/mapcode-js-2.4.1/ndata.js", "text/javascript");
        }

        if ($options->displayElevation) {
            // elevation
            $document->addScript("https://d3js.org/d3.v3.min.js", "text/javascript");
            $path = "libraries/ramblers/vendors/Leaflet.Elevation-0.0.4-ra/";
            $document->addScript($path . "leaflet.elevation-0.0.4.src.js", "text/javascript");
            $document->addStyleSheet($path . "elevation.css", "text/css");
            $document->addScript("libraries/ramblers/vendors/leaflet-gpx-1.3.1/gpx.js", "text/javascript");
        }
        if (RLicense::isGoogleKeyMapSet()) {
            // Google
            $document->addScript("https://maps.googleapis.com/maps/api/js?key=" . RLicense::getGoogleMapKey(), "text/javascript", true, true);
            $document->addScript("libraries/ramblers/vendors/Leaflet.GridLayer.GoogleMutant/Leaflet.GoogleMutant.js", "text/javascript");
            $document->addScript("libraries/ramblers/vendors/es6-promise-vv4.2.3/dist/es6-promise.auto.js", "text/javascript");
            $document->map->replaceString("// [set addGoogle]", "ramblersMap.options.google=true;");
        }
        if ($options->cluster) {
            // clustering
            $path = "libraries/ramblers/vendors/Leaflet.markercluster-1.4.1/dist/";
            $document->addStyleSheet($path . "MarkerCluster.css", "text/css");
            $document->addStyleSheet($path . "MarkerCluster.Default.css", "text/css");
            $document->addScript($path . "leaflet.markercluster.js", "text/javascript");
        }
        if ($options->mouseposition or $options->postcodes) {
            $document->addScript("libraries/ramblers/leaflet/L.Control.Mouse.js", "text/javascript");
            $document->addStyleSheet("libraries/ramblers/leaflet/L.Control.Mouse.css", "text/css");
        }

        if ($options->mouseposition or $options->osgrid or $options->postcodes) {
            // grid ref to/from lat/long
            $document->addScript("libraries/ramblers/vendors/geodesy/vector3d.js", "text/javascript");
            $document->addScript("libraries/ramblers/vendors/geodesy/latlon-ellipsoidal.js", "text/javascript");
            $document->addScript("libraries/ramblers/vendors/geodesy/osgridref.js", "text/javascript");
        }
        if ($options->draw) {
            $document->addScript("libraries/ramblers/leaflet/ra-gpx-drawcontrol.js", "text/javascript");
            $path = "libraries/ramblers/vendors/Leaflet.draw-0.4.14/dist/";
            $document->addStyleSheet($path . "leaflet.draw.css", "text/css");
            $document->addStyleSheet("libraries/ramblers/leaflet/ra-gpx-tools.css", "text/css");
            $document->addScript($path . "leaflet.draw.js", "text/javascript");
            $document->addScript("libraries/ramblers/leaflet/maplist.js", "text/javascript");
            $document->addScript("libraries/ramblers/leaflet/ra-gpx-upload.js", "text/javascript");
            $document->addScript("libraries/ramblers/leaflet/ra-gpx-download.js", "text/javascript");
            $document->addScript("libraries/ramblers/leaflet/ra-gpx-reverse-route.js", "text/javascript");
            $document->addScript("libraries/ramblers/leaflet/ra-gpx-simplify.js", "text/javascript");
            $document->addScript("libraries/ramblers/vendors/simplify-js-1.2.3/simplify.js", "text/javascript");
            $document->addScript("libraries/ramblers/vendors/blurt-1.0.2/dist/js/blurt.min.js", "text/javascript");
            $document->addStyleSheet("libraries/ramblers/vendors/blurt-1.0.2/dist/css/blurt.min.css", "text/css");
            $document->addScript("libraries/ramblers/vendors/FileSaver-js-1.3.8/src/FileSaver.js", "text/javascript");
        }
        if (RLicense::isBingKeyMapSet()) {
            // Bing maps
            $document->addScript("libraries/ramblers/vendors/bing/bing.js", "text/javascript");
        }

        if ($options->print) {
            $path = "libraries/ramblers/vendors/leaflet.browser.print-0.7/src/";
            $document->addScript($path . "leaflet.browser.print.js", "text/javascript");
            $document->addScript($path . "leaflet.browser.print.sizes.js", "text/javascript");
            $document->addScript($path . "leaflet.browser.print.utils.js", "text/javascript");
        }
        if ($this->help_page != "") {
            $document->addScript("libraries/ramblers/leaflet/ra-map-help.js", "text/javascript");
        }
        //     if ($options->ramblersPlaces){
        //        $this->addScript( "libraries/ramblers/leaflet/ra-ramblers-places.js", "text/javascript");
        //    }
        //     $this->addScriptandStyleSheets();
        $mapStyle = " #leafletmap { height: " . $this->mapHeight . "; width:" . $this->mapWidth . ";}";
        $document->addStyleDeclaration($mapStyle);

        $base = JURI::base();
        $folder = JURI::base(true);
        if (strpos($base, 'localhost') !== false) {
            $this->map->replaceString("[base]", "");
        } else {
            $this->map->replaceString("[base]", $folder . "/");
        }

        $optionstext = $options->text();
        if ($this->help_page != "") {
            $optionstext.="ramblersMap.maphelppage='" . $this->help_page . "';";
        }

        $this->map->replaceString("// [set MapOptions]", $optionstext);
        if ($this->debugoptions) {
            echo $optionstext;
        }
        if ($options->cluster) {
            echo "<div id='ra-cluster-progress'><div id='ra-cluster-progress-bar'></div></div> " . PHP_EOL;
        }
        echo "<div id='ra-error-text'></div> " . PHP_EOL;
        if ($options->draw) {
            echo "<div id='ra-map-details'><p>No routes or markers currently defined</p></div>";
        }
        echo "<div class='map-container'>" . PHP_EOL;


        echo "<div id='leafletmap'></div>" . PHP_EOL;
        echo "<script type='text/javascript'>";
        echo $this->map->getContents();
        if ($this->leafletLoad) {
            echo "window.onload = function () {raLoadLeaflet();};";
        }
        echo "</script>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
        echo "<p class='mapcopyright'>OS data © Crown copyright and database 2018;   Royal Mail data © Royal Mail copyright and Database 2018</p>";
        echo "<p class='mapcopyright'>Maps Icons Collection https://mapicons.mapsmarker.com</p>";
        //   echo "</div>";
    }
}