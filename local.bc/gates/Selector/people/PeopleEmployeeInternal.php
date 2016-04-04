<?php
namespace Selector\people;

class PeopleEmployeeInternal extends \Gate
{

    function gate()
    {
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:People:Employee:Internal'; //Исполнитель
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
