<?php
namespace Selector\process;

class ProcessProto extends \Gate
{

    public function gate()
    {
        $data = $this->data;
        if (!is_array($data)) {
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:ProcessModel:System';
        $m->order = 'title asc';
        //$m->isprocess = true;
        $data = $m->deliver();

        $arr = array();
        foreach ($data as $d) {
            array_push($arr, ['value' => (string) $d->urn, 'title' => $d->title]);
        }

        return [
                'options' => $arr
        ];
    }
}
