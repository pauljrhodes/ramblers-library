<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of group
 *
 * @author Chris Vaughan
 */
class REventGroup {

    private $arrayofevents;
    private $class = "event";
    static $id = 0;

    function __construct() {
        $this->arrayofevents = array();
    }

    public function setWalkClass($class) {
        $this->walkClass = $class;
    }

    public function addWalks($feed) {
        $walks = $feed->getWalks();
        $arrayofwalks = $walks->allWalks();
        foreach ($arrayofwalks as $walk) {
            $this->arrayofevents[] = $walk;
        }
    }

    public function addWalksArray($arrayofwalks) {
        foreach ($arrayofwalks as $walk) {
            $this->arrayofevents[] = $walk;
        }
    }

    public function getEvents() {
        return $this->arrayofevents;
    }

    public function getLastDate() {
        $lastdate = date("Y-m-d");
        foreach ($this->arrayofevents as $event) {
            $currentdate = $event->EventDateYYYYMMDD();
            if ($currentdate > $lastdate) {
                $lastdate = $currentdate;
            }
        }
        return $lastdate;
    }

    public function addEvent($display, $text, $currentDate) {
        $found = false;
        $out = "";
        foreach ($this->arrayofevents as $event) {
            if ($event->EventDateYYYYMMDD() === $currentDate) {
                // echo "found";
                if ($found == false) {
                    self::$id+=1;
                    $ident = "ev" . strval(self::$id);
                    $out.="<div class='event-list-cal-event'>" . PHP_EOL;
                    $out.= "<div class='event-list-cal-day'><a onclick=\"ra_toggle_visibility('" . $ident . "')\">" . $text . "</a></div>" . PHP_EOL;
                    $out.="<div class='event-list-cal-hover' id='" . $ident . "'>" . PHP_EOL;
                    $out.= $event->EventDate()->format('l, jS');
                }
                $found = true;
                $out.= $event->EventList($display, $text);
            }
        }
        if ($found) {
            $out.= "</div>";
            $out.= "</div>";
            //$out.= "</a>";
        } else {
            $out.= "<div class='event-list-cal-day'>" . $text . "</div>";
            // $out.= $text;
        }
        return $out;
    }

    public function getIcalendarFile($icsfile) {
        foreach ($this->arrayofevents as $event) {
            $event->Event_ics($icsfile);
        }
    }

}
