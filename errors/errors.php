<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of errors
 *
 * @author Chris Vaughan
 */
class RErrors {

    private static $ERROR_STORE_URL = "https://cache.ramblers-webs.org.uk/store_errors.php";

    public static function notifyError($errorText, $action, $level) {

        $url = self::$ERROR_STORE_URL;

        $data = [];
        $data['domain'] = JURI::base();
        $data['action'] = $action;
        $data['error'] = $errorText;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $json_response = curl_exec($curl);

        // var_dump($json_response);
        // var_dump(curl_getinfo($curl));

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200) {
            $reported = " [SYSTEM was unable to report error.]";
        } else {
            $reported = " ...";
        }

        curl_close($curl);
        $app = JFactory::getApplication();
        $app->enqueueMessage(JText::_($errorText . ": " . $action . $reported), $level);
    }

    public static function emailError($errorText, $action, $level) {
        $domain = JURI::base();
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );

        $mailer->setSender($sender);
        $recipient = array('feeds@ramblers-webs.org.uk');
        $mailer->addRecipient($recipient);

        $mailer->setSubject('Walks feed error');
        $body = '<h2>' . $domain . '</h2>'
                . '<h3>' . $action . '</h3>'
                . '<p>ERROR: ' . $errorText . '</p>'
                . '<p>Severity: ' . $level . '</p>';
        $mailer->isHtml(true);
        $mailer->setBody($body);
        $send = $mailer->Send();
    }

    public static function checkJsonFeed($feed, $feedTitle, $result, $properties) {
        $status = $result["status"];
        $contents = $result["contents"];

        switch ($status) {
            case RFeedhelper::OK:
                break;
            case RFeedhelper::READFAILED:
                RErrors::notifyError('Unable to fetch ' . $feedTitle . ', data may be out of date', $feed, 'warning');
                break;
            case RFeedhelper::FEEDERROR;
                RErrors::notifyError('Feed must use HTTP protocol', $feed, 'error');
                break;
            case RFeedhelper::FEEDFOPEN:
                RErrors::notifyError('Not able to read feed using fopen', $feed, 'error');
                break;
            default:
                break;
        }
        switch ($contents) {
            case NULL:
                RErrors::notifyError($feedTitle . ' feed: Unable to read feed (Null response)', $feed, 'error');
                break;
            case "":
                echo '<b>' . $feedTitle . ' feed: No ' . $feedTitle . ' found</b>';
                break;
            case "[]":
                echo '<b>' . $feedTitle . ' feed empty: No ' . $feedTitle . ' found</b>';
                break;
            default:
                $json = json_decode($contents);
                unset($contents);
                $errors = 0;
                $error = json_last_error();
                if ($error == JSON_ERROR_NONE) {
                    foreach ($json as $value) {
                        $ok = RErrors::checkJsonProperties($value, $properties);
                        $errors+=$ok;
                    }
                    if ($errors > 0) {
                        RErrors::notifyError('Feed: Json file contents not as expected - not supported', $feed, 'error');
                        RErrors::emailError('Feed: Json file contents not as expected - not supported', $feed, 'error');
                    }
                    return $json;
                    break;
                } else {
                   RErrors::notifyError('Feed is not in Json format: code ' . $error, $feed, 'error');
                }
                return null;
        }
    }

    private static function checkJsonProperties($item, $properties) {
        foreach ($properties as $value) {
            if (!RErrors::checkJsonProperty($item, $value)) {
                return 1;
            }
        }

        return 0;
    }

    private static function checkJsonProperty($item, $property) {
        if (property_exists($item, $property)) {
            return true;
        }
        return false;
    }
}