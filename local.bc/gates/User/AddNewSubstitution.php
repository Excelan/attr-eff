<?php
namespace User;

class AddNewSubstitution extends \Gate
{

    function gate()
    {
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'create';
        $m->urn = 'urn:substitution';
        $m->delegationfrom = $data['from'];
        $m->delegationto = $data['to'];
        $m->startdate = $data['startDate'];
        $m->enddate = $data['endDate'];
        $m->processurn = $data['urn'];
        $m->deliver();

        return ['status' => 200];
    }

}

?>