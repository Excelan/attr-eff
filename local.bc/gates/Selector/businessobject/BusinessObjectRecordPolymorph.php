<?php
namespace Selector\businessobject;

class BusinessObjectRecordPolymorph extends \Gate
{

    function gate()
    {
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:BusinessObject:Record:Polymorph'; // bo
        $data = $m->deliver();

        $arr = array();
        foreach($data as $d){
            array_push($arr,['value'=>$d->urn,'title'=>$d->title]);
        }



        return [
            'options' => $arr
        ];
    }

}

?>