<?php



/**
TableMetadata - column/row names
 */
class TableObject {

    protected $_structType; //
    protected $_records;

    public function __construct(array $records, $type=null) // array of Struct of type
    {
        $this->_records = $records;
    }

    function get()
    {
        return $this->_records;
    }

    //
    function addRecord($r)
    {

    }

    function getRecords(int $count)
    {

    }

    function toJSON()
    {

    }

    function sortAsc($columnName)
    {
        $cmp = function ($a, $b) use ($columnName)
        {
            if ($a[$columnName] == $b[$columnName]) {
                return 0;
            }
            return ($a[$columnName] < $b[$columnName]) ? -1 : 1;
        };
        $bool = usort($this->_records, $cmp);
        return $bool;
    }

    function sortDesc($columnName)
    {
        $cmp = function ($a, $b) use ($columnName)
        {
            if ($a[$columnName] == $b[$columnName]) {
                return 0;
            }
            return ($a[$columnName] < $b[$columnName]) ? 1 : -1;
        };
        $bool = usort($this->_records, $cmp);
        return $bool;
    }

    function filterGreaterThen($column, $o)
    {
        function criteria_greater_than($column, $min)
        {
            return function($item) use ($column, $min) {
                return $item[$column]->get() > $min;
            };
        }
        $this->_records = array_filter($this->_records, criteria_greater_than($column, $o));
    }

    function __toString()
    {
        $s = '';
        foreach ($this->_records as $index => $struct)
        {
            $s .= (string) $struct."\n\n";
        }
        return $s;
    }

}


?>