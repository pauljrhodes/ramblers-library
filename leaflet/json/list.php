<?php

/**
 * Description of list
 *
 * @author Chris Vaughan
 * 15/04/24 Charlie Bigley Allow specification of a table name or a SELECT statement
 */
class RLeafletJsonList extends RLeafletMap {

    private $filename = "";
    private $validOptions = false;
    private $list = null;
    private $fields = [];
    public $paginationDefault = 10;
    public $displayOptions;

    public function __construct($filename) {
        parent::__construct();
        $this->filename = $filename;
        $this->displayOptions = null;
    }

    public function setDisplayOptions($displayOptions) {
        $this->displayOptions = $displayOptions;
    }

    public function setOptions($options) {
        if (!is_array($options)) {
            echo "ERROR: RLeafletSqlList options  must be an array";
            return;
        }
        $this->list = new RLeafletTableColumns();
        foreach ($options as $option) {
            if (!array_key_exists("heading", $option)) {
                echo "ERROR: RLeafletSqlList options  does not contain heading field";
                return;
            }
            if (!array_key_exists("column", $option)) {
                echo "ERROR: RLeafletSqlList options  does not contain column field";
                return;
            }
            if (!array_key_exists("options", $option)) {
                echo "ERROR: RLeafletSqlList options  does not contain options field";
                return;
            }
            $column = new RLeafletTableColumn($option["heading"]);
            $this->list->addColumn($column);
            $column->addOptions($option["options"]);
            $column->columnName = $option["column"];
            $this->fields[] = $option["column"];
        }
        $this->validOptions = true;
    }

    public function display() {
        If (!$this->validOptions) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_("RLeafletJsonList options are invalid"), 'error');
            return;
        }
        $result = $this->readJson();
        if ($result === false) {
            return;
        }
        foreach ($result as $key => $row) {

            $columns = $this->list->getColumns();
            foreach ($columns as $column) {
                if (property_exists($row, $column->columnName)) {
                    $col = $column->columnName;
                    $value = $row->$col;
                    $column->addValue($value);
                }
            }
        }

        $this->help_page = "listofitems.html";
        $this->options->cluster = true;
        $this->options->fullscreen = true;
        $this->options->filter = true;
        $this->options->locationsearch = true;
        $this->options->osgrid = true;
        $this->options->mouseposition = true;
        $this->options->settings = true;
        $this->options->mylocation = true;
        $this->options->rightclick = true;
        $this->options->fitbounds = true;
        $this->options->print = true;

        $data = new class {
            
        };
        $data->displayOptions = $this->displayOptions;
        $data->list = $this->list;
        $data->paginationDefault = $this->paginationDefault;

        parent::setCommand('ra.display.tableList.display');
        parent::setDataObject($data);
        parent::display();
        RLoad::addScript("media/lib_ramblers/leaflet/table/ramblerstable.js");
        RLoad::addStyleSheet("media/lib_ramblers/leaflet/table/style.css");
        RLoad::addStyleSheet('media/lib_ramblers/css/ramblerslibrary.css');
        RLoad::addScript("media/lib_ramblers/js/ra.tabs.js");
        RLoad::addStyleSheet("media/lib_ramblers/css/ra.tabs.css");
        RLoad::addStyleSheet('media/lib_ramblers/vendors/cvList/cvList.css');
        RLoad::addScript("media/lib_ramblers/vendors/cvList/cvList.js");

      //  RLoad::addScript("https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/7.12.1/polyfill.min.js", "text/javascript");
    }

    private function readJson() {
        $data = file_get_contents($this->filename);
        $results = json_decode($data);
        if ($results === false) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_("RLeafletJsonList unable to read json file"), 'error');
        }
        return $results;
    }
}
