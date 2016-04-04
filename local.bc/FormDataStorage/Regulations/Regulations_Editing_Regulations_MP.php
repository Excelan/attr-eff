<?php

$GLOBALS['LOAD_Regulations_Editing_Regulations_MP'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['all']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Editing_Regulations_MP'] = function($d)
{
  Log::info("XX Save Regulaions_Editing_Regulations_MP", 'unisave');
  Log::info($d, "unisave");
  Log::info($d->all, 'unisave');

    Log::info("SAVE_Regulations_Editing_Regulations_MP", 'unisave');
    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();
    Log::info($d, 'unisave');


    $subjectURN = $d->urn;
    $document = 'DocumentRegulationsMP';
    $ListOfEntityDir = ['DirectoryCalendarPlanSimple' => 'Directory:CalendarPlan:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN,$document);


    $s = new stdClass();
    $s->state = (string)$d->urn;
    return $s;
  }

  ?>
