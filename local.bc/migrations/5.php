<?php

$GLOBALS['WORLD']['MIGRATIONS'][5] = function () {

    println("V5 - Убрать старые прототипы процессов из Definition:Prototype:System", 1, TERM_BLUE);

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Definition:Prototype:System';
    $m->isprocess = true;
    $systemprotos = $m->deliver();
    foreach ($systemprotos as $systemproto) {
        $m = new Message();
        $m->action = 'delete';
        $m->urn = $systemproto->urn;
        $m->deliver();
    }

};
