<?php

/**
 * Description of mapmarker
 *
 * @author Chris Vaughan
 */
class RLeafletGpxMap extends RLeafletMap {

    public $linecolour = "#782327";
    public $imperial = false;
    public $addDownloadLink = "Users"; // "None" - no link, "Users" - users link, "Public" - guest link

    public function __construct() {
        parent::__construct();
    }

    public function displayPath($gpx) {
        $document = JFactory::getDocument();
        $document->addScript("libraries/ramblers/leaflet/maplist.js", "text/javascript");
        $this->help_page = "https://maphelp.ramblers-webs.org.uk/walking-route.html";
        $this->options->fullscreen = true;
        $this->options->cluster = false;
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
        $file = JURI::root() . $gpx;
//   echo $file;

        if (file_exists($gpx)) {
            $path_parts = pathinfo($gpx);
            if (strtolower($path_parts['extension']) != "gpx") {
                $ $app = JApplicationCms::getInstance('site');
                $app->enqueueMessage(JText::_('GPX: Route file is not a gpx file: ' . $file), 'error');
                echo "<p><b>Unable to display gpx file</b></p>";
            }
        } else {
            $app = JApplicationCms::getInstance('site');
            $app->enqueueMessage(JText::_('GPX: Route file not found: ' . $file), 'error');
            echo "<p><b>Unable to display gpx file</b></p>";
        }

        if (file_exists($gpx)) {
            echo "<div id='gpxsingleheader'></div>";
            $link = false;
            switch ($this->addDownloadLink) {
                case "Users":
                case 1:
                    If ($this->loggedon()) {
                        $link = true;
                    } else {
                        echo "<br/>Please log on to this site to be able to download GPX file of this walk";
                    }
                    break;
                case "Public" :
                case 2:
                    $link = true;
                default:
                    break;
            }
            if ($link) {
                echo '<b>Download route:</b> <a href="' . $gpx . '"><img alt="gpx" src="libraries/ramblers/images/orange-gpx-32.png" width="20" height="20"></a><br/><br/>';
            }

            $text = "  ramblersGpx=new RamblersLeafletGpx();"
                    . "displayGPX( '$gpx', '$this->linecolour', $imperial)";
            parent::addContent($text);
        } else {
            parent::addContent("");
        }
        parent::display();
    }

    private function loggedon() {
        $user = JFactory::getUser(); //gets user object
        If ($user != null) {
            return $user->id != 0;
        }
        return false;
    }

}
