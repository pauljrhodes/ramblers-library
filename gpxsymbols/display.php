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
class RGpxsymbolsDisplay {

    public function __construct() {
        $document = JFactory::getDocument();
        $document->addStyleSheet('libraries/ramblers/gpxsymbols/display.css');
    }

    public function listFolder($folder) {
        $items = [];
        if ($handle = opendir($folder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $items[] = $entry;
                }
            }
            closedir($handle);
            asort($items,SORT_NATURAL);
            echo "<div class='gpximages' >";
            foreach ($items as $item) {
                $this->displayImage($folder, $item);
            }
            echo "</div>";

            echo "<div class='clear' ></div>";
        }
    }

    private function displayImage($folder, $entry) {
        $names = explode(".", $entry);
        $name = $names[0];
        echo "<div class='gpximage' >";
        echo "<span class='gpximagedisplay' ><img src='/" . $folder . "/" . $entry . "' alt='image'></span>";
        echo "<span class='gpximagetitle' >" . $name . "</span>";
        echo "</div>";
    }

}
