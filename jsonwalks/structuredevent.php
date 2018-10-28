<?php

/*
 * schema.org definition of walk
 */

/**
 * Description of structuredevent
 *
 * @author Chris
 * @author Paul
 */
class RJsonwalksStructuredevent {
    public $context;
    public $type;
	public $id;
	public $organizer;
    public $name;
	public $url;
	public $sameas;
    public $startDate;
    public $endDate;
    public $image;
    public $description;
    public $location;
    public $performer;
	public $potentialaction;
	
	
    public function __construct($performer, $location, $potentialaction, $organizer) {
        $this->context = "http://schema.org/";
        $this->type = "Event";
        $this->performer = $performer;
        $this->location = $location;
		$this->potentialaction = $potentialaction;
		$this->organizer = $organizer;
    }

}
