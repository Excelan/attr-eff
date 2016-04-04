<?php

function after_capa_create($capa)
{

    //Привязка капы к документу (зеркалу)
//    $m = new Message();
//    $m->action = 'create';
//    $m->urn = 'urn-document';
//    $m->resource = $capa->urn;
//    $document = $m->deliver();

    //Привязка документа (зекрала) к капе
//    $m = new Message();
//    $m->action = 'update';
//    $m->urn = $capa->urn;
//    $m->mirror = $document->urn;
//    $upd = $m->deliver();

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "after.create.capa");
$broker->bind_rpc ("ENITYCONSUMER", "after_capa_create");