<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class RJsonwalksLocation {

    public $description;        // text description of the location
    public $time;               // time as datetime object or "" if no time
    public $timeHHMM;           // time as string hh:mm am/pam or "No time"
    public $timeHHMMshort;      // time as $timeHHMM but without minutes if zero
    public $gridref;            // OS grid reference of the location
    public $easting;            // easting of the location
    public $northing;            // northing of the location
    public $longitude;          // Longitude of the location
    public $latitude;           // Latitude of the location
    public $postcode;           // either a postcode or null
    public $postcodeDistance;   // Distance of postcode from location or null
    public $postcodeDirection;   // Distance of postcode from location or null
    public $postcodeDirectionAbbr;
    public $postcodeLatitude;   // Longitude of the postcode or null
    public $postcodeLongitude;  // Latitude of the postcode or null
    public $type;               // type of location, meet,start,finish
    public $exact;              // true or false

    function __construct($value, $walkDate) {
        $this->description = RHtml::convertToText($value->description);
        $day = $walkDate->format('Ymd ');
        $this->time = DateTime::createFromFormat('Ymd H:i:s', $day . $value->time);
        If ($this->time === false) {
            $this->time = "";
            $this->timeHHMM = "No time";
            $this->timeHHMMshort = "No time";
        } else {
            $this->timeHHMM = $this->time->format('g:ia');
            $this->timeHHMMshort = str_replace(":00", "", $this->timeHHMM);
            if ($this->timeHHMMshort == "12am") {
                $this->time = "";
                $this->timeHHMM = "No time";
                $this->timeHHMMshort = "No time";
            }
        }
        $this->gridref = $value->gridRef;
        $this->easting = $value->easting;
        $this->northing = $value->northing;
        $this->latitude = $value->latitude;
        $this->longitude = $value->longitude;
        $this->postcode = $value->postcode;
        $this->postcodeLatitude = $value->postcodeLatitude;
        $this->postcodeLongitude = $value->postcodeLongitude;
        $this->type = $value->typeString;
        $this->exact = $value->showExact == "true";
        if ($this->postcode != null) {
            $lat1 = $this->postcodeLatitude;
            $lon1 = $this->postcodeLongitude;
            $lat2 = $this->latitude;
            $lon2 = $this->longitude;
            $this->postcodeDistance = 1000 * round(RGeometryGreatcircle::distance($lat1, $lon1, $lat2, $lon2, "KM"), 3); // metres
            $this->postcodeDirection = RGeometryGreatcircle::direction($lat1, $lon1, $lat2, $lon2);
            $this->postcodeDirectionAbbr = RGeometryGreatcircle::directionAbbr($this->postcodeDirection);
        }
    }

    public function getTextDescription() {
        $textdescription = "";
        switch ($this->type) {
            case "Meeting":
                $textdescription = "Meet: ";
                break;
            case "Start":
                if ($this->exact) {
                    $textdescription = "Start: ";
                } else {
                    $textdescription = "Walking area: ";
                }
                break;
            case "End":
                $textdescription = "Finish: ";
                break;
        }
        if ($this->exact) {
            if ($this->time != "") {
                $textdescription .= $this->timeHHMMshort . " @ ";
            }
        }
        $place = $this->gridref;
        if ($this->postcode <> null) {
            $place .= ", " . $this->postcode;
        }
        if ($this->description != '') {
            $textdescription .= $this->description . ' (' . $place . ')';
        } else {
            $textdescription .= $place;
        }
        return $textdescription;
    }

    public function getTextTime() {
        if ($this->time != "") {
            return $this->time->format('G:i:s');
        }
        return "";
    }

    public function distanceFrom($easting, $northing) {
        $dele = ($this->easting - $easting) / 1000;
        $deln = ($this->northing - $northing) / 1000;
        $dist = sqrt($dele * $dele + $deln * $deln);
        return $dist;
    }

    public function distanceFromLatLong($lat1, $lon2) {
        $lat2 = $this->latitude;
        $lon2 = $this->longitude;
        $dist = RGeometryGreatcircle::distance($lat1, $lon1, $lat2, $lon2, "KM");
        return $dist;
    }

    static function firstTime($loc1, $loc2) {
        if ($loc1 == null) {
            return $loc2->time;
        }
        if ($loc2 == null) {
            return $loc1->time;
        }
        if ($loc1->time == null) {
            return $loc2->time;
        }
        if ($loc2->time == null) {
            return $loc1->time;
        }
        if (!$loc1->exact) {
            return $loc2->time;
        }
        if (!$loc2->exact) {
            return $loc1->time;
        }
        if ($loc1->time < $loc2->time) {
            return $loc1->time;
        }
        return $loc2->time;
    }

    static function lastTime($loc1, $loc2) {
        if ($loc1 == null) {
            return $loc2->time;
        }
        if ($loc2 == null) {
            return $loc1->time;
        }
        if (!$loc1->exact) {
            return $loc2->time;
        }
        if (!$loc2->exact) {
            return $loc1->time;
        }
        if ($loc1->time > $loc2->time) {
            return $loc1->time;
        }
        return $loc2->time;
    }

    public function displayPostcode($detailsPageUrl) {
        $dist = $this->postcodeDistance; // metres
        $direction = $this->postcodeDirection;
        If ($dist < 100) {
            $note = "Postcode is within 100m of location";
            $link = "";
            $distclass = " distclose";
        } else {
            if ($dist < 500) {
                $distclass = " distnear";
            } else {
                $distclass = " distfar";
            }
            $note = $this->type . " place is " . $dist . " metres " . $direction . " of postcode. ";
            $note.= "Click to display the locations of the Postcode(P) and " . $this->type . " locations";
            $note2 = $dist . " metres " . RGeometryGreatcircle::directionAbbr($direction);
            $link = $this->getPostcodeMap($note2, $detailsPageUrl);
        }
        $pc = "<abbr title='" . $note . "'><b>Postcode</b>: " . $this->postcode . " ";
        $pc.=$link;
        $pc.= "</abbr>";
        $out = RHtml::withDiv("postcode " . $distclass, $pc, JRequest::getVar('print') == 1);
        return $out;
    }

    public function getDirectionsMap($text) {
        if ($this->exact) {
            $code = "https://www.google.com/maps/dir/Current+Location/[lat],[long]";
            $code = str_replace("[lat]", $this->latitude, $code);
            $code = str_replace("[long]", $this->longitude, $code);

            $out = "<span class='mappopup' onClick=\"javascript:window.open('" . $code . "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" . $text . "]</span>";
            return $out;
        } else {
            return "";
        }
    }

    public function getOSMap($text) {
        if ($this->exact) {
            $code = "http://streetmap.co.uk/loc/[lat],[long]&amp;Z=115";
            $code = str_replace("[lat]", $this->latitude, $code);
            $code = str_replace("[long]", $this->longitude, $code);
            $out = "<span class='mappopup' onClick=\"javascript:window.open('" . $code . "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" . $text . "]</span>";
            return $out;
        } else {
            return "";
        }
    }

    public function getMap($text1, $text2) {
        $out = "";
        If ($this->exact) {
            $out.=" " . $this->getDirectionsMap($text1);
        } else {
            $out.=" " . $this->getAreaMap($text2);
        }
        return $out;
    }

    public function getAreaMap($text) {
        if (!$this->exact) {
            $code = "http://maps.google.com/maps?z=13&amp;t=h&amp;ll=[lat],[long]";
            $code = str_replace("[lat]", $this->latitude, $code);
            $code = str_replace("[long]", $this->longitude, $code);
            $out = "<span class='mappopup' onClick=\"javascript:window.open('" . $code . "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=800,height=600');\">[" . $text . "]</span>";
            return $out;
        } else {
            return "";
        }
    }

    public function getPostcodeMap($text, $detailsPageUrl) {
        if ($this->exact) {
            $out = "<span class='mappopup' onClick=\"javascript:window.open('" . $detailsPageUrl . "', '_blank','toolbar=yes,scrollbars=yes,left=50,top=50,width=900,height=600');\">[" . $text . "]</span>";
            return $out;
        } else {
            return "";
        }
    }

    public function getDuration($time) {

        if (get_class($time) != 'DateTime') {
            return "";
        }
        if (get_class($this->time) != 'DateTime') {
            return "";
        }
        // calc time difference
        $interval = $this->time->diff($time);

        return $interval->format("%H:%I") . "hrs";
    }

    function __destruct() {
        
    }

}
