<?php

/**
 * @author  Xu Ding
 * @email   thedilab@gmail.com
 * @website http://www.StarTutorial.com
 * */
class RCalendar {

    private $size;

    /**
     * Constructor
     */
    public function __construct($size) {
        $this->size = $size;
    }

    /*     * ******************* PROPERTY ******************* */

    private $dayLabels = array("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");
    private $currentYear = 2000;
    private $currentMonth = 1;
    private $currentDay = 0;
    private $currentDate = null;
    private $daysInMonth = 0;
    private $events;
    static $copyno = 0;
    private $baseno = 1;

    /*     * ******************* PUBLIC ********************* */

    public function show($events) {
        self::$copyno += 1;
        $lastdate = $events->getLastDate();
        $this->events = $events;
        $this->baseno = self::$copyno * 20;
        $today = getdate();
        $this->currentYear = $today["year"];
        $this->currentMonth = $today["mon"];
        // $year = date("Y", time());
        // $month = date("m", time());
        $enddate = sprintf("%04d", $this->currentYear) . "-" . sprintf("%02d", $this->currentMonth) . "-01";
        $navtype = navigationtype::first;
        $i = 0;
        do {
            if (substr($lastdate, 0, 7) == substr($enddate, 0, 7)) {
                $navtype = navigationtype::last;
            }
            $this->showMonth($navtype);
            $i+=1;
            If ($i >= 12) {
                return;
            }
            $this->currentMonth+=1;
            if ($this->currentMonth == 13) {
                $this->currentMonth = 1;
                $this->currentYear+=1;
            }
            $navtype = navigationtype::both;
            $enddate = sprintf("%04d", $this->currentYear) . "-" . sprintf("%02d", $this->currentMonth) . "-01";
        } while ($lastdate > $enddate);
    }

    /**
     * print out the calendar
     */
    private function showMonth($navtype) {
        $this->currentDay = 0;
        $this->daysInMonth = $this->_daysInMonth($this->currentMonth, $this->currentYear);
        if ($navtype == navigationtype::first) {
            $disp = ' style="display: block;"';
        } else {
            $disp = ' style="display: none;"';
        }
        $class = "ra_calendar250";
        if ($this->size == 400) {
            $class = "ra_calendar400";
        }
        $content = '<div class=' . $class . ' id='. $this->getDivId($this->baseno + $this->currentMonth)  . $disp . '>' .
                '<div class="box">' .
                $this->_createNavi($navtype) .
                '</div>' .
                '<div class="box-content">' .
                '<ul class="label">' . $this->_createLabels() . '</ul>';
        $content.='<div class="clear"></div>';
        $content.='<ul class="dates">';
        $weeksInMonth = $this->_weeksInMonth($this->currentYear, $this->currentYear);
        // Create weeks in a month
        for ($i = 0; $i < $weeksInMonth; $i++) {
            //Create days in a week
            for ($j = 1; $j <= 7; $j++) {
                $content.=$this->_showDay($i * 7 + $j);
            }
        }
        $content.='</ul>';
        $content.='<div class="clear"></div>';
        $content.='</div>';
        $content.='</div>';
        echo $content;
    }

    /*     * ******************* PRIVATE ********************* */

    /**
     * create the li element for ul
     */
    private function _showDay($cellNumber) {
        if ($this->currentDay == 0) {
            $firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));
            if (intval($cellNumber) == intval($firstDayOfTheWeek)) {
                $this->currentDay = 1;
            }
        }
        if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
            $this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));
            $cellContent = $this->currentDay;
            $this->currentDay++;
        } else {
            $this->currentDate = null;
            $cellContent = null;
        }
        if ($cellContent != null) {
            $cellContent = $this->addEvents($cellContent, $this->currentDate);
        }
        return '<li id="li-' . $this->currentDate . '" class="' . ($cellNumber % 7 == 1 ? ' start ' : ($cellNumber % 7 == 0 ? ' end ' : ' ')) .
                ($cellContent == null ? 'mask' : '') . '">' . $cellContent . '</li>';
    }

    /**
     * create navigation
     */
    private function _createNavi($navtype) {
        $nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth) + 1;
        // $nextYear = $this->currentMonth == 12 ? intval($this->currentYear) + 1 : $this->currentYear;
        $preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth) - 1;
        // $preYear = $this->currentMonth == 1 ? intval($this->currentYear) - 1 : $this->currentYear;
        $out = '<div class="header">';
        if ($navtype != navigationtype::first) {
            $out.= '<a class="prev" ' . $this->getTogglePair($preMonth, $this->currentMonth) . ' >Prev</a>';
        }
        $out.= '<span class="title">' . date('Y M', strtotime($this->currentYear . '-' . $this->currentMonth . '-1')) . '</span>';
        if ($navtype != navigationtype::last) {
            $out.= '<a class="next" ' . $this->getTogglePair($nextMonth, $this->currentMonth) . ' >Next</a>';
        }
        $out.= '</div>';
        return $out;

        // <a href="#" onclick="toggle_visibility('foo');">Click here to toggle visibility of element #foo</a>
    }

    private function getTogglePair($one, $two) {
        $idone = $this->getDivId($this->baseno + $one);
        $idtwo = $this->getDivId( $this->baseno + $two);
        return ' onclick="ra_toggle_visibilities('. $idone . ',' . $idtwo .')"';
    }
    private function getDivId($no){
        return "'ra_cal" .sprintf("%04d", $this->baseno + $no)."'";
    }

    /**
     * create calendar week labels
     */
    private function _createLabels() {
        $content = '';
        foreach ($this->dayLabels as $index => $label) {
            $content.='<li class="' . ($label == 6 ? 'end title' : 'start title') . ' title">' . $label . '</li>';
        }
        return $content;
    }

    /**
     * calculate number of weeks in a particular month
     */
    private function _weeksInMonth($month, $year) {
        // find number of days in this month
        $daysInMonths = $this->_daysInMonth($month, $year);
        $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
        $monthEndingDay = date('N', strtotime($year . '-' . $month . '-' . $daysInMonths));
        $monthStartDay = date('N', strtotime($year . '-' . $month . '-01'));
        if ($monthEndingDay < $monthStartDay) {
            $numOfweeks++;
        }
        return $numOfweeks;
    }

    /**
     * calculate number of days in a particular month
     */
    private function _daysInMonth($month, $year) {
        return date('t', strtotime($year . '-' . $month . '-01'));
    }

    private function addEvents($cellContent, $currentDate) {
        //  echo $cellContent;
        //  echo $currentDate;
        return $this->events->addEvent($cellContent, $currentDate);
    }

}

abstract class navigationtype {

    const first = 0;
    const both = 1;
    const last = 2;

}

//- See more at: http://www.startutorial.com/articles/view/how-to-build-a-web-calendar-in-php#sthash.dyuf6D75.dpuf