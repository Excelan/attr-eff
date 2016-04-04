<?php

function before_capaproblem_delete($capaproblem)
{

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:capaevent';
    $m->capaproblem = $capaproblem->urn;
    $events = $m->deliver();

    foreach ($events as $e) {

        $m = new Message();
        $m->action = 'delete';
        $m->urn = $e->urn;
        $del = $m->deliver();
    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "before.delete.capaproblem");
$broker->bind_rpc ("ENITYCONSUMER", "before_capaproblem_delete");