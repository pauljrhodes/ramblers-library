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
    protected $dispMenu = 0;
    protected $dispArticle = 0;

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
        $this->dispMenu = $menu;
        $this->dispArticle = $article;
    }

    public function getWalkHref($walk, $desc) {
        $out = "<a href=";
        if ($this->dispArticle > 0) {
            $out .= "\"javascript:gotoURL(" . $this->dispArticle . "," . $this->dispMenu . "," . $walk->id . ");\" >";
        } else {
            $out .= "'" . $walk->detailsPageUrl . "' target='_blank'>";
        }
        $out.=$desc . "</a>";
        return $out;
    }
       public function getWalkMapHref($walk, $desc) {
        $out = "<a href=";
        if ($this->dispArticle > 0) {
            $out .= "'javascript:gotoURL(" . $this->dispArticle . "," . $this->dispMenu . "," . $walk->id . ");' >";
        } else {
            $out .= "'" . $walk->detailsPageUrl . "' target='_blank'>";
        }
        $out.=$desc . "</a>";
        return $out;
    }

}
