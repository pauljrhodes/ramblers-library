<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdLeaderstable extends RJsonwalksDisplaybase {

    private $tableClass = "";

    function DisplayWalks($walks) {
        $printOn = JRequest::getVar('print') == 1;
            if ($printOn) {
                $doc = JFactory::getDocument();
                $style = 'table { border-collapse: collapse;} table, td, th { border: 1px solid #657291;}td { padding: 5px;}';
                $doc->addStyleDeclaration($style);
            }

        $walks->sort(RJsonwalksWalk::SORT_CONTACT, RJsonwalksWalk::SORT_TELEPHONE1, NULL);
        $items = $walks->allWalks();
        $last = "";

        echo "<table class='$this->tableClass'>";
        echo RHtml::addTableHeader(array("Contact", "Email", "Telephone 1", "Telephone 2"));
        foreach ($items as $walk) {
            $value = $walk->contactName . " - " . $walk->telephone1;
            if (!$walk->telephone2 == NULL) {
                $value.=" ," . $walk->telephone2;
            }
            if (!$walk->getEmail() == NULL) {
                $value.=" ," . $walk->getEmail();
            }
            if ($value <> $last) {
                echo RHtml::addTableRow(array($walk->contactName, $walk->getEmail($this->emailDisplayFormat), $walk->telephone1, $walk->telephone2));
                $last = $value;
            }
        }
        echo "</table>" . PHP_EOL;
    }

    function setTableClass($class) {
        $this->tableClass = $class;
    }

}
