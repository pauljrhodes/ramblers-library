<?php

/**
 * @version		0.0
 * @package		List user information, including forum subscriptions
 * @author              Chris Vaughan Ramblers-webs.org.uk
 * @copyright	       Copyright (c) 2014 Chris Vaughan. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class RUsersStatus {

    private $srfr;
    private $user;
    private $cbInfo;
    private $membership;
    private $membershipno = 0;
    private $membershipnoValidFormat = false;
    public $postcodeText = 'HAVE YOU MOVED? - Your Postcode held by this site does not agree with that sent to us by Ramblers London Office';
    public $localRecordsText = "Note: Our local Membership records are updated monthly and hence may be out of date.";
    public $ramblersmembership = "Note: To update your Ramblers membership information log on to the <a href='http://www.ramblers.org.uk/' target='_blank'>www.ramblers.org.uk</a> and access your Profile via My Account";

    function __construct() {
        $this->user = JFactory::getUser(); //gets user object
    }

    function sitename() { //gets sitename
        $config = new JConfig();
        return $config->sitename;
    }

    function loggedon() {
        If ($this->user != null) {
            return $this->user->id != 0;
        }
        return false;
    }

    function username() {
        If ($this->user != null) {
            return $this->user->name;
        }
        return "";
    }

    function useremail() {
        If ($this->user != null) {
            return $this->user->email;
        }
        return null;
    }

    function userlastvisitdate() {
        If ($this->user != null) {
            return $this->user->lastvisitDate;
        }
        return null;
    }

    function userregisterdate() {
        If ($this->user != null) {
            return $this->user->registerDate;
        }
        return null;
    }

    function display() {

        if ($this->loggedon() == false) {
            echo "<pre>Unknown, not logged on</pre>";
        } else {
            echo "<p>You are signed in as </p>";
            echo '<ul>';
            echo '<li>Name: ' . $this->username() . '</li>';
            echo '<li>Email: ' . $this->useremail() . '</li>';
            echo '<li>Your last vist to this site: ' . $this->userlastvisitdate() . '</li>';
            echo '<li>Registration date: ' . $this->userregisterdate() . '</li>';
            echo '</ul>';
            // index.php?option=com_comprofiler&task=userdetails
            echo '<a class="link-button button-p0110" href="index.php?option=com_comprofiler&task=userdetails">My Account</a>';
        }
    }

    function displayMembershipNumberStatus() {
        if ($this->loggedon()) {
            if ($this->membershipnoValidFormat == false) {
                $text = 'The format of your Membership number is not correct. It should be of the form <b><i>DE-02-0123456</b></i>';
                $text .=" - value given in Profile: " . strtoupper($this->cbInfo->cb_membershipno);
                JFactory::getApplication()->enqueueMessage($text);
            }
        }
    }

    private function decodeMembershipNumber($no) {
        $this->membershipnoValidFormat = false;
        $this->membershipno = $no;
        $pieces = explode("-", $no);
        If (count($pieces) == 3) {
            $this->membershipno = $pieces[2];
        }
        If (count($pieces) == 2) {
            $this->membershipno = $pieces[1];
        }
        If (count($pieces) == 1) {
            $this->membershipno = $pieces[0];
        }
        if (is_numeric($this->membershipno)) {
            $this->membershipnoValidFormat = true;
        }
    }

    function getMembershipInfoDELETE($id) {
        $this->decodeMembershipNumber($id);
        $ClearCache = NULL;
        $feedTimeout = 5;
        $CacheTime = 60; // minutes
        $cacheLocation = $this->CacheLocation();
        $rafeedurl = "https://members.theramblers.org.uk/index.php?id=" . $this->membershipno;

// Fetch content
        $srfr = new RFeedhelper($cacheLocation, $CacheTime);

        if (isset($ClearCache)) {
            $srfr->clearCache($cacheLocation); // clear cache
        }
        $contents = $srfr->getFeed($rafeedurl);
        $this->membership = NULL;

        switch ($contents) {
            case NULL:
                echo '<b>Membership feed: Unable to read feed: ' . $rafeedurl . '</b>';
                break;
            case "":
                // echo '<b>Membership feed: Member not found</b>';
                break;
            case "[]":
                // echo '<b>Membership feed empty: Member not found</b>';
                break;
            default:
                $json = json_decode($contents);
                unset($contents);
                $error = 0;
                if (json_last_error() == JSON_ERROR_NONE) {
                    $this->membership = $json;
                    if (isset($this->membership->error)) {
                        if ($this->membership->error == true) {
                            $this->membership = NULL;
                        }
                    }
                    unset($json);
                    break;
                } else {
                    echo '<br/><b>Membership feed: feed is not in Json format</b>';
                }
        }
    }
 function getMembershipInfo($id) {
        $this->decodeMembershipNumber($id);
        $ClearCache = NULL;
        $feedTimeout = 5;
        $CacheTime = 60; // minutes
        $cacheLocation = $this->CacheLocation();
        $rafeedurl = "https://members.theramblers.org.uk/index.php?id=" . $this->membershipno;

// Fetch content
        $this->srfr = new RFeedhelper($cacheLocation, $CacheTime);

        if (isset($ClearCache)) {
            $this->srfr->clearCache($cacheLocation); // clear cache
        }
        
        $this->membership = NULL;

        $this->readFeed($rafeedurl);
    }

    private function readFeed($rafeedurl) {

        $result = $this->srfr->getFeed($rafeedurl);
        $status = $result["status"];
        $contents = $result["contents"];

        switch ($status) {
            case RFeedhelper::OK:
                break;
            case RFeedhelper::READFAILED:
                $application = JFactory::getApplication();
                $application->enqueueMessage(JText::_('Unable to fetch membership data, data may be out of date: ' . $rafeedurl), 'warning');
                break;
            case RFeedhelper::FEEDERROR;
                $application = JFactory::getApplication();
                $application->enqueueMessage(JText::_('Feed must use HTTP protocol: ' . $rafeedurl), 'error');
                break;
            case RFeedhelper::FEEDFOPEN:
                $application = JFactory::getApplication();
                $application->enqueueMessage(JText::_('Not able to read feed using fopen: ' . $rafeedurl), 'error');
                break;
            default:
                break;
        }
        switch ($contents) {
            case NULL:
                $application = JFactory::getApplication();
                $application->enqueueMessage(JText::_('Membership feed: Unable to read feed: ' . $rafeedurl), 'error');
                break;
            case "":
                echo '<b>Membership feed: Member not found</b>';
                break;
            case "[]":
                echo '<b>Membership feed empty: Member not found</b>';
                break;
            default:
                $json = json_decode($contents);
                unset($contents);
                $error = 0;
                if (json_last_error() == JSON_ERROR_NONE) {
                    $this->membership = $json;
                    if (isset($this->membership->error)) {
                        if ($this->membership->error == true) {
                            $this->membership = NULL;
                        }
                    }
                    unset($json);
                    break;
                } else {
                    $application = JFactory::getApplication();
                    $application->enqueueMessage(JText::_('Membership feed: feed is not in Json format: ' . $rafeedurl), 'error');
                }
        }
    }

    private function CacheLocation() {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        return 'cache' . DS . 'ra_members';
    }

    function displayKunenaSubscriptions() {
        if ($this->loggedon()) {
            $kunena = new RUsersKunena();
            $kunena->displaySubscriptions($this->user->id);
        }
    }

    function checkMembership() {
        if ($this->loggedon()) {
            if (RSqlUtils::tableExists('#__comprofiler')) {
                // echo '<p>Community Builder installed </p>';
                $db = JFactory::getDbo();
                $id = $this->user->id;
                $query = $db->getQuery(true);

                $query->select($db->quoteName(array('cb_membershipno', 'cb_postcode', 'lastname')));
                $query->from($db->quoteName('#__comprofiler'));
                $query->where($db->quoteName('id') . ' = ' . $id);
// Reset the query using our newly populated query object.
                $db->setQuery($query);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
                $results = $db->loadObjectList();
                $this->cbInfo = NULL;
                foreach ($results as $i => $item) :
                    $this->getMembershipInfo($item->cb_membershipno);
                    $this->cbInfo = $item;
                endforeach;
            }
        }
    }

    function postcodeOK() {
        if ($this->cbInfo == NULL) {
            return true;
        }
        if ($this->membership == NULL) {
            return true;
        }
        $email1 = strtoupper($this->cbInfo->cb_postcode);
        $email1 = str_replace(' ', '', $email1);
        $email2 = strtoupper($this->membership->postcode);
        $email2 = str_replace(' ', '', $email2);
        if ($email1 == $email2) {
            return true;
        }
        return false;
    }

    function displayPostcodeStatus() {
        if ($this->loggedon()) {
            if ($this->postcodeOK() == false) {
                $text = $this->postcodeText;
                $text .=" - Postcodes: " . strtoupper($this->cbInfo->cb_postcode) . " and " . strtoupper($this->membership->postcode);
                $text.= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $this->localRecordsText;
                $text.= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $this->ramblersmembership;
                JFactory::getApplication()->enqueueMessage($text);
            }
        }
    }

    function displayEmailStatus() {
        if ($this->loggedon()) {
            if ($this->emailOK() == false) {
                $text = "INFORMATION: You are logged on to this site with a different email address than held by Ramblers Central Office";
                $text.= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $this->ramblersmembership;
                JFactory::getApplication()->enqueueMessage($text);
            }
        }
    }

    function emailOK() {
        if ($this->cbInfo == NULL) {
            return true;
        }
        if ($this->membership == NULL) {
            return true;
        }
        if (md5($this->user->email) == $this->membership->privateemail) {
            return true;
        }
        if (md5($this->user->email) == $this->membership->publicemail) {
            return true;
        }
        return false;
    }

    function membershipOK() {
        if ($this->cbInfo == NULL) {
            return true;
        }
        if ($this->membership == NULL) {
            return true;
        }
        if (strtoupper($this->membership->status == "LIFE")) {
            return true;
        }
        $val = $this->membership->expiry;
        if ($val == "") {
            return false;
        }
        if (trim($val) != "") {
            $expiry = DateTime::createFromFormat('Y-m-d H:i:s', $val . ' 00:00:00');
            $date = new DateTime();
            $date->add(new DateInterval('P30D'));
            if ($date < $expiry) {
                return true;
            }
        }

        return false;
    }

    function displayMembershipStatus() {
        if ($this->cbInfo == NULL) {
            return;
        }
        if ($this->membership == NULL) {
            Echo "<div class='ra-membership'><b>Ramblers Membership record not found within our Area/Group</b></div>";
            return;
        }
        $val = $this->membership->expiry;
        if ($val == "") {
            return;
        }
        Echo "<div class='ra-membership'><b>Ramblers Membership</b></div>";
        Echo "<div class='ra-membershipstatus'><b>Status: </b>" . $this->membership->status . "</div>";
        if (strtoupper($this->membership->status == "LIFE")) {
            return;
        }
        if (trim($val) != "") {
            $expiry = DateTime::createFromFormat('Y-m-d H:i:s', $val . ' 00:00:00');
            //   $date = new DateTime();
            //   $date->add(new DateInterval('P30D'));
            //   if ($date < $expiry) {
            Echo "<div class='ra-membershipexpires'><b>Expires: </b>" . $expiry->format('jS F Y') . "</div>";
            //  }
        }
        Echo "<div class='ra-membershipnote'>" . $this->localRecordsText . "</div>";

        return;
    }

    function lastnameOK() {
        if ($this->cbInfo == NULL) {
            return true;
        }
        if ($this->membership == NULL) {
            return true;
        }

        if (md5($this->cbInfo->lastname) == $this->membership->surname) {
            return true;
        }
        return false;
    }

    function displayLastnameStatus() {
        if ($this->loggedon()) {
            if ($this->lastnameOK() == false) {
                $text = "INFORMATION: You are logged on to this site with a different LASTNAME than held by Ramblers Central Office";
                $text.= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $this->ramblersmembership;
                JFactory::getApplication()->enqueueMessage($text);
            }
        }
    }

}

?>