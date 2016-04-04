<?php

function after_capatask_update($rs)
{
	$old = $rs[1];
	$new = $rs[0];


	if ( $old->done ==false && $new->done == true )
	{

        //Указываем что по мероприятию задача уже завершена. Поскольку 1 мероприятие - 1 задача
        $m = new Message();
        $m->action = 'update';
        $m->urn = $old->capaevent->urn;
        $m->taskcompleted = true;
        $capaevent = $m->deliver();
    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "after.update.capatask");
$broker->bind_rpc ("ENITYCONSUMER", "after_capatask_update");