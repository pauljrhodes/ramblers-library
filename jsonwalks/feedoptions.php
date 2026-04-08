<?php

/**
 * Description of feedoptions
 *
 * @author chris
 */
class RJsonwalksFeedoptions {

    private $sources = [];

    public function __construct($value = null) {
        // input can be a list of groups or a null string
        if ($value === null) {
            return;
        }
        $groups = strtoupper($value);
        $this->addWalksManagerGroupWalks($groups);
    }

    public function addWalksMangerGroupWalks($groups) {
        $this->addWalksManagerGroupWalks($groups);
    }

    public function addWalksManagerGroupWalks($groups, $period = null) {
        $this->checkGroups($groups);
        $readwalks = true;
        $readevents = true;
        $wellbeingWalks = false;
        $source = new RJsonwalksSourcewalksmanager();
        $source->_initialise($groups, $readwalks, $readevents, $wellbeingWalks,$period);
        $this->sources[] = $source;
    }

    public function addWalksManagerWellbeingWalks($groups) {
        $this->addWalksMangerWellbeingWalks($groups);
    }

    public function addWalksMangerWellbeingWalks($groups) {
        $this->checkGroups($groups);
        $readwalks = false;
        $readevents = false;
        $wellbeingWalks = true;
        $source = new RJsonwalksSourcewalksmanager();
        $source->_initialise($groups, $readwalks, $readevents, $wellbeingWalks);
        $this->sources[] = $source;
    }

    public function addWalksManagerGroupsInArea($lat, $long, $km) {
        $readwalks = true;
        $readevents = true;
        $wellbeingWalks = false;
        $source = new RJsonwalksSourcewalksmanagerarea();
        $source->_initialiseArea($lat, $long, $km, $readwalks, $readevents, $wellbeingWalks);
        $this->sources[] = $source;
    }

    public function addWalksManagerWellbeingInArea($lat, $long, $km) {
        $readwalks = false;
        $readevents = false;
        $wellbeingWalks = true;
        $source = new RJsonwalksSourcewalksmanagerarea();
        $source->_initialiseArea($lat, $long, $km, $readwalks, $readevents, $wellbeingWalks);
        $this->sources[] = $source;
    }

    public function addWalksEditorWalks($groupCode, $groupName, $site) {
        if (str_contains($site, "<")) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_("Site parameter must not contain html tags: " . $site), "error");
        } else {
            $source = new RJsonwalksSourcewalkseditor();
            $source->_initialise($groupCode, $groupName, $site);
            $this->sources[] = $source;
        }
    }

    private function checkGroups($input) {
        $groups = explode(",", $input);
        foreach ($groups as $group) {
            $len = strlen($group);
            if ($len !== 2 && $len !== 4) {
                $app = JFactory::getApplication();
                $msg = "INVALID group code supplied when retrieving walks from Walks manager, codes must be 2 or 4 characters only";
                $msg .= "<br>Code provided : " . $group;
                $app->enqueueMessage($msg, 'Error');
            }
        }
    }

    public function getWalks($walks) {
        foreach ($this->sources as $source) {
            $source->getWalks($walks);
        }
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        return;
    }
}
