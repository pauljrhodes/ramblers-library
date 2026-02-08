<?php

/**
 * Description of Columns
 *
 * @author Chris Vaughan
 */
class RLeafletTableColumns implements JsonSerializable {

    private $columns = [];
    private $displayMap = false;
    private $paginateList = true;

    public function __construct() {
        
    }

    public function addColumn($column) {
        $this->columns[] = $column;
    }

    public function getColumn($no) {
        if (array_key_exists($no, $this->columns)) {
            return $this->columns[$no];
        } else {
            return null;
        }
    }

    public function getColumns() {
        return $this->columns;
    }

    public function validateColumnLinks() {
        $headings = [];
        foreach ($this->columns as $col) {
            $headings[] = $col->getName();
        }
        foreach ($this->columns as $col) {
            $col->validateColumnLinkTarget($headings);
        }
    }

    public function removeIgnoredColumns() {
        foreach ($this->columns as $key => $col) {
            if ($col->getIgnore()) {
                unset($this->columns[$key]);
            }
        }
        $this->columns = array_values($this->columns);
    }

    public function jsonSerialize(): mixed {
        return [
            'items' => $this->columns,
            'displayMap' => $this->displayMap,
            'paginateList' => $this->paginateList
        ];
    }
}
