<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdListleaders extends RJsonwalksDisplaybase {

    function DisplayWalks($walks) {
        
        $walks->sort(RJsonwalksWalk::SORT_CONTACT, RJsonwalksWalk::SORT_TELEPHONE1, NULL);
        $items = $walks->allWalks();
        $last = "";
        echo "<h2>List of Walks Leaders</h2>" . PHP_EOL;
        echo "<ul>" . PHP_EOL;
        foreach ($items as $walk) {
            $value = $walk->contactName . " - " . $walk->telephone1;
            if (!$walk->telephone2 == NULL) {
                $value.=" ," . $walk->telephone2;
            }
            if (!$walk->getEmail() == NULL) {
                $value.=" ," . $walk->getEmail($this->emailDisplayFormat);
            }
            if ($value <> $last) {
                echo "<li>" . $value . "</li>" . PHP_EOL;
                $last = $value;
            }
        }
        echo "</ul>" . PHP_EOL;
    }

}
