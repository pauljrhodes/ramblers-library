<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan: modified by Tony Parsons
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksAv01Programmeclevedon extends RJsonwalksDisplaybase {

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
      $bst="y";
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
//echo "ccc".$bst;
//m
//c        $bst=$this->displayWalk($walk,$thismonth3,$bst);
           $this->displayWalk($walk,$thismonth3,$bst);
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

//    private function &displayWalk($walk,$thismonth3,$bst):string {
    private function displayWalk($walk,$thismonth3,$bst) {
 ///       $col1 = ""

      $string = $walk->title;
$string2=$walk->additionalNotes;
$string3=$walk->description;
//       $z=$walk->walkDate->format('d');
//      $zz=$walk->walkDate->format('M'); 
//echo $z.$zz;    
//echo "%".$string."<br />";
//echo "%%".$string2."<br />";
//echo "%%%".$string3."<br />";
//      $zzz=$walk->groupCode; 
//echo $zzz;
//=================================================
$theme="no";
$stringx="";
$string1= strstr($string3, "hemed");
//echo "uuu /".$string1."/";
if (!($string1=="")){
$theme="yes";
$stringx="t";
}
$string1= strstr($string, "hemed");
if (!($string1=="")){
$theme="yes";
$stringx="t";
}
//echo "theme /".$theme."/";
if($theme=="yes"){
$walk->title=$walk->title." [T]";
//echo "xxx".$walk->title;
}
//=================================================      
$www="no";
//$stringx="";
$string1= strstr($string3, "essex Wanderer");
//echo "uuu /".$string1."/";
if (!($string1=="")){
$www="yes";
$stringx="w"; 
}
$string1= strstr($string, "essex Wanderer");
if (!($string1=="")){
$www="yes";
$stringx="w";
 }
//echo "www /".$www."/";
if($www=="yes"){
$walk->title=$walk->title." [WW]";
//echo "xxx".$walk->title;
}
if ($walk->groupName=="Clevedon"){$stringx="B";}
        $day= $walk->walkDate->format('D');  
// following line restricts the listing to saturday walks
       if(!($day=="Sat")){$stringx="";}
         if (!($stringx=="")){
$col1=" ";
  if ($walk->hasMeetPlace) {
    if($walk->meetLocation->description=="Meet at Clifton Water Tower for lift sharing") {
      $col2= "<b>".$walk->meetLocation->description."</b>";                      
      echo RHtml::addTableRow(array($col1, $col2));
    }}
  //echo $bst;
  if($bst=="y"){
    if($walk->walkDate->format('D')=="Sun"){
      if($walk->walkDate->format('d')>24.5){
        if($walk->walkDate->format('m')=="03"){
          $bst="n";
          $col2='<b><span style="color: red";> British Summer Time Begins Times are BST</span></b>';
          echo RHtml::addTableRow(array($col1, $col2));
          $bst="n";}
         if($walk->walkDate->format('m')=="10"){
          $bst="n";
          $col2='<b><span style="color: red";> British Summer Time Ends Times are GMT</span></b>';
          echo RHtml::addTableRow(array($col1, $col2));}
      }}}
  
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
       
           $col2 .= " <br />"; 
//z            $col1=$walk->meetLocation->time->format('H').":".$walk->meetLocation->time->format('i'); 
//$col1=substr($walk->meetLocation->timeHHMM,0,5) ; 
$col1=$walk->meetLocation->timeHHMM ;
//          echo strlen($col1);
//          echo substr($col1,4,1);
          If(strlen($col1)<7){$col1="0".$col1;}
          if(substr($col1,5,1)=="p"){
          $aaa=substr($col1,0,2)+12;
          $bbb=substr($col1,2,5);
          $col1=$aaa.$bbb;}
          $col1=substr($col1,0,5);
//          $col1=date('H',$walk->meetLocation->time).date('i',$walk->meetLocation->time);
         $col2=" ";
            if(!($walk->meetLocation->description=="Meet at Great George Street for lift sharing.")){
            if(!($walk->meetLocation->description=="Meet Great George Street for lift sharing.")){
            if(!($walk->meetLocation->description=="Meet at Great George Street for lift sharing")){
            if(!($walk->meetLocation->description=="Meet at Great George Street BS1 for lift sharing.")){
            if(!($walk->meetLocation->description=="Meet at Great George Street BS1 for lift sharing")){              
            if(!($walk->meetLocation->description=="Meet at Clifton Water Tower for lift sharing")) {
            if(!($walk->meetLocation->description=="Meet at Water Tower, Durdham Down for lift sharing")) {
            if(!(substr($walk->meetLocation->description,0,19)=="Meet at Priory Road")) {
  $col2= $walk->meetLocation->description;
             echo RHtml::addTableRow(array($col1, $col2));
            $col1=" ";

            }}}}}}}}}
      if ($walk->startLocation->exact) {
//        $col2 .="<br />";
      if(!($col1==" ")) {
      $col2=" ";
       echo RHtml::addTableRow(array($col1, $col2));
            $col1=" ";
        }
//***********************
//echo "qqq".$walk->walkDate->format('m');
        $col1=$walk->startLocation->time->format('H').":".$walk->startLocation->time->format('i');
//***********************
//                  $col1= $walk->startLocation->timeHHMMshort . " " . 
          $col2 = $walk->startLocation->description;
            $col2 .= " @". $walk->startLocation->gridref;
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
//$string1= strpos($col2, "essex Wanderer");
$string1= strpos($col2, "WESSEX WANDERER RAILWAY WALKS");              
//echo "s1".$string1."/";
if(!($string1=="")){ 
 $col2=substr($col2,0,$string1).". See Wessex Wanderer Website for full details."; 
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
{$fullname="Keith B<i>udd</i>";}
if($fullname=="Keith  B")
{$fullname="Keith B<i>udd</i>";}
if($fullname=="Clive R")
{$fullname="Clive R<i>ichardson</i>";}
//if($fullname=="Gwyneth L")
//{$fullname="Gwyneth L<i>ittlejohn</i>";}
if($fullname=="Julian B")
{$fullname="Julian B<i>utter</i>";}
if($fullname=="Meryl T")
{$fullname="Meryl T<i> ill</i>";}
if($fullname=="David P")
{$fullname="David P<i>ingstone</i>";}
if($fullname=="Wendy B")
{$fullname="Wendy B<i>ritton</i>";}
//if($fullname=="Tony C")
//{$fullname="Tony C<i>arter</i>";}
if($fullname=="Margaret P")
{$fullname="Margaret P<i>ostlethwaite</i>";}
if($fullname=="Gordon S")
{$fullname="Gordon S<i>tillman</i>";}
if($fullname=="Andy Se")
{$fullname="Andy Se<i>ars</i>";}
if($fullname=="Martin S")
{$fullname="Martin S<i>ummerell</i>";}
if($fullname=="Tony P")
{$fullname="Tony P<i>arsons</i>";}
if($fullname=="Paula C")
{$fullname="Paula C<i>annings</i>";}
if($fullname=="Rosemary D")
{$fullname="Rosemary D<i>an</i>";}
if($fullname=="Sue Y")
{$fullname="Sue Y<i>oung</i>";}
if($fullname=="Nigel  A")
{$fullname="Nigel A<i>ndrews</i>";}
if($fullname=="Nigel A")
{$fullname="Nigel A<i>ndrews</i>";}
if($fullname=="Julie B")
{$fullname="Julie B<i>oston</i>";}
if($fullname=="Maureen J")
{$fullname="Maureen J<i>ohnson</i>";}
if($fullname=="Bob M")
{$fullname="Bob M<i>ills</i>";}
if($fullname=="Stephen D")
{$fullname="Stephen D<i>raper</i>";}
if($fullname=="Patsy H")
{$fullname="Patsy H<i>udson</i>";}
//if($fullname=="Mary M")
//{$fullname="Mary M<i>agro</i>";}
//if($fullname=="Geoff H")
//{$fullname="Geoff H<i>arris</i>";}
if($fullname=="Susan C")
{$fullname="Susan C<i>arter</i>";}
if($fullname=="Neil B")
{$fullname="Neil B<i>urlton</i>";}
if($fullname=="John W")
{$fullname="John W<i>rigley</i>";}
if($fullname=="Carew R")
{$fullname="Carew R<i>eynell</i>";}
//if($fullname=="Mike F")
//{$fullname="Mike F<i>ox</i>";}
if($fullname=="Dave O")
{$fullname="Dave O<i>sborne</i>";}
//if($fullname=="Margaret R")
//{$fullname="Margaret R<i>use</i>";}
if($fullname=="Peter G")
{$fullname="Peter G<i>ould</i>";}
if($fullname=="Gill D")
{$fullname="Gill D<i>avies</i>";}
if($fullname=="Ann L")
{$fullname="Ann L<i>ight</i>";}
  if($fullname=="Heather T")
{$fullname="Heather T<i>oyne</i>";}
if($fullname=="Roger G")
{$fullname="Roger G<i>riffiths</i>";}
if($fullname=="Sarah M")
{$fullname="Sarah M<i>arshall</i>";}
if($fullname=="Derek B")
{$fullname="Derek B<i>ones</i>";}
if($fullname=="Ben M")
{$fullname="Ben M<i>acKay</i>";}
if($fullname=="Richard R")
{$fullname="Richard R<i>edding</i>";}
if($fullname=="Chris S")
{$fullname="Chris S<i>anders</i>";}
if($fullname=="Chris H")
{$fullname="Chris H<i>olloway</i>";}
if($fullname=="Julie P")
{$fullname="Julie P<i>arry</i>";}
if($fullname=="Sarah S")
{$fullname="Sarah S<i>horter</i>";}
//if($fullname=="Sarah  S")
//{$fullname="Sarah S<i>horter</i>";}
if($fullname=="Andy St")
{$fullname="Andy St<i>eward</i>";}
if($fullname=="Susan J")
{$fullname="Susan J<i>arvis</i>";}
if($fullname=="Bill M")
{$fullname="Bill M<i>oore</i>";}
if($fullname=="Tony K")
{$fullname="Tony K<i>err</i>";}
//if($fullname=="Magrid S")
//{$fullname="Magrid S<i>chindler</i>";}
if($fullname=="Keith B")
{$fullname="Keith B<i>udd</i>";}
if($fullname=="Margaret M")
{$fullname="Margaret M<i>arsh</i>";}
//if($fullname=="Lesley I")
//{$fullname="Lesley I<i>nsall</i>";}
if($fullname=="Beverley B")
{$fullname="Beverley B<i>leasdale</i>";}
if($fullname=="Jean W")
{$fullname="Jean W<i>aller</i>";}
if($fullname=="Chris D")
{$fullname="Chris D<i>ring</i>";}
if($fullname=="Hugh B")
{$fullname="Hugh B<i>ond</i>";}
if($fullname=="Anna K")
{$fullname="Anna K<i>ulisa</i>";}
 if($fullname=="Janet W")
{$fullname="Janet W<i>ood</i>";}
// if($fullname=="Margaret R")
//{$fullname="Margaret R<i>use</i>";}
// if($fullname=="Reg L")
//{$fullname="Reg L<i>onergan</i>";}
//if($fullname=="Mikiko T")
//{$fullname="Mikiko T<i>anda</i>";}
 if($fullname=="Brian D")
{$fullname="Brian D<i>rummond</i>";}
  if($fullname=="Geoff D")
{$fullname="Geoff D<i>aniels</i>";}
 if($fullname=="Phil S")
{$fullname="Phil S<i>elby</i>";}
 if($fullname=="Jill Bi")
{$fullname="Jill Bi<i>rd</i>";}
//if($fullname=="Jill Ba")
//{$fullname="Jill Ba<i>rrand</i>";}
if($fullname=="Bridget G")
{$fullname="Bridget G<i>regory</i>";}
// if($fullname=="Kay B")
//{$fullname="Kay B<i>orman</i>";}
 if($fullname=="Julian B & Julie W")
{$fullname="Julian B<i>utter</i> & Julie W<i>estgarth</i>";}
 if($fullname=="Maggie W")
{$fullname="Maggie W<i>ilcox</i>";}
 if($fullname=="David O")
{$fullname="David O<i>sborne</i>";} 
 if($fullname=="Alison P")
{$fullname="Alison P<i>arry</i>";} 
 if($fullname=="Mandy M")
{$fullname="Mandy M<i>acDonald</i>";}
//  if($fullname=="Andrew R")
//{$fullname="Andrew R<i>anshaw</i>";}
        $col2= $fullname;
         if ($walk->telephone1 != "") {
          $t1=preg_replace('/\s/','~',$walk->telephone1);
           $t1=preg_replace('/\s/','&nbsp',$walk->telephone1);
//             echo $t1;
            $col2.= " " . $t1;
}
        
            if ($walk->telephone2 != "") {
                         $t2=preg_replace('/\s/','&nbsp',$walk->telephone2);
//             echo $t2;
                $col2.= " / " . $t2;
            }
//$col2 .=$walk->status;
       echo RHtml::addTableRow(array($col1, $col2));
 $col1="&pound;&pound;&pound;";
//$col2=$walk->additionalNotes; 
$col2=" ";
       echo RHtml::addTableRow(array($col1, $col2));
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

  return $bst; }
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
