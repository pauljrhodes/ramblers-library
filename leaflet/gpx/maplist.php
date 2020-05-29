<?php

/**
 * Description of RLeafletGpxMapget
 *    Display Gpx file on map but use file name from GET
 *
 * @author Chris Vaughan
 */
class RLeafletGpxMaplist extends RLeafletMap {

    public $linecolour = "#782327";
    public $imperial = false;
    Public $folder = "images";
    public $addDownloadLink = "Users"; // "None" - no link, "Users" - users link, "Public" - guest link
    public $descriptions = true; // set false if NO description files are to be supplied
    public $getMetaFromGPX = true;
    public $displayAsPreviousWalks = false;
    public $displayTitle = true;

    public function __construct() {
        parent::__construct();
    }

    public function display() {
        // get all names from folder
        $stats = new RGpxStatistics($this->folder, $this->getMetaFromGPX);
        $items = $stats->getJson();
        $this->addMapScript($items);
        $this->help_page = "https://maphelp.ramblers-webs.org.uk/list-of-walking-routes.html";
        $this->options->cluster = true;
        $this->options->displayElevation = true;
        $this->options->fullscreen = true;
        $this->options->search = true;
        $this->options->locationsearch = true;
        $this->options->osgrid = true;
        $this->options->mouseposition = true;
        $this->options->postcodes = true;
        $this->options->fitbounds = true;
        $this->options->print = true;
        //    RLicense::BingMapKey(false);
        if ($this->imperial) {
            $imperial = "true";
        } else {
            $imperial = "false";
        }
        if ($this->displayTitle) {
            if ($this->displayAsPreviousWalks) {
                echo "<h2>Previous Walks</h2>";
            } else {
                echo "<h2>Walking Routes</h2>";
            }
        }

        echo "<table id='gpxoptions' ><tr><td class='ra-tab active' id='Map' class='active' onclick=\"javascript:ra_format('Map')\">Map</td><td class='ra-tab' id='List' onclick=\"javascript:ra_format('List')\">List</td></tr></table>";
        echo "<div id='gpxouter' >";
        echo "<div id='gpxmap'>";
        echo "<p> </p>";
        echo "<div id = \"gpxheader\" ><h4>Click on any walk to display route</h4></div>";

        $text = " 
                ramblersGpx=new RamblersLeafletGpx();
                ramblersGpx.download=" . $this->downloadState() . ";
                ramblersGpx.folder= \"" . $this->folder . "\";
                ramblersGpx.linecolour= \"" . $this->linecolour . "\";
                addRoutes();";

        if ($this->displayAsPreviousWalks) {
            $text .="ramblersGpx.displayAsPreviousWalks=true;";
        } else {
            $text .="ramblersGpx.displayAsPreviousWalks=false;";
        }
        $text .= "displayData();";
        parent::addContent($text);
        parent::display();
        $document = JFactory::getDocument();
        $document->addScript("libraries/ramblers/leaflet/maplist.js", "text/javascript");
        $document->addStyleSheet('libraries/ramblers/jsonwalks/css/ramblerswalks.css');
        $document->addScript("libraries/ramblers/vendors/jplist-es6-master/dist/1.2.0/jplist.min.js", "text/javascript");
        //   <!-- IE 10+ / Edge support via babel-polyfill: https://babeljs.io/docs/en/babel-polyfill/ --> 
        $document->addScript("https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/6.26.0/polyfill.min.js", "text/javascript");
        echo "<br/>";
        echo "</div>";
        echo "<div id='gpxlist' style='display:none;' >";
        if ($this->displayAsPreviousWalks) {
            echo "<h4>List of Previous Walks</h4>";
        } else {
            echo "<h4>List of Walking Routes</h4>";
        }
        echo "<div id='ra-pagination1'></div>";

        echo "<div id=\"dataTab\">Program loading: please give this a minute or so. If this does not vanish then please contact the web master!</div>";
        echo "</div>";
        echo "</div>";
    }

    private function addMapScript($items) {
        $script = "function addRoutes() {"
                . "ramblersGpx.routes=" . $items . ";}";
        $document = JFactory::getDocument();
        $document->addScriptDeclaration($script, "text/javascript");
    }

    private function loggedon() {
        $user = JFactory::getUser(); //gets user object
        If ($user != null) {
            return $user->id != 0;
        }
        return false;
    }

    private function hasDescriptions($items) {
        return true;
        foreach ($items as $item) {
            if ($item->description !== '') {
                return true;
            }
        }
        return false;
    }

    private function downloadState() {
        $state = 0; // no download link
        switch ($this->addDownloadLink) {
            case "Users":
            case 1:
                If ($this->loggedon()) {
                    $state = 2; // display link
                } else {
                    $state = 1; // need to be logged on
                }
                break;
            case "Public" :
            case 2:
                $state = 2;  // display link
            default:
                break;
        }
        return $state;
    }

}
