<?php

function before_capaevent_delete($capaevent)
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:capaeventvariant';
    $m->capaevent = $capaevent->urn;
    $variants = $m->deliver();

    foreach ($variants as $v) {

        $m = new Message();
        $m->action = 'delete';
        $m->urn = $v->urn;
        $del = $m->deliver();
    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "before.delete.capaevent");
$broker->bind_rpc ("ENITYCONSUMER", "before_capaevent_delete");