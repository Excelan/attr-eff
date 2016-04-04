<?php

$GLOBALS['LOAD_Protocol_Rating_Protocol_RR'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniloadsave');

    // TOOD по протоколу

    // риски грузить по объекту или процессу
    $protocol = $urn->resolve()->current();
    Log::debug((string) $protocol, 'uniloadsave');
    $bprocess = $protocol->DirectoryBusinessProcessItem;
    Log::debug((string) $bprocess, 'uniloadsave');
    $bobject = $protocol->BusinessObjectRecordPolymorph;
    Log::debug((string) $bobject, 'uniloadsave');

    $d = [];
    $d['eachrisk'] = [];

    $m = new Message();
    $m->action = "load";
    $m->urn = 'urn:RiskManagement:Risk:Approved';
    if (count($bprocess))
      $m->DirectoryBusinessProcessItem = $bprocess->urn; //
    if (count($bobject))
      $m->BusinessObjectRecordPolymorph = $bobject->urn; //
    Log::debug((string) $m, 'uniloadsave');
    $allloaded = $m->deliver();

    foreach ($allloaded as $loaded)
    {
        $d['eachrisk'][] = $loaded->toArray();
    }

    $d['urn'] = (string) $urn;
    $d['DirectoryBusinessProcessItem'] = $protocol->toArray()['DirectoryBusinessProcessItem'];
    $d['BusinessObjectRecordPolymorph'] = $protocol->toArray()['BusinessObjectRecordPolymorph'];

    $json = json_encode($d);
    Log::debug($json, 'uniloadsave');
    return $json;

};


$GLOBALS['SAVE_Protocol_Rating_Protocol_RR'] = function ($d)
{
    Log::info("SAVE_Protocol_Rating_Protocol_RR", 'uniloadsave');

    foreach ($d->eachrisk as $riska)
    {
      $m = new Message();
      $m->urn = $riska->urn; //
      $m->action = "update";
      $m->producteffect = $riska->producteffect; //
      $m->producteffect = $riska->producteffect; //
      $m->emergenceprobability = $riska->emergenceprobability; //
      $m->undetectedprobability = $riska->undetectedprobability; //
      Log::debug((string) $m, 'uniloadsave');
      $m->deliver();
    }

    $s = new stdClass();
    $s->state = (string)$d->urn;

    return $s;

}
?>
