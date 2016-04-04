<?php
extract($this->context);


$m = new Message();
$m->action = 'load';
$m->urn = 'urn:Document:Capa:Deviation:654835513';
$capa = $m->deliver();

$m = new Message();
$m->action = 'load';
$m->urn = 'urn:Document:Correction:Capa';
$m->DocumentCapaDeviation = (string)$capa->urn;
$corrections = $m->deliver();

foreach($corrections as $correction){

    $m = new Message();
    $m->action = 'update';
    $m->urn = (string)$correction->selectedsolution->urn;
    $m->approveded = 1;
    $m->deliver();

}