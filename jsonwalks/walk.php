<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class RJsonwalksWalk extends REvent {

// administration items
    public $id;                     // database ID of walk on Walks Finder
    public $status;                 // whether the walk is published, cancelled etc
    public $groupCode;              // group code e.g. SR01
    public $groupName;              // the group name e.g. Derby & South Derbyshire
    public $dateUpdated;            // date of the walk as a datetime object
    public $dateCreated;            // date of the walk as a datetime object
    public $cancellationReason;     // text reason walk cancelled
// basic walk details
    public $walkDate;               // date of the walk as a datetime object
    Public $dayofweek;              // day of the week as text
    public $day;                    // the day number as text
    public $month;                  // the month as text
    public $title;                  // title of the walk
    public $description = "";       // description of walk with html tags removed
    public $descriptionHtml = "";   // description of walk with html tags
    public $additionalNotes = "";   // the additional notes field as text
    public $detailsPageUrl = "";    // url to access the ramblers.org.uk page for this walk
// contact
    public $isLeader = false;       // is the contact info for the leader of the walk
    public $walkLeader = "";        // walk leader if isLeader is false
    public $contactName = "";       // contact name
    public $email = "";             // email address for contact
    private $emailAddr = "";             // email address for contact
    public $telephone1 = "";        // first telephone number of contact
    public $telephone2 = "";        // second telephone number of contact
// meeting place
    public $hasMeetPlace = false;   // true or false
    public $meetLocation;           // a [[RJsonwalksLocation]] object if hasMeetPlace=true
// starting place
    public $startLocation;          // a [[RJsonwalksLocation]] object for the start
// finish place
    public $isLinear = false;       // true if walk has a finishing place otherwise false
    public $finishLocation;         // a [[RJsonwalksLocation]] object if isLinear=true
    // not always provided so check if null
    public $finishTime;             // finishTime as dateTime or null
// grades ,length
    public $nationalGrade = "";     // national grade as full word
    public $localGrade = "";        // local grade
    public $distanceMiles;          // distance of walk as number in miles
    public $distanceKm;             // distance of walk as number in kilometres
    public $pace;                   // the pace of the walk or null
    Public $ascentMetres;           // the ascent in metres or null
    Public $ascentFeet;             // the ascent in feet or null
    Public $strands;                // RJsonwalksItems object or null
    Public $festivals;              // RJsonwalksItems object or null
    public $suitability;          // RJsonwalksItems object or null
    public $surroundings;          // RJsonwalksItems object or null
    public $theme;          // RJsonwalksItems object or null
    public $specialStatus;          // RJsonwalksItems object or null
    public $facilities;          // RJsonwalksItems object or null
// extra derived values
    private $sortTime;
    private $icsDayEvents = false;
    private static $gradeSidebarDisplayed = false;

    const SORT_DATE = 0;
    const SORT_CONTACT = 1;
    const SORT_NATIONALGRADE = 2;
    const SORT_LOCALGRADE = 3;
    const SORT_DISTANCE = 4;
    const SORT_TELEPHONE1 = 5;
    const SORT_TELEPHONE2 = 6;
    const SORT_TIME = 7;
    const TIMEFORMAT = "Y-m-d\TH:i:s";

    public function __construct($item) {

        try {
            $this->id = $item->id; // admin details
            $this->status = $item->status->value;
            $this->groupCode = $item->groupCode;
            $this->groupName = $item->groupName;
            $item->dateUpdated = substr($item->dateUpdated, 0, 19);
            $item->dateCreated = substr($item->dateCreated, 0, 19);
            $this->dateUpdated = DateTime::createFromFormat(self::TIMEFORMAT, $item->dateUpdated);
            $this->dateCreated = DateTime::createFromFormat(self::TIMEFORMAT, $item->dateCreated);
            $this->cancellationReason = $item->cancellationReason;
// basic walk details
            $this->walkDate = DateTime::createFromFormat(self::TIMEFORMAT, $item->date);
            $this->detailsPageUrl = $item->url;
            $this->title = RHtml::convertToText($item->title);
            $this->descriptionHtml = $item->description;
            $this->description = RHtml::convertToText($item->description);

            $this->additionalNotes = $item->additionalNotes;
            $this->isLinear = $item->isLinear == "true";
            switch ($item->finishTime) {
                case null:
                    $this->finishTime = null;
                    break;
                case "00:00:00":
                    $this->finishTime = null;
                    break;
                default:
                    $day = $this->walkDate->format('Ymd ');
                    $this->finishTime = DateTime::createFromFormat('Ymd H:i:s', $day . $item->finishTime);
                    break;
            }

            $this->nationalGrade = $item->difficulty->text;
            $this->localGrade = $item->gradeLocal;
            $this->distanceMiles = $item->distanceMiles;
            $this->distanceKm = $item->distanceKM;
            $this->pace = $item->pace;
            $this->ascentFeet = $item->ascentFeet;
            $this->ascentMetres = $item->ascentMetres;
// contact details
            if ($item->walkContact != null) {
                $this->isLeader = $item->walkContact->isWalkLeader == "true";
                $this->contactName = trim($item->walkContact->contact->displayName);
                $this->emailAddr = $item->walkContact->contact->email;
                $this->email = str_replace("@", " (at) ", $this->emailAddr);
                $this->telephone1 = $item->walkContact->contact->telephone1;
                $this->telephone2 = $item->walkContact->contact->telephone2;
            }
            $this->walkLeader = $item->walkLeader;
// read strands
            if (count($item->strands->items) > 0) {
                $this->strands = new RJsonwalksItems($item->strands);
            }
// read festivals
            if (count($item->festivals->items) > 0) {
                $this->festivals = new RJsonwalksItems($item->festivals);
            }
// read suitability
            if (count($item->suitability->items) > 0) {
                $this->suitability = new RJsonwalksItems($item->suitability);
            }
// read surroundings
            if (count($item->surroundings->items) > 0) {
                $this->surroundings = new RJsonwalksItems($item->surroundings);
            }
// read theme
            if (count($item->theme->items) > 0) {
                $this->theme = new RJsonwalksItems($item->theme);
            }
// read specialStatus
            if (count($item->specialStatus->items) > 0) {
                $this->specialStatus = new RJsonwalksItems($item->specialStatus);
            }
            // read facilities
            if (count($item->facilities->items) > 0) {
                $this->facilities = new RJsonwalksItems($item->facilities);
            }
// pocess meeting and starting locations
            $this->processPoints($item->points);
            $this->createExtraData();
        } catch (Exception $ex) {
            $this->errorFound = 2;
        }
    }

    public function getEmail($option = 1, $withtitle = false) {
        if ($withtitle) {
            switch ($option) {
                case 1:
                    return "<b>Email: </b>" . $this->emailAddr;
                    break;
                case 2:
                    $printOn = JRequest::getVar('print') == 1;
                    $link = "http://www.ramblers.org.uk/go-walking/find-a-walk-or-route/contact-walk-organiser.aspx?walkId=";
                    return RHtml::withDiv("email", "<b>Email: </b><a href='" . $link . $this->id . "' target='_blank'>Contact via ramblers.org.uk</a>", $printOn);
                case 3:
                    return "";
                    break;
                case 4:
                    return "<b>Email: </b>" . str_replace("@", " (at) ", $this->emailAddr);
                    break;
                default:
                    return "Invalid option specified for \$display->emailDisplayFormat";
                    break;
            }
        } else {
            switch ($option) {
                case 1:
                    return $this->emailAddr;
                    break;
                case 2:
                    $printOn = JRequest::getVar('print') == 1;
                    $link = "http://www.ramblers.org.uk/go-walking/find-a-walk-or-route/contact-walk-organiser.aspx?walkId=";
                    return "<a href='" . $link . $this->id . "' target='_blank'>Email contact via ramblers.org.uk</a>";
                    break;
                case 3:
                    return "";
                    break;
                case 4:
                    return str_replace("@", " (at) ", $this->emailAddr);
                    break;
                default:
                    return "Invalid option specified for \$display->emailDisplayFormat";
            }
        }
    }

    public function getValue($type) {
        switch ($type) {
            case self::SORT_CONTACT :
                return $this->contactName;
            case self::SORT_DATE:
                return $this->walkDate;
            case self::SORT_DISTANCE:
                return $this->distanceMiles;
            case self::SORT_LOCALGRADE:
                return $this->localGrade;
            case self::SORT_NATIONALGRADE:
                return $this->nationalGrade;
            case self::SORT_TELEPHONE1:
                return $this->telephone1;
            case self::SORT_TELEPHONE2:
                return $this->telephone2;
            case self::SORT_TIME:
                return $this->sortTime;
            default:
                return NULL;
        }
    }

    public function isCancelled() {
        return strtolower($this->status) == "cancelled";
    }

    public function setNewWalk($date) {
        if ($this->status == "New") {
            $this->status = "Published";
        }
        if ($this->status == "Published") {
            if ($this->dateUpdated > $date) {
                $this->status = "New";
            }
        }
    }

    private function createExtraData() {
        $this->dayofweek = $this->walkDate->format('l');
        $this->month = $this->walkDate->format('F');
        $this->day = $this->walkDate->format('jS');
        if ($this->meetLocation != NULL) {
            $this->sortTime = $this->meetLocation->time;
        }
        if ($this->sortTime == NULL) {
            $this->sortTime = $this->startLocation->time;
        }
    }

    private function processPoints($points) {
        $this->hasMeetPlace = false;
        foreach ($points as $value) {
            if ($value->typeString == "Meeting") {
                $this->hasMeetPlace = true;
                $this->meetLocation = new RJsonwalksLocation($value, $this->walkDate);
            }
            if ($value->typeString == "Start") {
                $this->startLocation = new RJsonwalksLocation($value, $this->walkDate);
            }
            if ($value->typeString == "End") {
                $this->finishLocation = new RJsonwalksLocation($value, $this->walkDate);
            }
        }
    }

    public function EventDate() {
        return $this->walkDate;
    }

    public function EventText() {
        $text = "";
        if ($this->hasMeetPlace) {
            $text.=$this->meetLocation->timeHHMMshort;
        }
        if ($this->startLocation->time != "") {
            if ($text != "") {
                $text.="/";
            }
            $text.=$this->startLocation->timeHHMMshort;
        }
        $text = $text . ", " . $this->title;
        if ($this->distanceMiles > 0) {
            $text .=", " . $this->distanceMiles . "mi/" . $this->distanceKm . "km";
        }
        return $text;
    }

    public function EventLink($display, $text) {
        return $display->getWalkHref($this, $text);
        // return $this->detailsPageUrl;
    }

    public function EventLinks() {
        $out = "";
        If ($this->hasMeetPlace) {
            $out.="Meet:" . $this->meetLocation->getOSMap("OS Map");
            $out.=" " . $this->meetLocation->getDirectionsMap("Directions");
        }

        $out.=" Start:" . $this->startLocation->getOSMap("OS Map");
        $out.=" " . $this->startLocation->getMap("Directions", "Area Map");
        return $out;
    }

    public function EventStatus() {
        return "walk" . $this->status;
    }

    public function Event_ics($icsfile) {
        if ($this->hasMeetPlace) {
            $meetLocation = $this->meetLocation->getTextDescription();
            $meetLocation .="; \\n";
        } else {
            $meetLocation = "";
        }
        $startLocation = $this->startLocation->getTextDescription();
        $before = $meetLocation . $startLocation . "\\nDescription: ";
        $after = "\\nContact: " . $this->contactName . " (" . $this->telephone1 . " " . $this->telephone2 . "); \\n";
        if ($this->localGrade <> "") {
            $after .= "Grade: " . $this->localGrade . "/" . $this->nationalGrade . "; \\n ";
        } else {
            $after .= "Grade: " . $this->nationalGrade . "; \\n ";
        }
        $after .= $this->detailsPageUrl;
        $after .="\\nNote: Finish times are very approximate!";
        if ($this->additionalNotes != '') {
            $after .= "\\nNotes: " . strip_tags($this->additionalNotes);
        }
        $summary = $this->title;
        if ($this->distanceMiles > 0) {
            $summary .=", " . $this->distanceMiles . "mi/" . $this->distanceKm . "km";
        }
        $now = new datetime();
        $icsfile->addRecord("BEGIN:VEVENT");
        $this->addIcsTimes($icsfile);
        $icsfile->addRecord("LOCATION:", $startLocation);
        $icsfile->addRecord("TRANSP:OPAQUE");
        $icsfile->addSequence($this->dateUpdated);
        $icsfile->addRecord("UID: walk" . $this->id . "-isc@ramblers-webs.org.uk");
        $icsfile->addRecord("ORGANIZER;CN=" . $this->groupName . ":mailto:ignore@ramblers-webs.org.uk");
        if ($this->isCancelled()) {
            $icsfile->addRecord("METHOD:CANCEL");
            $icsfile->addRecord("SUMMARY: CANCELLED ", RHtml::convertToText($summary));
            $icsfile->addRecord("DESCRIPTION: CANCELLED - REASON: ", RHtml::convertToText($this->cancellationReason) . " (" . $this->description . ")");
        } else {
            $icsfile->addRecord("SUMMARY:", RHtml::convertToText($summary));
            $icsfile->addRecord("DESCRIPTION:", $before . $this->description . $after);
            $icsfile->addRecord("X-ALT-DESC;FMTTYPE=text/html:", $before . $this->descriptionHtml . $after, true);
        }
        $icsfile->addRecord("CATEGORIES:", "Walk," . $this->groupName);
        $icsfile->addRecord("DTSTAMP;VALUE=DATE-TIME:", $this->dateTimetoUTC($now));
        $icsfile->addRecord("CREATED;VALUE=DATE-TIME:", $this->dateTimetoUTC($this->dateCreated));
        $icsfile->addRecord("LAST-MODIFIED;VALUE=DATE-TIME:", $this->dateTimetoUTC($this->dateUpdated));
        $icsfile->addRecord("PRIORITY:1");
        $icsfile->addRecord("URL;VALUE=URI:", $this->detailsPageUrl);
        $icsfile->addRecord("CLASS:PUBLIC");
        $icsfile->addRecord("END:VEVENT");
        return;
    }

    private function dateTimetoUTC($date) {
        $utcdate = new DateTime($date->format('Ymd His'));
        $utcdate->setTimezone(new DateTimeZone('UTC'));
        return $utcdate->format('Ymd\THis\Z');
    }

    private function addIcsTimes($icsfile) {
        if ($this->icsDayEvents) {
            $icsfile->addRecord("DTSTART;VALUE=DATE:" . $this->walkDate->format('Ymd'));
        } else {
            $time = $this->getFirstTime();
            if ($time <> null) {
                $icsfile->addRecord("DTSTART;VALUE=DATE-TIME:" . $time->format('Ymd\THis'));
                $time = $this->getFinishTime();
                if ($time <> null) {
                    $icsfile->addRecord("DTEND;VALUE=DATE-TIME:" . $time->format('Ymd\THis'));
                }
            } else {
                $icsfile->addRecord("DTSTART;VALUE=DATE:" . $this->walkDate->format('Ymd'));
            }
        }
    }

    private function getFirstTime() {
        $starttime = RJsonwalksLocation::firstTime($this->meetLocation, $this->startLocation);
        if ($starttime == "") {
            $starttime = null;
        }
        return $starttime;
    }

    private function getFinishTime() {
        if ($this->finishTime != null) {
            return $this->finishTime;
        }
        // calculate end time
        $lasttime = RJsonwalksLocation::lastTime($this->meetLocation, $this->startLocation);
        $durationFullMins = ceil($this->distanceMiles / 2) * 60;
        if ($this->startLocation->exact == false) {
            $durationFullMins += 60;
        }
        $intervalFormat = "PT" . $durationFullMins . "M";
        $interval = new DateInterval($intervalFormat);
        $lasttime = $lasttime->add($interval);
        return $lasttime;
    }

    public static function nationalGradesLink() {
        $out = '<p></p><p>Description of <a href="libraries/ramblers/pages/grades.html" class="jcepopup" data-mediabox="1">National Grades</a></p>';
        echo $out;
    }

    public function getGradeImage() {
        $image = "libraries/ramblers/images/grades/base.jpg";
        switch ($this->nationalGrade) {
            case "Easy Access":
                $image = "libraries/ramblers/images/grades/grade-ea.jpg";
                break;
            case "Easy":
                $image = "libraries/ramblers/images/grades/grade-e.jpg";
                break;
            case "Leisurely":
                $image = "libraries/ramblers/images/grades/grade-l.jpg";
                break;
            case "Moderate":
                $image = "libraries/ramblers/images/grades/grade-m.jpg";
                break;
            case "Strenuous":
                $image = "libraries/ramblers/images/grades/grade-s.jpg";
                break;
            case "Technical":
                $image = "libraries/ramblers/images/grades/grade-t.jpg";
                break;
            default:
                break;
        }
        return $image;
    }

    function getGradeSpan($class) {
        $tag = "";
        $img = $this->getGradeImg();
        switch ($this->nationalGrade) {
            case "Easy Access":
                $tag = "<span data-descr='Easy Access' class='" . $class . "'><span class='grade easy-access " . $class . "' onclick='javascript:dGH()'>" . $img . "</span></span>";
                break;
            case "Easy":
                $tag = "<span data-descr='Easy' class='" . $class . "'><span class='grade easy " . $class . "' onclick='javascript:dGH()'>" . $img . "</span></span>";
                break;
            case "Leisurely":
                $tag = "<span data-descr='Leisurely' class='" . $class . "'><span class='grade leisurely " . $class . "' onclick='javascript:dGH()'>" . $img . "</span></span>";
                break;
            case "Moderate":
                $tag = "<span data-descr='Moderate' class='" . $class . "'><span class='grade " . $class . "' onclick='javascript:dGH()'>" . $img . "</span></span>";
                break;
            case "Strenuous":
                $tag = "<span data-descr='Strenuous' class='" . $class . "'><span class='grade strenuous " . $class . "' onclick='javascript:dGH()'>" . $img . "</span></span>";
                break;
            case "Technical":
                $tag = "<span data-descr='Technical' class='" . $class . "'><span class='grade technical " . $class . "' onclick='javascript:dGH()'>" . $img . "</span></span>";
                break;
            default:
                break;
        }
        return $tag;
    }

    function getGradeImg() {
        $base = JURI::base();
        $folder = JURI::base(true);
        $url = $folder . "/libraries/ramblers/images/grades/";
     //   if (strpos($base, 'localhost') !== false) {
     //   }

        switch ($this->nationalGrade) {
            case "Easy Access":
                $url = "<img src='" . $url . "grade-ea30.jpg' alt='Easy Access' height='30' width='30'>";
                break;
            case "Easy":
                $url = "<img src='" . $url . "grade-e30.jpg' alt='Easy' height='30' width='30'>";
                break;
            case "Leisurely":
                $url = "<img src='" . $url . "grade-l30.jpg' alt='Leisurely' height='30' width='30'>";
                break;
            case "Moderate":
                $url = "<img src='" . $url . "grade-m30.jpg' alt='Moderate' height='30' width='30'>";
                break;
            case "Strenuous":
                $url = "<img src='" . $url . "grade-s30.jpg' alt='Strenuous' height='30' width='30'>";
                break;
            case "Technical":
                $url = "<img src='" . $url . "grade-t30.jpg' alt='Technical' height='30' width='30'>";
                break;
        }
        return $url;
    }

    public function distanceFrom($easting, $northing, $distanceKm) {
        return $this->startLocation->distanceFrom($easting, $northing, $distanceKm);
    }

    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    function __destruct() {
        
    }

}
