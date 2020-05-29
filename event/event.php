<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of calendarevent
 *
 * @author Chris Vaughan
 */
abstract class REvent {

    abstract protected function EventDate(); // as date

    abstract protected function EventText(); // as text string

    abstract protected function EventLink($display, $text); // as A tag

    abstract protected function EventLinks(); // as url

    abstract protected function EventStatus(); //  published,cancelled etc

    abstract protected function Event_ics($icsfile); //  ics icalandar format

    public function EventDateYYYYMMDD() {
        $out = $this->EventDate()->format('Y-m-d');
        return $out;
    }

    public function EventList($display, $text) {
        $class="";
        $text = $this->EventText();
        $link = $this->EventLink($display, $text);
        $out = "";
        $out.= "<div class='event-list-cal-event-single-link " . $class . $this->EventStatus() . "'>" . PHP_EOL;
        $out.= $link . PHP_EOL;
        $out.="<div class='events links'>";
        $out.=$this->EventLinks();
        $out.="</div>";
        $out.= "<hr/></div>" . PHP_EOL;
        return $out;
    }

}
