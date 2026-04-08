<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of display
 *
 * @author Chris Vaughan
 */
use Joomla\CMS\Uri\Uri;

class RGpxsymbolsDisplay {

    public function __construct() {
        RLoad::addStyleSheet("media/lib_ramblers/gpxsymbols/display.css");
    }

    public function listFolder($folder) {
        $items = [];
        // Get the base URL 
        $baseUrl = Uri::base();
        if ($handle = opendir($folder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $items[] = $entry;
                }
            }
            closedir($handle);
            asort($items, SORT_NATURAL);
            echo "<details class='ra' name='symbols'>";
            echo "<summary>Display possible markers</summary>";
            echo "<div class='gpximages' >";
            foreach ($items as $item) {
                $this->displayImage($baseUrl . $folder, $item);
            }
            echo "</div>";
            echo "</details>";
            echo "<div class='clear' ></div>";
        }
    }

    private function displayImage($folder, $entry) {
        $names = explode(".", $entry);
        $name = $names[0];
        echo "<div class='gpximage' >";
        echo "<span class='gpximagedisplay' ><img src='" . $folder . "/" . $entry . "' alt='image'></span>";
        echo "<span class='gpximagetitle' >" . $name . "</span>";
        echo "</div>";
    }
}
