<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of items
 *
 * @author Chris Vaughan
 */
class RLeafletCsvItems {

    public $items = [];
    public $displayMap = false;
    public $paginateList = true;
    public $rows = 0;

    public function __construct() {
        
    }

    public function addItem($item) {
        $this->items[] = $item;
        $item->jpclass="var".count($this->items);
    }

    public function getItem($no) {
        if (array_key_exists ($no,$this->items)){
             return $this->items[$no];
        } else {
            return null;
        }
    }
}
