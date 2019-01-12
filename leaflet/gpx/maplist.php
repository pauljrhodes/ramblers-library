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

    public function __construct() {
        parent::__construct();
    }

    public function display() {
        $document = JFactory::getDocument();
        $document->addScript("ramblers/leaflet/maplist.js", "text/javascript");
        // get all names from folder
        $stats = new RGpxStatistics($this->folder, $this->getMetaFromGPX);
        $items = $stats->getJson();
        $this->addMapScript($items);
        $this->options->cluster = true;
        $this->options->displayElevation = true;
        $this->options->fullscreen = true;
        $this->options->search = true;
        $this->options->locationsearch = true;
        $this->options->osgrid = true;
        $this->options->mouseposition = true;
        $this->options->postcodes = true;
        $this->options->fitbounds = true;
        $this->options->displayElevation = true;
        $this->options->print = true;
    //    RLicense::BingMapKey(false);
        if ($this->imperial) {
            $imperial = "true";
        } else {
            $imperial = "false";
        }

        echo "<div class=\"tab\">";
        if ($this->displayAsPreviousWalks) {
            echo "<button class=\"tablinks active\" onclick=\"openTab(event, 'tabRouteDetails')\">Previous Walks</button>";
            echo "<button class=\"tablinks\" onclick=\"openTab(event, 'tabRouteList')\">Titles</button>";
        } else {
            echo "<button class=\"tablinks active\" onclick=\"openTab(event, 'tabRouteList')\">Walking Routes</button>";
            echo "<button class=\"tablinks\" onclick=\"openTab(event, 'tabRouteDetails')\">Route Details</button>";
        }
        if ($this->descriptions) {
            echo "<button class=\"tablinks\" onclick=\"openTab(event, 'tabDescriptions')\">Descriptions</button>";
        }
        echo "<form id=\"searchform\" ACTION=\"\"\ onsubmit=\"return gpxsearch()\">
              <input name=\"titlesearch\" id=\"gpxtitlesearch\" maxlength=\"200\" class=\"inputbox search-query input-medium\" size=\"20\" placeholder=\"Search\" type=\"search\">
         </form>";
        echo "</div>";
        if ($this->displayAsPreviousWalks) {
            echo "<div id=\"tabRouteDetails\" class=\"tabcontent\" style=\"display:block\">Processing ...</div>";
            echo "<div id=\"tabRouteList\" class=\"tabcontent\" >Processing ...</div>";
            if ($this->descriptions) {
                echo "<div id=\"tabDescriptions\" class=\"tabcontent\" >Webmaster: Add text files to display descriptions of each route</div>";
            }
        } else {
            echo "<div id=\"tabRouteList\" class=\"tabcontent\"  style=\"display:block\">Processing ...</div>";
            if ($this->descriptions) {
                echo "<div id=\"tabDescriptions\" class=\"tabcontent\" >Webmaster: Add text files to display descriptions of each route</div>";
            }
            echo "<div id=\"tabRouteDetails\" class=\"tabcontent\">Processing ...</div>";
        }

        echo "<p> </p>";
        echo "<div id = \"gpxheader\" ><h4>Click on any walk to display route</h4></div>";

        $text = " 
                ramblersGpx=new RamblersLeafletGpx();
                ramblersGpx.download=" . $this->downloadState() . ";
                ramblersGpx.folder= \"" . $this->folder . "\";
                ramblersGpx.linecolour= \"" . $this->linecolour . "\";
                addRoutes();";
        if ($this->descriptions) {
            $text .="ramblersGpx.description='true';";
            $text .="displayGPXDescriptions();";
        } else {
            $text .="ramblersGpx.description='false';";
        }
        if ($this->displayAsPreviousWalks) {
            $text .="ramblersGpx.displayAsPreviousWalks=true;";
        } else {
            $text .="ramblersGpx.displayAsPreviousWalks=false;";
        }
        $text .= "displayGPXNames();
                displayGPXTable();
                addGPXMarkers();";
        parent::addContent($text);
        parent::display();
        echo "<br/>";
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
