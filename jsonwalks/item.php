<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Description of items
 *
 * @author Chris Vaughan
 */
class RJsonwalksItem {

    //put your code here
    public $name;

    function __construct($value) {
        if (isset($value)) {
            if (property_exists($value, "text")) {
                $this->name = $value->text;
            }
        }
    }
    public function getName() {
        return $this->name;
    }

}
