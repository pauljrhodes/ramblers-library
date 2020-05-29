<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of watchful
 *
 * @author Chris Vaughan
 */
class RWatchfulWatchful {

    private $watchful_url = "https://app.watchful.li/api/v1/";
    Private $currentDate;
    private $domains;

    public function __construct() {
        $this->currentDate = new DateTime();
        require_once 'watchfulapi.php';
        $this->retrieveInfo();
    }

    private function retrieveInfo() {
        $sites = $this->getSites();
        $this->domains = [];
        foreach ($sites as $site) {
            $dom = $this->getDomainFromURL($site->access_url);
            if (!array_key_exists($dom, $this->domains)) {
                $this->domains[$dom] = new RWatchfulDomain($dom);
            }
            $this->domains[$dom]->addSite($site);
        }
        ksort($this->domains);
    }

    public function getDomains() {
        return $this->domains;
    }

    private function getDomainFromURL($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return FALSE;
    }

    private function getSites() {
        $options = [];
        return $this->getItems('sites', $options);
    }

    private function getItems($type, $data) {
        $total = 200;
        $items = [];
        do {
            $opts = array(
                'http' => array(
                    'method' => "GET",
                    'header' => "Accept: application/json\r\n" .
                    "Api-Key: " . WATCHFUL_API_KEY . "\r\n"
                )
            );

            $context = stream_context_create($opts);
            $url = $this->watchful_url . $type . '?limit=100&limitstart=' . count($items);
            $file = file_get_contents($url, false, $context);
            if ($file === false) {
                echo 'Error: Reading Watchfull data';
                break;
            }
            $result = json_decode($file);
            $total = $result->msg->total;
            // if ($total>20 ){   $total=20;}
            $newitems = $result->msg->data;
            foreach ($newitems as $item) {
                $items[] = $item;
            }
        } while (count($items) < $total);
        return $items;
    }

    public function getSitesList($domainname, $noJoomla) {
        $out = '';
        $no = 0;
        foreach ($this->domains as $domain) {
            if ($domainname === $domain->getName()) {
                foreach ($domain->getSites() as $site) {
                    $out .= '<div>' . $site->access_url . ' v' . $site->j_version . $this->displayTags($site) . '<div>';
                    $no +=1;
                }
            }
        }
        if ($noJoomla > 0) {
            if ($no !== $noJoomla) {
                $out .= "<div style='color:red'>Mismatch in number of Joomla sites</div>";
            }
        }
        return $out;
    }

    public function displayTags($site) {
        $out = '';
        if (isset($site->tags)) {
            if (count($site->tags) > 0) {
                $out.=" Tags:  ";
                foreach ($site->tags as $tag) {
                    $out.= $tag->name . " ";
                }
            }
        }
        return $out;
    }

}
