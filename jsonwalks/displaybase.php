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
    //   protected $printOn = false;
    public $displayGradesIcon = true;
    public $displayGradesSidebar = true;
    public $emailDisplayFormat = 1;
    // 1 display mailto link to contact, Obfuscated to prevent harvesting by bots and spammers, without visible changes to the address for human visitors.
    // 2 link to ramblers.org.uk form to email contact 
    // 3 do not display
    // 4 display as name (at) domain
//    protected $dispMenu = 0;
//    protected $dispArticle = 0;
    private $script = null;
    private $options = null;

    // 0 display walk via ramblers.org.uk
    // >0 display via local site article

    abstract protected function DisplayWalks($walks);

    public function __construct() {
        $this->script = new RLeafletScript();
        $this->options = new RLeafletMapoptions();
        // default map options for display of walk
        $this->options->mapHeight = "250px";
        $this->options->rightclick = true;
        $this->options->fullscreen = true;
        $this->options->copyright = false;
        $this->script->add($this->options);
    }

    public function alwaysDisplayStartTime($value) {
        $this->displayStartTime = $value;
    }

    public function alwaysDisplayStartDescription($value) {
        $this->displayStartDescription = $value;
    }
}
