<?php

$GLOBALS['WORLD']['MIGRATIONS'][4] = function () {

    println("V4", 1, TERM_BLUE);

    // Права старта проверить, MQ зеркало в прототипе - авто внесение в atomic perms
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RBAC:Permission:Atomic';
    $m->cando = 'ProcessStart';
    $startperms = $m->deliver();
    foreach ($startperms as $startperm) {
        $systemProto = $startperm->withprototype;
        // load :urn by other side :useone
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->DefinitionPrototypeSystem = $systemProto->urn;
        $documentProto = $m->deliver();
        // println($m);

        $m = new Message();
        $m->action = 'add';
        $m->urn = $startperm->actor->urn;
        $m->to = $documentProto->urn.':initiators';
        println($m);
        $m->deliver();
    }

    unset($documentProto);

    // - Ответственные на этапах - проверить - 2 прототипа заменить на раздельные
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RBAC:DocumentPrototypeResponsible:System';
    $stageActors = $m->deliver();
    foreach ($stageActors as $stageActor) {
        // model process
        $systemproto = $stageActor->processprototype;
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:ProcessModel:System';
        $m->indomain = $systemproto->indomain;
        $m->target = $systemproto->ofclass;
        $m->way = $systemproto->oftype;
        $DefinitionProcessModelSystem = $m->deliver();
        // println($m);
        unset($systemproto);

        // subject
        $systemproto = $stageActor->subjectprototype;
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->DefinitionPrototypeSystem = $systemproto->urn;
        $documentProto = $m->deliver();
        if (!count($documentProto)) {
            println('old model '.$DefinitionProcessModelSystem->current(), 1, TERM_RED);
            println('old subject '.$systemproto->current(), 1, TERM_RED);
            println('load new proto doc by system proto (subject)'.$m, 1, TERM_RED);
            println($stageActor, 1, TERM_RED);
            continue;
        } else { // transfer
            $m = new Message();
            $m->action = 'update';
            $m->urn = $stageActor->urn;
            $m->processmodelprototype = $DefinitionProcessModelSystem->urn;
            $m->documentprototype = $documentProto->urn;
            $m->deliver();
            //println($m, 1, TERM_VIOLET);
        }
    }
    // up
    // throw new Exception("4 pending");
};
