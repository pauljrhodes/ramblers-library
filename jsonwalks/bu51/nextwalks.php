<?php
/**
 * Description of WalksDisplay
 *
 * @author Chris Vaughan edited Paul Rhodes
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdNextwalks extends RJsonwalksDisplaybase {

    public $walkClass = "nextwalk";
    public $feedClass = "walksfeed";
    private $nowalks = 5;

    function DisplayWalks($walks) {
        if ($this->displayGradesSidebar) {
            RJsonwalksWalk::gradeSidebar();
        }
        $schemawalks = array();
        $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
        $items = $walks->allWalks();
        $no = 0;
		$urlbase = "http://www.chilterns2030s.org.uk/walks/our-walks/";

        foreach ($items as $walk) {
            $no+=1;
            if ($no > $this->nowalks) {
                break;
            }
            #$desc = "<li id=W" . $walk->id . "><h4>" . $walk->title . "</h4><b>"; 
			#$desc .= $walk->startLocation->time->format('l jS F H:i') . " until approx. " . $walk->finishTime->format('H:i') . "</b><br />"; 
            $date = "<b>" . $walk->walkDate->format('D, jS F') . "</b>, ";
            $desc = $date . $walk->title . ", ";
            if ($walk->distanceMiles > 0) {
                $desc .= $walk->nationalGrade . " " . $walk->distanceMiles . " miles / " . $walk->distanceKm . "km" ;
            }
            #echo "<div class='" . $this->walkClass . $walk->status . "' " . "><a href='" . $urlbase . "#W" . $walk->id . "' target='_blank' >" . $desc . "</a></div>" . PHP_EOL;
			echo "<li> <div class='" . $this->walkClass . $walk->status . "' " . "><a href='" . $urlbase . "#W" . $walk->id . "' >" . $desc . "</a></div></li>" . PHP_EOL;
            if ($walk->isCancelled()) {
                echo "CANCELLED: " . $walk->cancellationReason;
            } else {
            	
				$performer = new RJsonwalksStructuredperformer($walk->groupName); # Change to walk leader in future
                $location = new RJsonwalksStructuredlocation($walk->startLocation->description, $walk->startLocation->postcode);
				if ($walk->distanceMiles > 0) {
					$potentialaction = new RJsonwalksStructuredaction($walk->distanceMiles, $walk->distanceKm, $walk->nationalGrade);
				}
				
				$orgainzer = new RJsonwalksStructuredorganizer("#GroupInfo");
                $schemawalk = new RJsonwalksStructuredevent($performer, $location, $potentialaction, $orgainzer);
				$schemawalk->id = "#W" . $walk->id;
				# if area listing refer to OurGroupsPage/#$walk->groupName
                $schemawalk->name = $walk->title;
                $schemawalk->url = $urlbase . "#W" . $walk->id;
				$schemawalk->sameas = $walk->detailsPageUrl;
				$schemawalk->startDate = $walk->startLocation->time->format(DateTime::ISO8601);
				if ( $walk->finishTime != null) {
					$schemawalk->endDate = $walk->finishTime->format(DateTime::ISO8601);
				}
				$schemawalk->description = "A " .  $walk->nationalGrade . " " . $walk->distanceMiles . "mile / " . $walk->distanceKm . "km walk";
				# Google don't like markup which doesn't appear on page so description must be as on page and image should  be excluded from next walks summary listing
				#if ($walk->description == "") {
                #    $schemawalk->description = $desc;
                #} else {
                #    $schemawalk->description = $walk->description;  
                #}
                #$schemawalk->image = "http://www.ramblers-webs.org.uk/images/ra-images/logos/standard/logo92.png";
                
                $schemawalks[] = $schemawalk;
            }
        }
        echo "</ul>" . PHP_EOL;
        $script = json_encode($schemawalks);
        $script = str_replace('"context":', '"@context":', $script);
        $script = str_replace('"type":', '"@type":', $script);
		$script = str_replace('"id":', '"@id":', $script);
        $script = str_replace('\/', '/', $script);
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($script, "application/ld+json");
    }
    function noWalks($no) {
        $this->nowalks = $no;
    }
}
