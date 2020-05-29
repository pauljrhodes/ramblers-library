<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of areas
 *
 * @author Chris
 */
class ROrganisation {

    private $srfr;
    public $groups;
    public $areas;
    public $showLinks = true;
    public $showCodes = true;
    public $showGroups = true;
    Public $centreGroup = "";
    public $mapZoom = -1;

    function __construct() {
        $this->load();
    }

    public function load() {
        $url = "https://groups.theramblers.org.uk/";
        $CacheTime = 60; // 60 minutes
        $cacheLocation = $this->CacheLocation();
        $this->srfr = new RFeedhelper($cacheLocation, $CacheTime);
        // $this->srfr->setReadTimeout(60);
        $this->readFeed($url);
    }

    private function readFeed($rafeedurl) {

        $result = $this->srfr->getFeed($rafeedurl);
        $status = $result["status"];
        $contents = $result["contents"];

        switch ($status) {
            case RFeedhelper::OK:
                break;
            case RFeedhelper::READFAILED:
                $app = JApplicationCms::getInstance('site');
                $app->enqueueMessage(JText::_('Unable to fetch organisation data, data may be out of date: ' . $rafeedurl), 'warning');
                break;
            case RFeedhelper::FEEDERROR;
                $app = JApplicationCms::getInstance('site');
                $app->enqueueMessage(JText::_('Feed must use HTTP protocol: ' . $rafeedurl), 'error');
                break;
            case RFeedhelper::FEEDFOPEN:
                $app = JApplicationCms::getInstance('site');
                $app->enqueueMessage(JText::_('Not able to read feed using fopen: ' . $rafeedurl), 'error');
                break;
            default:
                break;
        }
        switch ($contents) {
            case NULL:
                $app = JApplicationCms::getInstance('site');
                $app->enqueueMessage(JText::_('Groups feed: Unable to read feed: ' . $rafeedurl), 'error');
                break;
            case "":
                echo '<b>Groups feed: No Groups found</b>';
                break;
            case "[]":
                echo '<b>Groups feed empty: No Groups found</b>';
                break;
            default:
                $json = json_decode($contents);
                unset($contents);
                $error = 0;
                if (json_last_error() == JSON_ERROR_NONE) {
                    foreach ($json as $value) {
                        $ok = $this->checkJsonProperties($value);
                        $error+=$ok;
                    }
                    if ($error > 0) {
                        $app = JApplicationCms::getInstance('site');
                        $app->enqueueMessage(JText::_('Groups feed: Json file format not supported: ' . $rafeedurl), 'error');
                    } else {
                        $this->convert($json);
                    }
                    unset($json);
                    break;
                } else {
                    $app = JApplicationCms::getInstance('site');
                    $app->enqueueMessage(JText::_('Groups feed: feed is not in Json format: ' . $rafeedurl), 'error');
                }
        }
    }

    private function convert($json) {
        $this->areas = array();
        $this->groups = array();
        foreach ($json as $item) {
            if ($item->scope == "A") {
                $group = new ROrganisationArea($item);
                $this->areas[$item->groupCode] = $group;
                $this->groups[$item->groupCode] = $group;
            }

            if ($item->scope <> "A") {
                $group = new ROrganisationGroup($item);
                $this->groups[$item->groupCode] = $group;

                $areacode = substr($item->groupCode, 0, 2);
                if (isset($this->areas[$areacode])) {
                    $area = $this->areas[$areacode];
                    $area->addGroup($group);
                }
            }
        }
        ksort($this->groups);
        ksort($this->areas);
        foreach ($this->areas as $area) {
            $area->sort();
        }
    }

    public function listAreas() {
        echo "<table>";
        echo RHtml::addTableHeader(["Area", "Group", "Hosted Web Site", "Status"]);
        foreach ($this->areas as $area) {
            $code = "";
            if ($this->showCodes) {
                $code = "Area " . $area->code . ": ";
            }

            echo RHtml::addTableRow([ $code . $area->getLink($this->showLinks), " ", $area->website, $area->status]);

            if ($this->showGroups) {
                $code = "";
                foreach ($area->groups as $group) {
                    if ($this->showCodes) {
                        switch ($group->scope) {
                            case "G":
                                $code = "Group " . $group->code . ":  ";
                                break;
                            case "S":
                                $code = "Special Group " . $group->code . ":  ";
                                break;
                            default:
                                $code = "Other Group " . $group->code . ":  ";
                                break;
                        }
                    }
                    //    Echo "<div class='ra_group'>" . $code . $group->getLink($this->showLinks) . "</div>";
                    echo RHtml::addTableRow([ "", $code . $group->getLink($this->showLinks), $group->website, $group->status]);
                }
            }
        }
        echo "</table>";
    }

    public function listAreasOLD() {
        foreach ($this->areas as $area) {
            $code = "";
            if ($this->showCodes) {
                $code = "Area " . $area->code . ": ";
            }
            echo "<div class='ra_area'>" . $code . $area->getLink($this->showLinks) . "</div>";
            echo "<ul>";
            if ($this->showGroups) {
                $code = "";
                foreach ($area->groups as $group) {
                    if ($this->showCodes) {
                        switch ($group->scope) {
                            case "G":
                                $code = "Group " . $group->code . ":  ";
                                break;
                            case "S":
                                $code = "Special Group " . $group->code . ":  ";
                                break;
                            default:
                                $code = "Other Group " . $group->code . ":  ";
                                break;
                        }
                    }
                    Echo "<div class='ra_group'>" . $code . $group->getLink($this->showLinks) . "</div>";
                }
                echo "</ul>";
            }
        }
    }

    public function addMapMarkers($map) {
        if (isset($map)) {
            $map->addBounds();
            $map->options->cluster = true;
            $text = "";
            foreach ($this->groups as $key => $group) {
                $areatext = "";
                if ($group->scope <> "A") {
                    $areacode = substr($key, 0, 2);
                    $area = $this->areas[$areacode];
                    $areatext = "Area <br/><a href='" . $area->url . "' target='_blank'>" . $area->name . "</a><br/>";
                }
                $marker = $group->addMapMarker($areatext);
                $text.=$marker . PHP_EOL;
            }
            $map->addContent($text);
        }
    }

    public function centreMap($centreGroup) {
        $this->centreGroup = strtoupper($centreGroup);
    }

    public function setZoom($zoom) {
        $this->mapZoom = $zoom;
    }

    private function checkJsonProperties($item) {
        $properties = array("scope", "groupCode", "name", "url", "description", "latitude",
            "longitude");

        foreach ($properties as $value) {
            if (!$this->checkJsonProperty($item, $value)) {
                return 1;
            }
        }

        return 0;
    }

    private function checkJsonProperty($item, $property) {
        if (property_exists($item, $property)) {
            return true;
        }
        return false;
    }

    private function CacheLocation() {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        return 'cache' . DS . 'ra_groups';
    }

}
