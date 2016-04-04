<?php

function before_capa_delete($capa)
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:capaproblem';
    $m->capa = $capa->urn;
    $problems = $m->deliver();

    foreach ($problems as $p) {

        $m = new Message();
        $m->action = 'delete';
        $m->urn = $p->urn;
        $del = $m->deliver();

    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "before.delete.capa");
$broker->bind_rpc ("ENITYCONSUMER", "before_capa_delete");