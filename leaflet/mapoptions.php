<?php

/**
 * Description of mapoptions
 *
 * @author Chris Vaughan
 */
class RLeafletMapoptions {

    // Map options

    public $divId = "";
    public $base = "";
    public $mapHeight = "500px";
    public $mapWidth = "100%";
    public $licenseKeys;
    public $helpPage = "";
    // the following can be true of false
    public $cluster = false;
    public $fitbounds = false;
    public $resizer = true;
    public $controlcontainer = false; // used by Walks Editor
    public $displayElevation = false;
    public $calendar = false;
    public $topoMapDefault = false;
    public $initialview = null;
    public $copyright = true;
    // always on
    public $fullscreen = true;
    public $settings = true;
    public $mylocation = true;
    public $print = true;
    //// ************** these options have three values
    // null never display option
    // true always display option
    // false only display option on full screen
    public $mouseposition = false;
    public $rightclick = false;

    // ************** END these options have three values

    public function __construct() {
        $this->divId = uniqid(rand());
        $this->base = JURI::base();
        $this->licenseKeys = new stdClass();
        $this->licenseKeys->ORSkey = null;
        $this->licenseKeys->ESRIkey = null;
        $this->licenseKeys->OSTestStyle = null;
        $this->licenseKeys->OSMVectorStyle = null;
        $this->licenseKeys->mapBoxkey = null;
        $this->licenseKeys->thunderForestkey = null;
    }

    public function setinitialviewView($latitude, $longitude, $zoom) {
        $this->initialview = (object) [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'zoom' => $zoom
        ];
    }

    public function setLicenses() {

        $this->licenseKeys->ESRIkey = RLicense::getESRILicenseKey();

        if (RLicense::isOpenRoutingServiceKeySet()) {
            $this->licenseKeys->ORSkey = RLicense::getOpenRoutingServiceKey();
        }

        $this->licenseKeys->OSkey = RLicense::getOrdnanceSurveyLicenseKey();
        $this->licenseKeys->OSTestkey = RLicense::getOrdnanceSurveyLicenseTestKey();
        $this->licenseKeys->OSTestStyle = RLicense::getOrdnanceSurveyLicenseKeyTestStyle();
        $this->licenseKeys->OSMVectorStyle = RLicense::getOSMVectoricenseKey();
        $this->licenseKeys->mapBoxkey = RLicense::getMapBoxLicenseKey();
        $this->licenseKeys->thunderForestkey = RLicense::getThunderForestLicenseKey();
        $this->licenseKeys->W3Wkey = RLicense::getW3WLicenseKey();
    }
}
