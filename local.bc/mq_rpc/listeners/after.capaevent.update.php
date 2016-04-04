<?php

function after_capaevent_update($rs)
{
	$old = $rs[1];
	$new = $rs[0];


	if ( $old->confirmed ==false && $new->confirmed == true ) {

        $capa_urn = $old->capaproblem->capa->urn;

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:capaproblem';
        $m->capa = $capa_urn;
        $problems = $m->deliver();

        //Если на все мероприятия назначены исполнители, то отправляем капу на согласование инициатору
        $all_events_are_confirmed = true;
        foreach ($problems as $p) {

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:capaevent';
            $m->capaproblem = $p->urn;
            $m->confirmed = false;
            $capaevent = $m->deliver();

            if ( count($capaevent) ) {
                $all_events_are_confirmed = false;
                break;
            }
        }

        if ( $all_events_are_confirmed ) {

            $m = new Message();
            $m->action = 'update';
            $m->urn = $capa_urn;
            $m->workflowcapa = "authormatching";
            $capa = $m->deliver();
        }

    }

    if ( $old->taskcompleted ==false && $new->taskcompleted == true ) {

        $capa_urn = $old->capaproblem->capa->urn;

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:capaproblem';
        $m->capa = $capa_urn;
        $problems = $m->deliver();

        //Если по всем мероприятиям завершены задачи, то завершаем капу
        $all_events_are_taskcompleted = true;
        foreach ($problems as $p) {

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:capaevent';
            $m->capaproblem = $p->urn;
            $m->taskcompleted = false;
            $capaevent = $m->deliver();

            if ( count($capaevent) ) {
                $all_events_are_taskcompleted = false;
                break;
            }
        }

        if ( $all_events_are_taskcompleted ) {

            $m = new Message();
            $m->action = 'update';
            $m->urn = $capa_urn;
            $m->workflowcapa = "done";
            $capa = $m->deliver();
        }
    }

}

$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "after.update.capaevent");
$broker->bind_rpc ("ENITYCONSUMER", "after_capaevent_update");