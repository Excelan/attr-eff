<?php
namespace Selector\management;

class ManagementPostGroup extends \Gate
{

    function gate()
    {
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Group';
        $m->order = 'title ASC';
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
