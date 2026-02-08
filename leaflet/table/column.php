<?php

/**
 * Description of Column
 *
 * @author Chris Vaughan
 */
class RLeafletTableColumn implements JsonSerializable {

    private $ignore = false;
    private $name = "";
    private $sort = false;
    private $table = false;
    private $filter = false;
    private $popup = false;
    private $gridref = false;
    private $latitude = false;
    private $longitude = false;
    //   public $easting = false;
    //   public $northing = false;
    private $linkmarker = false;
    private $columnLinkTo = null;
    private $linkOptionSet = null;
    private $sameTab = false;
    private $align = 'right';
    private $type = "text";
    private $values = [];
    public $columnName = null; // used by sql option

    public function __construct($name) {
        $this->name = $name;
    }

    public function addValue($value) {
        $this->values[] = $value;
    }

    public function getIgnore() {
        return $this->ignore;
    }

    public function addOptions($value) {
        $options = explode(" ", $value);
        foreach ($options as $option) {
            $trimmedOption = trim($option);
            $lowerOption = strtolower($trimmedOption);
            switch ($lowerOption) {
                case "sort":
                    $this->sort = true;
                    break;
                case "table":
                    $this->table = true;
                    break;
                case "filter":
                    $this->filter = true;
                    break;
                case "popup":
                    $this->popup = true;
                    break;
                case "gridref":
                    $this->gridref = true;
                    break;
                case "latitude":
                    $this->latitude = true;
                    break;
                case "longitude":
                    $this->longitude = true;
                    break;
                case "easting":
                //                   $this->easting = true;
                //                   break;
                case "northing":
                    //                   $this->northing = true;
                    break;
                case "date":
                    $this->type = "datetime";
                    break;
                case "int":
                case "integer":
                    $this->type = "number";
                    break;
                case "real":
                case "float":
                    $this->type = "number";
                    break;
                case "textlink":
                    if ($this->linkOptionAlreadySet("textlink")) {
                        break;
                    }
                    // link/textlink/exturl/columnlink/linkmarker are mutually exclusive; first option wins
                    $this->linkOptionSet = "textlink";
                    $this->type = "textlink";
                    break;
                case "link":
                    if ($this->linkOptionAlreadySet("link")) {
                        break;
                    }
                    // link/textlink/exturl/columnlink/linkmarker are mutually exclusive; first option wins
                    $this->linkOptionSet = "link";
                    $this->type = "link";
                    break;
                case "exturl":
                    if ($this->linkOptionAlreadySet("exturl")) {
                        break;
                    }
                    // link/textlink/exturl/columnlink/linkmarker are mutually exclusive; first option wins
                    $this->linkOptionSet = "exturl";
                    $this->type = "exturl";
                    break;
                case "linkmarker":
                    if ($this->linkOptionAlreadySet("linkmarker")) {
                        break;
                    }
                    // link/textlink/exturl/columnlink/linkmarker are mutually exclusive; first option wins
                    $this->linkOptionSet = "linkmarker";
                    $this->linkmarker = true;
                    break;
                case "sametab":
                case "no_target_blank":
                    $this->sameTab = true;
                    break;
                case "left":
                    $this->align = 'left';
                    break;
                case "right":
                    $this->align = 'right';
                    break;
                case "centre":
                case "center":
                    $this->align = 'center';
                    break;
                case "ignore":
                    $this->ignore = true;
                    break;
                case "":
                    break;
                default:
                    if (strpos($lowerOption, 'columnlink=') === 0) {
                        if ($this->linkOptionAlreadySet("columnlink")) {
                            break;
                        }
                        // link/textlink/exturl/columnlink/linkmarker are mutually exclusive; first option wins
                        $this->linkOptionSet = "columnlink";
                        $this->columnLinkTo = substr($trimmedOption, strlen('columnlink='));
                    } else {
                        $this->invalidOptionMessage($option, "Option not recognised for {$this->name}");
                    }
            }
        }
    }

    private function linkOptionAlreadySet($option) {
        if ($this->linkOptionSet !== null) {
            $this->invalidOptionMessage($option, "{$this->linkOptionSet} was already set for {$this->name}; link options are mutually exclusive (configuration notice shown in page output)");
            return true;
        }
        return false;
    }

    private function invalidOptionMessage($option, $detail) {
        // Surface configuration issues in the rendered page so site administrators can spot them without checking server logs
        Echo "<p>Invalid options supplied:" . $option . " - " . $detail . "</p>";
    }

    public function getName() {
        return $this->name;
    }

    public function validateColumnLinkTarget($headings) {
        if ($this->columnLinkTo === null) {
            return;
        }
        if (!in_array($this->columnLinkTo, $headings)) {
            $available = implode(", ", $headings);
            $this->invalidOptionMessage("columnlink=" . $this->columnLinkTo, "Column heading not found for {$this->name}. Available headings: " . $available);
        }
    }

    public function jsonSerialize(): mixed {
        return [
            'name' => $this->name,
            'sort' => $this->sort,
            'table' => $this->table,
            'filter' => $this->filter,
            'popup' => $this->popup,
            'gridref' => $this->gridref,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'linkmarker' => $this->linkmarker,
            'columnLinkTo' => $this->columnLinkTo,
            'sameTab' => $this->sameTab,
            'align' => $this->align,
            'type' => $this->type,
            'values' => $this->values
        ];
    }
}
