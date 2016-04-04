<?php

function aquire_initiators($tuple, $key)
{
    list($listURN, $d) = $tuple;

    $docProtoUrn = new URN('urn:'.$listURN->prototype.':'.$listURN->uuid);
    $docProto = $docProtoUrn->resolve();
    if (!count($docProto)) {
        throw new Exception("Cant load $docProtoUrn");
    }
    $systemProto = $docProto->DefinitionPrototypeSystem;
    if (!count($systemProto)) {
        throw new Exception("Cant load docProtoUrn->DefinitionPrototypeSystem for $docProto->urn");
    }

    Log::info((string)$listURN, 'list');
    // Log::info((string)$listURN->prototype, 'list');
    // Log::info((string)$listURN->uuid, 'list');
    // Log::info((string)$listURN->uuid, 'list');
    // Log::info($d, 'list');
    // Log::debug($key, 'list');
    Log::info((string)$docProtoUrn, 'list');
    Log::info((string)$systemProto, 'list');

    $m = new Message();
    $m->action = 'create';
    $m->urn = 'urn:RBAC:Permission:Atomic';
    $m->actor = 'urn:Management:Post:Individual:'.$d;
    $m->cando = 'ProcessStart';
    $m->withprototype = $systemProto->urn;
    Log::debug((string)$m, 'list');
    $r = $m->deliver();
    Log::debug((string)$r, 'list');
}

$broker = Broker::instance();
$broker->queue_declare("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "list.add.Definition:Prototype:Document.initiators");
$broker->bind_rpc("ENITYCONSUMER", "aquire_initiators");


function removed_initiators($tuple, $key)
{
    list($listURN, $d) = $tuple;

    $docProtoUrn = new URN('urn:'.$listURN->prototype.':'.$listURN->uuid);
    $docProto = $docProtoUrn->resolve();
    if (!count($docProto)) {
        throw new Exception("Cant load $docProtoUrn");
    }
    $systemProto = $docProto->DefinitionPrototypeSystem;
    if (!count($systemProto)) {
        throw new Exception("Cant load docProtoUrn->DefinitionPrototypeSystem for $docProto->urn");
    }

    Log::info((string)$listURN, 'list');
    // Log::info($d, 'list');
    // Log::debug($key, 'list');
    Log::info((string)$docProtoUrn, 'list');
    Log::info((string)$systemProto, 'list');

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RBAC:Permission:Atomic';
    $m->actor = 'urn:Management:Post:Individual:'.$d;
    $m->cando = 'ProcessStart';
    $m->withprototype = $systemProto->urn;
    Log::debug((string)$m, 'list');
    $perms = $m->deliver();
    foreach ($perms as $perm) {
        $m = new Message();
        $m->action = 'delete';
        $m->urn = $perm->urn;
        $r = $m->deliver();
        Log::debug((string)$m, 'list');
        Log::debug((string)$r, 'list');
    }
}

$broker = Broker::instance();
$broker->queue_declare("ENITYCONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYCONSUMER", "list.remove.Definition:Prototype:Document.initiators");
$broker->bind_rpc("ENITYCONSUMER", "removed_initiators");
