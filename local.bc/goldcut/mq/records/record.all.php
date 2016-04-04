<?php

// TODO add update, delete

// TODO move out to madeheart
/**
function record_created($new)
{
    //printlnd($new,1,TERM_GREEN);
    if (!$new->user) return;
    $m = new Message();
    $m->urn = 'urn-actionrecord';
    $m->action = 'create';
    $m->urnlink = $new->urn;
    $m->actiontype = 'urn-actiontype-1';
    $m->user = $new->user;
    $m->deliver();
}
$broker = Broker::instance();
$broker->queue_declare ("ENITYCONSUMERREC", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMERREC", "after.create.product");
$broker->bind_rpc ("ENITYCONSUMERREC", "record_created");

function after_any_translated($tr)
{
    //println($tr->urn,1,TERM_VIOLET);
    //println($tr->lang,1,TERM_VIOLET);
    //println($tr->translator,1,TERM_VIOLET);
    $m = new Message();
    $m->urn = 'urn-actionrecord';
    $m->action = 'create';
    $m->urnlink = $tr->urn;
    // en - 101, de - 102, cn - 103, it - 104
    if ($tr->lang == 'en')
        $m->actiontype = 'urn-actiontype-101';
    elseif ($tr->lang == 'de')
        $m->actiontype = 'urn-actiontype-102';
    elseif ($tr->lang == 'cn')
        $m->actiontype = 'urn-actiontype-103';
    elseif ($tr->lang == 'it')
        $m->actiontype = 'urn-actiontype-104';
    elseif ($tr->lang == 'ru')
        $m->actiontype = 'urn-actiontype-107';
    elseif ($tr->lang == 'ua')
        $m->actiontype = 'urn-actiontype-108';
    elseif ($tr->lang == 'es')
        $m->actiontype = 'urn-actiontype-109';
    else
        $m->actiontype = 'urn-actiontype-100';
    $m->user = $tr->translator;
    $m->deliver();
}
$broker->queue_declare ("ENITYCONSUMERTRANSLATEREC", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMERTRANSLATEREC", "after.translate.product");
$broker->bind_rpc ("ENITYCONSUMERTRANSLATEREC", "after_any_translated");
*/
?>