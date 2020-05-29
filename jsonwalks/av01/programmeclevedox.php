<?php

/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan: modified by Tony Parsons
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksAv01Programmeclevedox extends RJsonwalksDisplaybase {

    private $lastValue = "";
    public $addDescription = true;

    const BR = "<br />";

    function DisplayWalks($walks) {
 $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);        
 $items = $walks->allWalks();
//$myfile = fopen("https://bristolramblers.org.uk/images/pdfs/Walksprogrammes/tony.txt", "r") or die("Unable to open file!");
//      foreach(file('https://bristolramblers.org.uk/images/pdfs/Walksprogrammes/tony.txt') as $line) {
//   echo $line. "\n";
//}
      $colx="";
      $iprint=0;
      foreach ($items as $walk) {
           $this->displayWalk($walk,$colx,$iprint,$id);
        }
        if($iprint==1){echo $colx;
                       $url="https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/walk-detail.aspx?walkID=".$id;
   echo "<br /> <a href=".$url."> click here for details</a>"; 
                      }}

    private function displayWalk($walk,&$colx,&$iprint,&$id) {
      $datestring=$walk->walkDate->format('Y-m-d');
      $tw=strtotime($datestring);
       $dweek=7*24*60*60;
     $tb=time();
      $te=$tb+$dweek;
      $day=$walk->walkDate->format('D');
       if($day=="Sat"){
         if($tw>$tb){
           if($tw<$te){
  if ($walk->groupName=="Bristol"){$iprint=10;}
         if ($walk->groupName=="Clevedon"){
  $col1 = $walk->walkDate->format('d') ." ". $walk->walkDate->format('M') ;
        $col2 = $walk->title ;
           $colx = $col1." Clevedon group walk ".$col2;
           $id=$walk->id;
           $iprint=$iprint+1;
//#        echo RHtml::addTableRow(array($col1, $col2));
    }}}}

  return ; }
}

