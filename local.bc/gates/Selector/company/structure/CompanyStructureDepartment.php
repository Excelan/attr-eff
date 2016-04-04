<?php
namespace Selector\company\structure;

class CompanyStructureDepartment extends \Gate
{

    function gate()
    {
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Company:Structure:Department'; //Идентифицированный риск
        $m->order = 'title asc';
        $data = $m->deliver();
        $data->treesort();

        $arr = array();
        foreach($data as $d){
            $prefix = '';
            for ($i=0;$i<$d->_level-1;$i++) $prefix .= '-';
            if ($prefix) $prefix .= ' ';
            array_push($arr,['value'=>(string)$d->urn,'title' => $prefix.$d->title]);
        }

        return [
            'options' => $arr
        ];
    }

}

?>
