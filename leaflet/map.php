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
    private $scripts = [];
    private $stylesheets = [];

    const COMBINED_JS = "cache/ra_leaflet/ra-leafet";
    const FOLDER = "cache/ra_leaflet";

    function __construct() {
        $template = "ramblers/leaflet/mapTemplate.js";
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
        $this->addStyleSheet("ramblers/vendors/leaflet1.3.4/leaflet.css", "text/css");
        $this->addScript("ramblers/vendors/leaflet1.3.4/leaflet.js", "text/javascript");
        $this->addScript("ramblers/leaflet/ramblersleaflet.js", "text/javascript");
        $this->addStyleSheet("ramblers/leaflet/ramblersleaflet.css", "text/css");
        if ($options->search) {
            $path = "ramblers/vendors/leaflet-control-geocoder-1.5.8/dist/";
            $this->addStyleSheet($path . "Control.Geocoder.css", "text/css");
            $this->addScript($path . "Control.Geocoder.js", "text/javascript");
        }
        if ($options->locationsearch) {
            $this->addStyleSheet("ramblers/leaflet/Control.Locationsearch.css", "text/css");
            $this->addScript("ramblers/leaflet/Control.Locationsearch.js", "text/javascript");
        }
        if ($options->fullscreen) {
            $path = "ramblers/vendors/Leaflet.fullscreen-1.0.2/dist/";
            $this->addScript($path . "Leaflet.fullscreen.min.js", "text/javascript");
            $this->addStyleSheet($path . "leaflet.fullscreen.css", "text/css");
        }

        if ($options->postcodes) {
            // Mapcodes        
            $this->addScript("ramblers/vendors/mapcode-js-2.4.1/mapcode.js", "text/javascript");
            $this->addScript("ramblers/vendors/mapcode-js-2.4.1/ndata.js", "text/javascript");
        }

        if ($options->displayElevation) {
            // elevation
            $this->addScript("https://d3js.org/d3.v3.min.js", "text/javascript");
            $path = "ramblers/vendors/Leaflet.Elevation-0.0.4-ra/";
            $this->addScript($path . "leaflet.elevation-0.0.4.src.js", "text/javascript");
            $this->addStyleSheet($path . "elevation.css", "text/css");
            $this->addScript("ramblers/vendors/leaflet-gpx-1.3.1/gpx.js", "text/javascript");
        }
        if (RLicense::isGoogleKeyMapSet()) {
            // Google
            $this->addScript("https://maps.googleapis.com/maps/api/js?key=" . RLicense::getGoogleMapKey(), "text/javascript", true, true);
            $this->addScript("ramblers/vendors/Leaflet.GridLayer.GoogleMutant/Leaflet.GoogleMutant.js", "text/javascript");
            $this->addScript("ramblers/vendors/es6-promise-vv4.2.3/dist/es6-promise.auto.js", "text/javascript");
            $this->map->replaceString("// [set addGoogle]", "ramblersMap.options.google=true;");
        }
        if ($options->cluster) {
            // clustering
            $path = "ramblers/vendors/Leaflet.markercluster-1.3.0/dist/";
            $this->addStyleSheet($path . "MarkerCluster.css", "text/css");
            $this->addStyleSheet($path . "MarkerCluster.Default.css", "text/css");
            $this->addScript($path . "leaflet.markercluster.js", "text/javascript");
        }
        if ($options->mouseposition or $options->postcodes) {
            $this->addScript("ramblers/leaflet/L.Control.Mouse.js", "text/javascript");
            $this->addStyleSheet("ramblers/leaflet/L.Control.Mouse.css", "text/css");
        }

        if ($options->mouseposition or $options->osgrid or $options->postcodes) {
            // grid ref to/from lat/long
            $this->addScript("ramblers/vendors/geodesy/vector3d.js", "text/javascript");
            $this->addScript("ramblers/vendors/geodesy/latlon-ellipsoidal.js", "text/javascript");
            $this->addScript("ramblers/vendors/geodesy/osgridref.js", "text/javascript");
        }
        if ($options->draw) {
            $this->addScript("ramblers/leaflet/ra-gpx-drawcontrol.js", "text/javascript");
            $path = "ramblers/vendors/Leaflet.draw-0.4.14/dist/";
            $this->addStyleSheet($path . "leaflet.draw.css", "text/css");
            $this->addScript($path . "leaflet.draw.js", "text/javascript");
            $this->addScript("ramblers/leaflet/maplist.js", "text/javascript");
            $this->addScript("ramblers/leaflet/ra-gpx-upload.js", "text/javascript");
            $this->addScript("ramblers/leaflet/ra-gpx-download.js", "text/javascript");
            $this->addScript("ramblers/leaflet/ra-gpx-reverse-route.js", "text/javascript");
            $this->addScript("ramblers/leaflet/ra-gpx-simplify.js", "text/javascript");
            $this->addScript("ramblers/vendors/simplify-js-1.2.3/simplify.js", "text/javascript");
            $this->addStyleSheet("ramblers/leaflet/ra-gpx-tools.css", "text/css");
            $this->addScript("ramblers/vendors/blurt-1.0.2/dist/js/blurt.min.js", "text/javascript");
            $this->addStyleSheet("ramblers/vendors/blurt-1.0.2/dist/css/blurt.min.css", "text/css");
            $this->addScript("ramblers/vendors/FileSaver-js-1.3.8/src/FileSaver.js", "text/javascript");
        }
        if (RLicense::isBingKeyMapSet()) {
            // Bing maps
            $this->addScript("ramblers/vendors/bing/bing.js", "text/javascript");
        }

        if ($options->print) {
            $path = "ramblers/vendors/leaflet.browser.print-0.7/src/";
            $this->addScript($path . "leaflet.browser.print.js", "text/javascript");
            $this->addScript($path . "leaflet.browser.print.sizes.js", "text/javascript");
            $this->addScript($path . "leaflet.browser.print.utils.js", "text/javascript");
        }
        if ($this->help_page != "") {
            $this->addScript("ramblers/leaflet/ra-map-help.js", "text/javascript");
        }
        //     if ($options->ramblersPlaces){
        //        $this->addScript( "ramblers/leaflet/ra-ramblers-places.js", "text/javascript");
        //    }
        $this->addScriptandStyleSheets();
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
        echo "</script>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
        echo "<p class='mapcopyright'>OS data © Crown copyright and database 2018;   Royal Mail data © Royal Mail copyright and Database 2018</p>";
        echo "<p class='mapcopyright'>Maps Icons Collection https://mapicons.mapsmarker.com</p>";
        echo "</div>";
    }

    private function addScript($script, $type) {
        $this->scripts[] = $script;
    }

    private function addStyleSheet($stylesheet, $type) {
        $this->stylesheets[] = $stylesheet;
    }

    private function addScriptandStyleSheets() {


        if (strpos(JURI::base(), 'localhost') !== false) {
            $test = true;
        } else {
            $test = false;
        }
        // $test = false;
        $document = JFactory::getDocument();
        $jsfilename = self::COMBINED_JS . $this->options->getFileName() . ".js";
        foreach ($this->stylesheets as $item) {
            $document->addStyleSheet($item, "text/css");
        }
        if ($test) {
            foreach ($this->scripts as $item) {
                $document->addScript($item, "text/javascript");
            }
        } else {
            if (!file_exists($jsfilename)) {
                if (!file_exists(self::FOLDER)) {
                    mkdir(self::FOLDER);
                }
                $this->createFile($this->scripts, $jsfilename);
            }
            $document->addScript($jsfilename, "text/javascript");
        }
    }

    private function createFile($items, $path) {
        $myfile = fopen($path, "w");
        foreach ($items as $item) {
            fwrite($myfile, "//\n");
            fwrite($myfile, "//\n");
            fwrite($myfile, "//  INCLUDE FILE  " . $item . "      \n");
            fwrite($myfile, "//\n");
            fwrite($myfile, "//\n");
            $txt = file_get_contents($item);
            fwrite($myfile, $txt);
        }
        fclose($myfile);
    }

}
