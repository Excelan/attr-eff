<?php
// Византы и откуда берется перенести.
// Add-Remove
// TODO
/**
$m = new Message();
$m->action = 'load';
$m->urn = 'urn:Definition:Prototype:Document';
$docProtos = $m->deliver();
foreach ($docProtos as $documentProto) {
    foreach ($documentProto->visants as $visant) {
        $m = new Message();
        $m->action = 'remove';
        $m->urn = $visant->urn;
        $m->from = $documentProto->urn.':visants';
        $m->deliver();

        $m = new Message();
        $m->action = 'add';
        $m->urn = $visant->urn;
        $m->to = $documentProto->urn.':visants';
        $m->deliver();
    }
}
*/;
