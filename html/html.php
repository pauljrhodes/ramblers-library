<?php

class RHtml {

    static function convertToText($html) {
        $text = str_replace("\r", "", $html);
        $text = str_replace("\n", "", $text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = strip_tags($text);
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        $text = trim($text);
        return $text;
    }

    static function addTableHeader($cols) {
        if (is_array($cols)) {
            $out = "<tr>";
            foreach ($cols as $value) {
                $out.="<th>" . $value . "</th>";
            }
            $out.="</tr>" . PHP_EOL;
            return $out;
        } else {
            return "<tr><td>invalid argument in html::addTableRows</td></tr>";
        }
    }

    static function addTableRow($cols, $class = "") {
        if (is_array($cols)) {
            if ($class == "") {
                $out = "<tr>";
            } else {
                $out = "<tr class='" . $class . "'>";
            }

            foreach ($cols as $value) {
                $out.="<td>" . $value . "</td>";
            }
            $out.="</tr>" . PHP_EOL;
            return $out;
        } else {
            return "<tr><td>invalid argument in html::addTableRows</td></tr>";
        }
    }

    static function withDiv($class, $text, $printOn) {
        $out = "";
        if ($printOn) {
            $out.="&nbsp;&nbsp;&nbsp;" . $text;
        } else {
            $out.= "<div class='" . $class . "'>";
            $out.=$text;
            $out.= "</div>";
        }
        return $out;
    }

    static function displayTitle($title, $display) {
        If ($display == "Yes") {
            echo "<h2>" . $title . "</h2>";
        }
    }

}
