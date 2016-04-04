<?php

function after_capa_update($rs)
{
	$old = $rs[1];
	$new = $rs[0];

    //$new->workflowcapa return number
    //{"id":1971939242,"author":0,"approver":0,"mirror":0,"workflowcapa":10,"created":"1445539976","updated":1445548386,"_e":"capa","urn":"urn-capa-1971939242"}
    //That's why we need to load new capa

    $m = new Message();
    $m->action = 'load';
    $m->urn = $new->urn;
    $capa = $m->deliver();

	if ( $old->workflowcapa != "done" && $capa->workflowcapa == "done" )
	{

        //Капа завершена. Создаем документ самоинспекция по данной капе
        $m = new Message();
        $m->gate = 'Selfinspection/CreateFromCapa';
        $m->capa_urn = $old->urn;
        $selfinspection = $m->send();
    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "after.update.capa");
$broker->bind_rpc ("ENITYCONSUMER", "after_capa_update");