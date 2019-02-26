<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdFulldetails2 extends RJsonwalksDisplaybase {

    public $nationalGradeHelp = "";
    public $localGradeHelp = "";
    public $nationalGradeTarget = "_parent";
    public $localGradeTarget = "_parent";
    private $legendposition = "top";
    public $addContacttoHeader = false;
    public $displayGroup = null;  // should the Group name be displayed
    public $displayClass="pantone7474white";
    private $walksClass = "walks";
    private $walkClass = "walk";
    private $map = null;

    public function DisplayWalks($walks) {

        if ($this->displayGradesIcon) {
            RJsonwalksWalk::gradeToolTips();
        }
        $document = JFactory::getDocument();

        $document->addScript("ramblers/jsonwalks/std/fulldetails.js", "text/javascript");
        $document->addScript("ramblers/vendors/jplist-es6-master/dist/1.1.2/jplist.min.js", "text/javascript");
        $items = $walks->allWalks();
        $text = "ramblerswalks='" . addslashes(json_encode($items)) . "'";
        //  echo $text;
        $out = "window.addEventListener('load', function(event) {
            ramblerswalksDetails = new RamblersWalksDetails();
            ramblerswalksDetails.displayClass='".$this->displayClass."';
    FullDetailsLoad();
  });
                function addContent() {" . $text . "};";
        $document->addScriptDeclaration($out, "text/javascript");
        echo '<div id="raModal" class="modal">
        <!-- Modal Content (The Image) -->
        <div class="modal-content" >
        <div class="modal-header">
        <button id="btnPrint" class="btn" type="button" >Print</button>
        <button id="btnClose" class="btn" data-dismiss="modal" >Close</button>
        </div>
        <p style="clear:right;"> </p>
        <div id="modal-data"></div>
        </div></div>';
        echo "<div id='raprint' ></div>";
        echo "<div id='raoptions' ></div><p></p>";
        echo "<div id='rapagination-1' ></div>";
        echo "<div id='rawalks' >Page error - No walks</div>";
        echo "<div id='rapagination-2' ></div>";
        // send walks as json file
        // write json to display a number of them
        $this->map = new RLeafletMap();
        $this->map->leafletLoad = false;
        $options = $this->map->options;
        $options->cluster = true;
        $options->displayElevation = true;
        $options->fullscreen = true;
        $options->search = true;
        $options->locationsearch = true;
        $options->osgrid = true;
        $options->mouseposition = true;
        $options->postcodes = true;
        $options->fitbounds = false;
        $options->print = true;
        echo "<div id='ra-map' >";
        $this->displayMap();
        echo "</div>";
    }

    private function displayMap() {
        $legend = '<p><strong>Zoom</strong> in to see where our walks are going to be. <strong>Click</strong> on a walk to see details.</p>
<p><img src="ramblers/images/marker-start.png" alt="Walk start" height="26" width="16">&nbsp; Start locations&nbsp; <img src="ramblers/images/marker-cancelled.png" alt="Cancelled walk" height="26" width="16"> Cancelled walk&nbsp; <img src="ramblers/images/marker-area.png" alt="Walking area" height="26" width="16"> Walk in that area.</p>';

        if (isset($this->map)) {
            if (strpos($this->legendposition, "top") !== false) {
                echo $legend;
            }

            $this->map->display();
            if (strpos($this->legendposition, "bottom") !== false) {
                echo $legend;
            }
        }
    }

    private function addGotoWalk() {

        $walk = $_GET["walk"];
        if ($walk != null) {
            if (is_numeric($walk)) {
                $add = "<script type=\"text/javascript\">window.onload = function () {
                gotoWalk($walk);};</script>";
                echo $add;
            }
        }
    }

}