<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of domain
 *
 * @author Chris Vaughan
 */
class RWatchfulDomain {

    private $name;
    private $websites;

    public function __construct($name) {
        $this->name = $name;
        $this->websites = [];
    }

    public function addSite($site) {
        $this->websites[] = $site;
    }

    public function getSites() {
        return $this->websites;
    }

    public function getName() {
        return $this->name;
    }

}
