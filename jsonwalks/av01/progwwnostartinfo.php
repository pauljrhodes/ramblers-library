<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan: modified by Tony Parsons
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksAv01Progwwnostartinfo extends RJsonwalksDisplaybase {

    private $lastValue = "";
    private $walksClass = "de02walks";
    private $walkClass = "de02walk";
    public $addDescription = true;

    const BR = "<br />";

    function DisplayWalks($walks) {
//+++        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_DISTANCE, NULL);
 $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);        
$items = $walks->allWalks();
        echo "<div class='" . $this->walksClass . "' >" . PHP_EOL;
                echo "<table>" . PHP_EOL;
        foreach ($items as $walk) {
            $thismonth = $walk->walkDate->format('F');
            $thismonth3 = $walk->walkDate->format('M');
//            if ($thismonth <> $this->lastValue) {
//                if ($this->lastValue <> "") {
//                    echo "</table>" . PHP_EOL;
//                }
//                $this->lastValue = $thismonth;
//               echo "<h2>" . $thismonth . "</h2>" . PHP_EOL;
//            }

            $this->displayWalk($walk,$thismonth3);
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

    private function displayWalk($walk,$thismonth3) {
 ///       $col1 = "";
$string = $walk->title;
$string2=$walk->additionalNotes;
$string3=$walk->description;
//echo "%".$string."<br />";
//echo "%%".$string2."<br />";
//echo "%%%".$string3."<br />";
$www="no";
$stringx="";
$string1= strstr($string3, "essex Wanderer");
//echo "uuu /".$string1."/";
if (!($string1=="")){
$www="yes";
$stringx="w";
$walk->description=str_replace("Wessex Wanderer Railway Walk","",$walk->description);
$walk->description=str_replace("Wessex Wanderer Walk","",$walk->description);
$walk->description=str_replace("Wessex Wanderer","",$walk->description);
}
$string1= strstr($string, "essex Wanderer");
if (!($string1=="")){
$www="yes";
$stringx="w";
$walk->title=str_replace("Wessex Wanderer Railway Walk","",$walk->title);
$walk->title=str_replace("Wessex Wanderer Walk","",$walk->title);
$walk->title=str_replace("Wessex Wanderer","",$walk->title);
}
//echo "www /".$www."/";
if($www=="yes"){
$walk->title=$walk->title." [WW]";
//echo "xxx".$walk->title;
}
if ($walk->groupName=="Bristol"){$stringx="B";}
if (!($stringx=="")){
        $col1 = "<b>" . " ".$walk->walkDate->format('d') ." ". $thismonth3 ."</b>";
        $col2 = "<b>" . $walk->title . "</b>";
//        if (!($walk->localGrade =="")) {$col2 .= " <b>"  . $walk->localGrade . "</b> ";}
///$col2 .="<br />" ;       
        echo RHtml::addTableRow(array($col1, $col2));
        $col1=$walk->walkDate->format('D');     
        $col2= $walk->distanceMiles . "miles"  ;
$kmr=$walk->distanceKm; 
$km=floor($kmr+0.5);
        $col2.= " " . $km . "km "  ;
// check for map info
//        if ($this->addDescription) {
//echo "wd ".$walk->description."<br />";
$string2=$walk->description;
$string1="";
$stringz= substr($walk->description,0,1);
//echo $stringz."<br />";
if ($stringz=="["){
$string1= strtok($walk->description, "]");
$string2 = strtok( "]");
$string1=substr($string1,1);
//echo "s1".$string1."<br />";
//echo "s2".$string2."<br />";
}
if (!($string1=="")) {
$col2.=" ".$string1;
}
$walk->description=$string2;

//echo "wd2 /".$walk->description."/<br />";
//echo "/".$col1."/".$col2."/<br />";
         echo RHtml::addTableRow(array($col1, $col2));
        $col1=" ";
        if ($walk->hasMeetPlace) {
//           $col2 .= " <br />"; 
            $col1= $walk->meetLocation->time->format('H').":".$walk->meetLocation->time->format('i'); 
            $col2=" ";
            if(!($walk->meetLocation->description=="Meet at Great George Street for lift sharing.")){
            if(!($walk->meetLocation->description=="Meet Great George Street for lift sharing.")){
            if(!($walk->meetLocation->description=="Meet at Great George Street BS1 for lift sharing.")){
            if(!($walk->meetLocation->description=="Meet at Great George Street BS1 for lift sharing")){
            $col2= $walk->meetLocation->description;
        echo RHtml::addTableRow(array($col1, $col2));
            $col1=" ";
}
}
}
}
        }
 $day= $walk->walkDate->format('D');  

      if ($walk->startLocation->exact) {
//        $col2 .="<br />";
      if(!($col1==" ")) {
      $col2=" ";
       echo RHtml::addTableRow(array($col1, $col2));
            $col1=" ";
        }
        $col1=$walk->startLocation->time->format('H').":".$walk->startLocation->time->format('i');
//          $col2= $walk->startLocation->timeHHMMshort . " " . 
          $col2 = $walk->startLocation->description;
            $col2 .= " @". $walk->startLocation->gridref;
   if($day=="Sat" OR $day=="Wed"){
   $col2="Contact Leader for details of start location";}

//echo $col2;
        echo RHtml::addTableRow(array($col1, $col2));
            $col1=" ";
         }

                     $col4=$walk->description;
//echo $col4." c4";
//echo $this->addDescription."tad";
         if ($this->addDescription) {
            if ("."==$walk->description) {$walk->description="-";}
            if (!("-"==$walk->description)) {
 //        $col1=" ";
                $col2=$walk->description;
$string1= strpos($col2, "essex Wanderer");
//echo "/".$string1."s1";
if(($string1=="1")){
$col2=" See Wessex Wanderer Website for full details.";
}
if(!($col2=="")){
       echo RHtml::addTableRow(array($col1, $col2));
$col1="";
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
         $col2= $walk->contactName ;
////
$fullname=$walk->contactName ;
//echo "fn ".$fullname;
$len=strlen($fullname);
$len1=$len-1;
$len3=$len-3;
$clast=substr($fullname,$len1,1);
$cpenpen=substr($fullname,$len3,2);
if($cpenpen=="  "){
$nx=substr($fullname,0,-2);
$fullname=$nx.$clast;
}
 if($fullname=="Keith B")
{$fullname="Keith Budd";}
if($fullname=="Keith  B")
{$fullname="Keith Budd";}
if($fullname=="Clive R")
{$fullname="Clive Richardson";}
if($fullname=="Gwyneth L")
{$fullname="Gwyneth Littlejohn";}
if($fullname=="Julian B")
{$fullname="Julian Butter";}
if($fullname=="Meryl T")
{$fullname="Meryl Till";}
if($fullname=="David P")
{$fullname="David Pingstone";}
if($fullname=="Wendy B")
{$fullname="Wendy Britton";}
if($fullname=="Tony C")
{$fullname="Tony Carter";}
if($fullname=="Margaret P")
{$fullname="Margaret Postlethwaite";}
if($fullname=="Gordon S")
{$fullname="Gordon Stillman";}
if($fullname=="Andy Se")
{$fullname="Andy Sears";}
if($fullname=="Martin S")
{$fullname="Martin Summerell";}
if($fullname=="Tony P")
{$fullname="Tony Parsons";}
if($fullname=="Paula C")
{$fullname="Paula Cannings";}
if($fullname=="Sue Y")
{$fullname="Sue Young";}
if($fullname=="Nigel  A")
{$fullname="Nigel Andrews";}
if($fullname=="Nigel A")
{$fullname="Nigel Andrews";}
if($fullname=="Julie B")
{$fullname="Julie Boston";}
if($fullname=="Maureen J")
{$fullname="Maureen Johnson";}
if($fullname=="Bob M")
{$fullname="Bob Mills";}
if($fullname=="Stephen D")
{$fullname="Stephen Draper";}
if($fullname=="Patsy H")
{$fullname="Patsy Hudson";}
//if($fullname=="Mary M")
//{$fullname="Mary Magro";}
if($fullname=="Geoff H")
{$fullname="Geoff Harris";}
if($fullname=="Susan C")
{$fullname="Susan Carter";}
if($fullname=="Neil B")
{$fullname="Neil Burlton";}
if($fullname=="John W")
{$fullname="John Wrigley";}
if($fullname=="Carew R")
{$fullname="Carew Reynell";}
if($fullname=="Mike F")
{$fullname="Mike Fox";}
if($fullname=="Dave O")
{$fullname="Dave Osborne";}
//if($fullname=="Margaret R")
//{$fullname="Margaret Ruse";}
if($fullname=="Peter G")
{$fullname="Peter Gould";}
if($fullname=="Gill D")
{$fullname="Gill Davies";}
if($fullname=="Ann L")
{$fullname="Ann Light";}
if($fullname=="Roger G")
{$fullname="Roger Griffiths";}
if($fullname=="Sarah M")
{$fullname="Sarah Marshall";}
if($fullname=="Derek B")
{$fullname="Derek Bones";}
if($fullname=="Ben M")
{$fullname="Ben MacKay";}
if($fullname=="Richard R")
{$fullname="Richard Redding";}
if($fullname=="Chris S")
{$fullname="Chris Sanders";}
if($fullname=="Chris H")
{$fullname="Chris Holloway";}
if($fullname=="Julie P")
{$fullname="Julie Parry";}
if($fullname=="Sarah S")
{$fullname="Sarah Shorter";}
//if($fullname=="Sarah  S")
//{$fullname="Sarah Shorter";}
if($fullname=="Andy St")
{$fullname="Andy Steward";}
if($fullname=="Susan J")
{$fullname="Susan Jarvis";}
if($fullname=="Bill M")
{$fullname="Bill Moore";}
if($fullname=="Tony K")
{$fullname="Tony Kerr";}
if($fullname=="Magrid S")
{$fullname="Magrid Schindler";}
if($fullname=="Keith B")
{$fullname="Keith Budd";}
if($fullname=="Margaret M")
{$fullname="Margaret Marsh";}
if($fullname=="Lesley I")
{$fullname="Lesley Insall";}
if($fullname=="Beverley B")
{$fullname="Beverley Bleasdale";}
if($fullname=="Jean W")
{$fullname="Jean Waller";}
if($fullname=="Chris D")
{$fullname="Chris Dring";}
if($fullname=="Hugh B")
{$fullname="Hugh Bond";}
if($fullname=="Anna K")
{$fullname="Anna Kulisa";}
 if($fullname=="Janet W")
{$fullname="Janet W";}
 if($fullname=="Margaret R")
{$fullname="Margaret Ruse";}
 if($fullname=="Reg L")
{$fullname="Reg Lonergan";}
if($fullname=="Mikiko T")
{$fullname="Mikiko Tanda";}
 if($fullname=="Brian D")
{$fullname="Brian Drummond";}
 if($fullname=="Phil S")
{$fullname="Phil Selby";}
 if($fullname=="Jill B")
{$fullname="Jill Bird";}
if($fullname=="Bridget G")
{$fullname="Bridget Gregory";}
 if($fullname=="Kay B")
{$fullname="Kay Borman";}
 if($fullname=="Julian B & Julie W")
{$fullname="Julian Butter & Julie Westgarth";}
 if($fullname=="Maggie W")
{$fullname="Maggie Wilcox";}
        $col2= $fullname;
         if ($walk->telephone1 != "") {
            $col2.= " " . $walk->telephone1;
}
        
            if ($walk->telephone2 != "") {
                $col2.= " / " . $walk->telephone2;
            }
//$col2 .=$walk->status;
       echo RHtml::addTableRow(array($col1, $col2));
 $col1="&pound;&pound;&pound;";
$col2=$walk->additionalNotes; 
//$col2=" ";
       echo RHtml::addTableRow(array($col1, $col2));
        $col2 = "<div class='" . $this->walkClass . $walk->status . "'>" . $col2 . "</div>";
$col2 .="<br />";
//echo $col2;
//import json;
//with open('data.txt', 'w') as outfile:
//    json.dump($col2, outfile);
//        echo RHtml::addTableRow(array($col2));
//        echo RHtml::addTableRow(array($col1, $col2));
    }

    

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
