<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan: modified by Tony Parsons
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksAv01Programmeww extends RJsonwalksDisplaybase {

    private $lastValue = "";
    private $walksClass = "de02walks";
    private $walkClass = "de02walk";
    public $addDescription = true;
    private $names = [];

    const BR = "<br />";

    function DisplayWalks($walks) {
//+++        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_DISTANCE, NULL);
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        echo "<div class='" . $this->walksClass . "' >" . PHP_EOL;
        echo "<table>" . PHP_EOL;
        $bst = "y";

//$file = fopen("https://bristolramblers.org.uk/index.php?option=com_content&view=article&id=127", "r") or die("Unable to open file!");
//while (!feof($file)){   
//    $data = fgets($file);
//  echo " xx ".$data." "."<br />";
//}
        $articleId = 127; // Your article id here
        $table_plan = JTable::getInstance('Content', 'JTable');
        $table_plan_return = $table_plan->load(array('id' => $articleId));
        $uuu = $table_plan->introtext;
//      echo "uuu ".$uuu;
        $nn = 0;
        for ($i = 0; $i < 1000; $i++) {
            if (substr($uuu, $i, 6) == "<p>/20") {
                $line = substr($uuu, $i + 4, 8);
                $linex[$nn] = $line;
                echo $nn . $linex[$nn] . "<br />";
                $nn = $nn + 1;
            }

            $uuu1 = ord(substr($uuu, $i, 1));
            if ($uuu1 == 0)
                break;
//        echo $i." ".$uuu1." ".substr($uuu,$i,1)."<br />";
        }

        $string = "test";
//#      $myfile = fopen("https://bristolramblers.org.uk/images/Other/tauntonleisuredates.txt", "r") or die("Unable to open file!");
//$myfile = fopen("https://bristolramblers.org.uk/index.php?option=com_content&view=article&id=127", "r") or die("Unable to open file!");
//#$nn=0;
//#      foreach(file('https://bristolramblers.org.uk/images/Other/tauntonleisuredates.txt') as $line) {
//      foreach(file('https://bristolramblers.org.uk/index.php?option=com_content&view=article&id=127') as $line) {
//#$linex[$nn]=$line;        
//echo $line. "<br />";
//        echo $nn." ".$linex[$nn]."<br />";
//#        $nn=$nn+1;
//#      }
        $linex[$nn] = "2037-01-01";
        $ind = "1";
        foreach ($items as $walk) {
            if ($ind == "1") {
                $ind = 0;
                $datestring = $walk->walkDate->format('Y-m-d');
                $tw = strtotime($datestring);
                $nxx = 0;
                $datetle = strtotime($linex[$nxx]);
//      echo $tw."<br />".$datetle;
                if ($tw > $datetle) {
                    $nxx = $nxx + 1;
                }
                $datetle = strtotime($linex[$nxx]);
                if ($tw > $datetle) {
                    $nxx = $nxx + 1;
                }
                $datetle = strtotime($linex[$nxx]);
                if ($tw > $datetle) {
                    $nxx = $nxx + 1;
                }
                $datetle = strtotime($linex[$nxx]);
                if ($tw > $datetle) {
                    $nxx = $nxx + 1;
                }


                if ($nxx == $nn) {
                    echo "<br />" . "<b>Update Taunton Leisure Shopping Evening Dates</b>" . "<br />" . "<br />" . "<br />";
                }

//         echo $nxx;
//      echo $walk->walkDate->format('Y-m-d');
            }
            $thismonth = $walk->walkDate->format('F');
            $thismonth3 = $walk->walkDate->format('M');
//            if ($thismonth <> $this->lastValue) {
//                if ($this->lastValue <> "") {
//                    echo "</table>" . PHP_EOL;
//                }
//                $this->lastValue = $thismonth;
//               echo "<h2>" . $thismonth . "</h2>" . PHP_EOL;
//            }
//echo "ccc".$bst;
//m
//c        $bst=$this->displayWalk($walk,$thismonth3,$bst,$nn,$nxx,$linex);
            $this->displayWalk($walk, $thismonth3, $bst, $nn, $nxx, $linex);
        }
        echo "</table>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    }

    function setWalksClass($class) {
        $this->walksClass = $class;
    }

    function setWalkClass($class) {
        $this->walkClass = $class;
    }

    function setNames($names) {
        $this->names = $names;
    }

    function getFullname($name) {
        if (array_key_exists($name, $this->names)) {
            return $this->names[$name];
        }
        return $name;
    }

//    private function &displayWalk($walk,$thismonth3,$bst):string {
    private function displayWalk($walk, $thismonth3, $bst, &$nn, &$nxx, &$linex) {
        ///       $col1 = ""
//echo "nn=".$nn;
//echo "nxx=".$nxx;
//echo  "linex=".$linex[$nxx];     
        $string = $walk->title;
        $string2 = $walk->additionalNotes;
        $string3 = $walk->description;
//       $z=$walk->walkDate->format('d');
//      $zz=$walk->walkDate->format('M'); 
//echo $z.$zz;    
//echo "%".$string."<br />";
//echo "%%".$string2."<br />";
//echo "%%%".$string3."<br />";
//      $zzz=$walk->groupCode; 
//echo $zzz;
//=================================================
        $theme = "no";
        $stringx = "";
        $string1 = strstr($string3, "hemed");
//echo "uuu /".$string1."/";
        if (!($string1 == "")) {
            $theme = "yes";
            $stringx = "t";
        }
        $string1 = strstr($string, "hemed");
        if (!($string1 == "")) {
            $theme = "yes";
            $stringx = "t";
        }
//echo "theme /".$theme."/";
        if ($theme == "yes") {
            $walk->title = $walk->title . " [T]";
//echo "xxx".$walk->title;
        }
//=================================================      
        $www = "no";
//$stringx="";
        $string1 = strstr($string3, "essex Wanderer");
//echo "uuu /".$string1."/";
        if (!($string1 == "")) {
            $www = "yes";
            $stringx = "w";
        }
        $string1 = strstr($string, "essex Wanderer");
        if (!($string1 == "")) {
            $www = "yes";
            $stringx = "w";
        }
//echo "www /".$www."/";
        if ($www == "yes") {
            $walk->title = $walk->title . " [WW]";
//echo "xxx".$walk->title;
        }
        if ($walk->groupName == "Bristol") {
            $stringx = "B";
        }
        if (!($stringx == "")) {
            $datestring = $walk->walkDate->format('Y-m-d');
//echo "datestring ".$datestring;
            $tw = strtotime($datestring);
            $twx = substr($linex[$nxx], 6, 2);
//echo $twx." ".$nxx." ".$linex[$nxx]. "</br>";
            $twy = substr($linex[$nxx], 4, 2);
//  echo $twy;
            $monthName = substr(date("F", mktime(0, 0, 0, $twy, 10)), 0, 3);
// echo $monthName;
            $datetle = strtotime($linex[$nxx]);
//      echo $tw."<br />".$datetle;
            if ($tw > $datetle) {
                $col1 = " ";
                $col2 = " ";
                echo RHtml::addTableRow(array($col1, $col2));



                $col1 = "<b>" . $twx . " " . $monthName . "</b>";
                $col2 = "<b>" . '<div align="center"> Taunton Leisure Shopping </div>' . "</b>";
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = "Thurs";
                $col2 = "<b>" . '<div align="center"> Evening</div>' . "</b>";
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = "17:00-";
                $col2 = " ";
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = "19:00";
                $col2 = '<div align="center"> 20% DISCOUNT WITH  </div>';
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = " ";
                $col2 = '<div align="center"> CURRENT</div>';
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = " ";
                $col2 = '<div align="center"> RAMBLERS CARD</div>';
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = " ";
                $col2 = '<div align="center"> 0117 963 7640</div>';
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = "£ ";
                $col2 = " ";
                echo RHtml::addTableRow(array($col1, $col2));
//echo $col1."#".$col2;
                $col1 = "&pound;&pound;&pound;";
                $col2 = " ";
                echo RHtml::addTableRow(array($col1, $col2));
                $nxx = $nxx + 1;
            }
            $col1 = " ";
            if ($walk->hasMeetPlace) {
                if ($walk->meetLocation->description == "Meet at Clifton Water Tower for lift sharing") {
                    $col2 = "<b>" . $walk->meetLocation->description . "</b>";
                    echo RHtml::addTableRow(array($col1, $col2));
                }
            }
            //echo $bst;
            if ($bst == "y") {
                if ($walk->walkDate->format('D') == "Sun") {
                    if ($walk->walkDate->format('d') > 24.5) {
                        if ($walk->walkDate->format('m') == "03") {
                            $bst = "n";
                            $col2 = '<b><span style="color: red";> British Summer Time Begins Times are BST</span></b>';
                            echo RHtml::addTableRow(array($col1, $col2));
                            $bst = "n";
                        }
                        if ($walk->walkDate->format('m') == "10") {
                            $bst = "n";
                            $col2 = '<b><span style="color: red";> British Summer Time Ends Times are GMT</span></b>';
                            echo RHtml::addTableRow(array($col1, $col2));
                        }
                    }
                }
            }

            $col1 = "<b>" . " " . $walk->walkDate->format('d') . " " . $thismonth3 . "</b>";
            $col2 = "<b>" . $walk->title . "</b>";
//        if (!($walk->localGrade =="")) {$col2 .= " <b>"  . $walk->localGrade . "</b> ";}
///$col2 .="<br />" ;       
            echo RHtml::addTableRow(array($col1, $col2));
            $col1 = $walk->walkDate->format('D');
            $col2 = $walk->distanceMiles . "miles";
            $kmr = $walk->distanceKm;
            $km = floor($kmr + 0.5);
            $col2.= " " . $km . "km ";
// check for map info
//        if ($this->addDescription) {
//echo "wd ".$walk->description."<br />";
            $string2 = $walk->description;
            $string1 = "";
            $stringz = substr($walk->description, 0, 1);
//echo $stringz."<br />";
            if ($stringz == "[") {
                $string1 = strtok($walk->description, "]");
                $string2 = strtok("]");
                $string1 = substr($string1, 1);
//echo "s1".$string1."<br />";
//echo "s2".$string2."<br />";
            }
            if (!($string1 == "")) {
                $col2.=" " . $string1;
            }
            $walk->description = $string2;

//echo "wd2 /".$walk->description."/<br />";
//echo "/".$col1."/".$col2."/<br />";
            echo RHtml::addTableRow(array($col1, $col2));
            $col1 = " ";
            if ($walk->hasMeetPlace) {

                $col2 .= " <br />";
//z            $col1=$walk->meetLocation->time->format('H').":".$walk->meetLocation->time->format('i'); 
//$col1=substr($walk->meetLocation->timeHHMM,0,5) ; 
                $col1 = $walk->meetLocation->timeHHMM;
//          echo strlen($col1);
//          echo substr($col1,4,1);
                If (strlen($col1) < 7) {
                    $col1 = "0" . $col1;
                }
                if (substr($col1, 5, 1) == "p") {
                    $aaa = substr($col1, 0, 2) + 12;
                    $bbb = substr($col1, 2, 5);
                    $col1 = $aaa . $bbb;
                }
                $col1 = substr($col1, 0, 5);
//          $col1=date('H',$walk->meetLocation->time).date('i',$walk->meetLocation->time);
                $col2 = " ";
                if (!($walk->meetLocation->description == "Meet at Great George Street for lift sharing.")) {
                    if (!($walk->meetLocation->description == "Meet Great George Street for lift sharing.")) {
                        if (!($walk->meetLocation->description == "Meet at Great George Street for lift sharing")) {
                            if (!($walk->meetLocation->description == "Meet at Great George Street BS1 for lift sharing.")) {
                                if (!($walk->meetLocation->description == "Meet at Great George Street BS1 for lift sharing")) {
                                    if (!($walk->meetLocation->description == "Meet at Clifton Water Tower for lift sharing")) {
                                        if (!($walk->meetLocation->description == "Meet at Water Tower, Durdham Down for lift sharing")) {
                                            if (!(substr($walk->meetLocation->description, 0, 19) == "Meet at Priory Road")) {
                                                $col2 = $walk->meetLocation->description;
                                                echo RHtml::addTableRow(array($col1, $col2));
                                                $col1 = " ";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($walk->startLocation->exact) {
//        $col2 .="<br />";
                if (!($col1 == " ")) {
                    $col2 = " ";
                    echo RHtml::addTableRow(array($col1, $col2));
                    $col1 = " ";
                }
//***********************
//echo "qqq".$walk->walkDate->format('m');
                $col1 = $walk->startLocation->time->format('H') . ":" . $walk->startLocation->time->format('i');
//***********************
//                  $col1= $walk->startLocation->timeHHMMshort . " " . 
                $col2 = $walk->startLocation->description;
                $col2 .= " @" . $walk->startLocation->gridref;
//echo $col2;
                echo RHtml::addTableRow(array($col1, $col2));
                $col1 = " ";
            }

            $col4 = $walk->description;
//echo $col4." c4";
//echo $this->addDescription."tad";
            if ($this->addDescription) {
                if ("." == $walk->description) {
                    $walk->description = "-";
                }
                if (!("-" == $walk->description)) {
                    //        $col1=" ";
                    $col2 = $walk->description;
//$string1= strpos($col2, "essex Wanderer");
                    $string1 = strpos($col2, "WESSEX WANDERER RAILWAY WALKS");
//echo "s1".$string1."/";
                    if (!($string1 == "")) {
                        $col2 = substr($col2, 0, $string1) . ". See Wessex Wanderer Website for full details.";
                    }
                    if (!($col2 == "")) {
                        echo RHtml::addTableRow(array($col1, $col2));
                        $col1 = "";
                    }
                }
            }
//        $col3="";
//        if(!($walk->additionalNotes==""))
//        {$col3.= $walk->additionalNotes;}
//        if ($walk->isLeader) {
//            $col2.=", Leader " ;
//        } else {
//            $col2.=", Contact " ;
//        }
//         $col1=" ";
            $col2 = $walk->contactName;
////
            $fullname = $walk->contactName;
//echo "fn ".$fullname;
            $len = strlen($fullname);
            $len1 = $len - 1;
            $len3 = $len - 3;
            $clast = substr($fullname, $len1, 1);
            $cpenpen = substr($fullname, $len3, 2);
            if ($cpenpen == "  ") {
                $nx = substr($fullname, 0, -2);
                $fullname = $nx . $clast;
            }

            $col2 = $this->getFullname($fullname);
            if ($walk->telephone1 != "") {
                $t1 = preg_replace('/\s/', '~', $walk->telephone1);
                $t1 = preg_replace('/\s/', '&nbsp', $walk->telephone1);
//             echo $t1;
                $col2.= " " . $t1;
            }

            if ($walk->telephone2 != "") {
                $t2 = preg_replace('/\s/', '&nbsp', $walk->telephone2);
//             echo $t2;
                $col2.= " / " . $t2;
            }
//$col2 .=$walk->status;
            echo RHtml::addTableRow(array($col1, $col2));
            $col1 = "&pound;&pound;&pound;";
            $col2 = " ";
//$col2=$walk->additionalNotes; 
            echo RHtml::addTableRow(array($col1, $col2));
            $xs = substr($walk->additionalNotes, 3, 1);
//             echo $xs."<br />";
            $col1 = "";
            $col2 = "<div class='" . $this->walkClass . $walk->status . "'>" . $col2 . "</div>";
            $col2 .="<br />";
//
//  
//echo $col2;
//import json;
//with open('data.txt', 'w') as outfile:
//    json.dump($col2, outfile);
//        echo RHtml::addTableRow(array($col2));
//        echo RHtml::addTableRow(array($col1, $col2));
        }

        return $bst;
    }

}

//$string = "Hello world! Beautiful day today.";
//$string1= strtok($string, "|");
//echo $string1."<br />";
//$string2 = strtok("|");
//echo "/".$string2."/";
// if string does not contain | then $string2=""

//        if ($walk->isLeader) {
//            $col2.=", Leader " ;
//        } else {
//            $col2.=", Contact " ;
//        }
