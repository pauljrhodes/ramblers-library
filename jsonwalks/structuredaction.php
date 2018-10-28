<?php

/*
 * schema.org definition of action
 */

/**
 * Description of structuredaction
 *
 * @author Chris
 * @author Paul
 */
class RJsonwalksStructuredaction {
    public $type;
    public $distance;
    public $exercisetype;
	
    public function __construct($miles, $grade) {
        $this->type = "ExerciseAction";
        $this->distance = $miles . " miles";
        $this->exercisetype = $grade . " Walk";
    }

}
