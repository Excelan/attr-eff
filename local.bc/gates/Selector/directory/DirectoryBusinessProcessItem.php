<?php
namespace Selector\directory;

class DirectoryBusinessProcessItem extends \Gate
{

    function gate()
    {
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Directory:BusinessProcess:Item'; //Процессы компании
        $data = $m->deliver();

        $arr = array();
        foreach($data as $d){
            array_push($arr,['value'=>(string)$d->urn,'title'=>$d->title]);
        }

        return [
            'options' => $arr
        ];
    }

}

?>