<?php

// no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Description of items
 *
 * @author Chris Vaughan
 */
class RJsonwalksItems {
    public $items;
    //put your code here
     function __construct($values) {
         foreach ($values->items as $value) {
             $item= new RJsonwalksItem($value);
             $this->items[]=$item;
         }
     }
     public function getItems(){
         return $this->items;
     }
}
