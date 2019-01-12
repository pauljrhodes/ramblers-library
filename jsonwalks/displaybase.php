<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RJsonwalksDisplaybase
 *
 * @author Chris Vaughan
 */
abstract class RJsonwalksDisplaybase {

    protected $displayStartTime = false;
    protected $displayStartDescription = false;
    protected $printOn = false;
    public $displayGradesIcon = true;
    public $displayGradesSidebar = true;
    public $emailDisplayFormat = 1;
    // 1 display mailto link to contact, Obfuscated to prevent harvesting by bots and spammers, without visible changes to the address for human visitors.
    // 2 link to ramblers.org.uk form to email contact 
    // 3 do not display
    // 4 display as name (at) domain
    protected static $dispMenu = 0;
    protected static $dispArticle = 0;

    // 0 display walk via ramblers.org.uk
    // >0 display via local site article

    abstract protected function DisplayWalks($walks);

    public function __construct() {
        $this->printOn = JRequest::getVar('print') == 1;
    }

    public function alwaysDisplayStartTime($value) {
        $this->displayStartTime = $value;
    }

    public function alwaysDisplayStartDescription($value) {
        $this->displayStartDescription = $value;
    }

    public function displayArticle($menu, $article) {
        self::$dispMenu = $menu;
        self::$dispArticle = $article;
    }

    public function getWalkHref($walk) {
        $out = "<a href='";
        if (self::$dispArticle > 0) {
            // $out.="index.php?option=com_content&view=article&id=" . $this->dispArticle . "&Itemid=" . $this->dispMenu . "&walk=" . $walk->id . "'";
            $out .= self::getWalkUrl($walk->id) . "'";
        } else {
            $out .= $walk->detailsPageUrl . " target='_blank' ";
        }

        $out .= ">";
        return $out;
    }

    private static function getWalkUrl($id) {
        return "index.php?option=com_content&view=article&id=" . self::$dispArticle . "&Itemid=" . self::$dispMenu . "&walk=" . $id;
    }

    public function getPrintButton($id) {
        $isModal = JRequest::getVar('print') == 1; // 'print=1' will only be present in the url of the modal window, not in the presentation of the page
        if ($isModal) {
            $href = '"#" onclick="window.print(); return false;"';
        } else {
            $href = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
            $url = self::getWalkUrl($id) . "&print=1";
            //  $href = "\"javascript:window.open('$url','win2','" . $href . "'); return false;\"";
            $href = "\"javascript:window.open('$url','win2');\"";
            $href = $href;
        }
        echo "<a href=$href;>Click for Printing</a>";
    }

}
