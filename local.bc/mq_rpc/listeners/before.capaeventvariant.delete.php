<?php

function before_capaeventvariant_delete($capaeventvariant)
{

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:capatask';
    $m->capaeventvariant = $capaeventvariant->urn;
    $capatasks = $m->deliver();

    foreach ($capatasks as $ct) {

        $m = new Message();
        $m->action = 'delete';
        $m->urn = $ct->urn;
        $del = $m->deliver();
    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "before.delete.capaeventvariant");
$broker->bind_rpc ("ENITYCONSUMER", "before_capaeventvariant_delete");