<?php

//
//     @version		2.0
//     @package		Simple Feed Reader 
//     @author                 Chris Vaughan
//     @copyright              Copyright (c) 2016 Chris Vaughan Derby & South Derbyshire Ramblers
//     @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
//
//
// no direct access
defined('_JEXEC') or die('Restricted access');

class RFeedhelper {

    private $cacheFolderPath;
    private $cacheTime;
    private $status = self::OK;

    const OK = 0;
    const READFAILED = 1;
    const FEEDERROR = 2;
    const FEEDFOPEN = 3;

    public function __construct($cacheLocation, $cacheTime) {
        $this->cacheTime = $cacheTime * 60; // convert to seconds
        $this->cacheFolderPath = JPATH_SITE . DS . $cacheLocation;
        if (isset($cacheLocation)) {
            $this->createCacheFolder();
        } else {
            die("Invalid call to RJsonwalksFeedhelper");
        }
    }

    public function setReadTimeout($value) {
        ini_set('default_socket_timeout', $value);
    }

    public function getFeed($feedurl) {

        $url = trim($feedurl);
        if ($this->startsWith($url, "http://www.ramblers.org.uk")) {
            $url = str_replace("http://", "https://", $url);
        }
        $contents = '';
        $this->status = self::OK;
        if (ini_get('allow_url_fopen') == false) {
            $this->status = self::FEEDFOPEN;
        }
        if (substr($url, 0, 4) != "http") {
            $this->status = self::FEEDERROR;
        }

        if ($this->status == self::OK) {
            $cachedFile = $this->createCachedFileFromUrl($url);
            if ($cachedFile != '') {
                $contents = file_get_contents($cachedFile);
                if ($contents === false) {
                    $contents = NULL;
                }
            }
        }
        $result = [];
        $result["status"] = $this->status;
        $result["contents"] = $contents;
        return $result;
    }

    // Get remote file
    private function createCachedFileFromUrl($url) {
        jimport('joomla.filesystem.file');
        $result = '';
        $tmpFile = $this->getCacheName($url);
        // Check if a cached copy exists otherwise create it
        if (file_exists($tmpFile) && is_readable($tmpFile) && (filemtime($tmpFile) + $this->cacheTime) > time()) {
            $this->status = self::OK;
            // echo "<br/>" . $url . "<br/>Existing cache used<br/>";
            return $tmpFile;
        }
        // create cached file
        // check url exists
        //     if ($this->urlExists($url)) {
        $options = [
            "http" => [
                "header" => "Accept-language: en\r\n" .
                "Referer: " . JURI::base() . "\r\n",]
        ];
        $context = stream_context_create($options);
        $fgcOutput = file_get_contents($url, false, $context);
        if ($fgcOutput === false) {
            $this->status = self::READFAILED;
        } else {
            JFile::write($tmpFile, $fgcOutput);
        }
        //    } else {
        //        $this->status = self::READFAILED;
        //    }
        // if cached file exists (new or old) then return it.
        if (file_exists($tmpFile) && is_readable($tmpFile)) {
            $result = $tmpFile;
        }
        return $result;
    }

//    private function urlExists($url) {
//        $exists = true;
//        $file_headers = @get_headers($url);
//        if ($file_headers == false) {
//            return false;
//        }
//        $InvalidHeaders = array('404', '403', '500');
//        foreach ($InvalidHeaders as $HeaderVal) {
//            if (strstr($file_headers[0], $HeaderVal)) {
//                $exists = false;
//                break;
//            }
//        }
//        return $exists;
//    }

    public function clearCache() {

        // Check if the cache folder exists
        if (file_exists($this->cacheFolderPath) && is_dir($this->cacheFolderPath)) {
            // clear files from folder
            $files = glob($this->cacheFolderPath . '/*'); // get all file names
            echo "<h2 class='feedrefresh'>Feed cache will be refreshed</h2>";
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file}
                }
            }
        }
    }

    private function getCacheName($feedname) {
        $url = strtolower(trim($feedname));
        if (substr($url, 0, 4) == "http") {
            $turl = explode("?", $url);
            $matchComponents = array("#(http|https)\:\/\/#s", "#www\.#s");
            $replaceComponents = array("", "");
            $turl = preg_replace($matchComponents, $replaceComponents, $turl[0]);
            $turl = str_replace(array("/", "-", "."), array("_", "_", "_"), $turl);
            $tmpFile = $this->cacheFolderPath . DS . urlencode($turl) . '.cache';
            /* $turl = explode("?", $url); CEV replacement code to handle walksfeed options */
            $turl = $url;
            $matchComponents = array("#(http|https)\:\/\/#s", "#www\.#s");
            $replaceComponents = array("", "");
            $turl = preg_replace($matchComponents, $replaceComponents, $turl);
            $turl = str_replace(array("/", "-", "."), array("_", "_", "_"), $turl);
            $turl = str_replace(array("?", "&", ",", "="), array("_", "", "", ""), $turl);
            $tmpFile = $this->cacheFolderPath . DS . urlencode($turl) . '.cache';
            /* end of change */
        } else {
            $tmpFile = $this->cacheFolderPath . DS . 'cached_' . md5($url);
        }
        return $tmpFile;
    }

    private function createCacheFolder() {
        // Check if the cache folder exists
        if (file_exists($this->cacheFolderPath) && is_dir($this->cacheFolderPath)) {
            // all OK
        } else {
            mkdir($this->cacheFolderPath);
        }
    }

    private function startsWith($string, $startString) {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

}
