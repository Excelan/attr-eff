<?php

$GLOBALS['LOAD_Protocol_Considering_Protocol_RR'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    // риски грузить по объекту или процессу
    $protocol = $urn->resolve()->current();
    Log::debug((string) $protocol, 'uniload');
    $bprocess = $protocol->DirectoryBusinessProcessItem;
    Log::debug((string) $bprocess, 'uniload');
    $bobject = $protocol->BusinessObjectRecordPolymorph;
    Log::debug((string) $bobject, 'uniload');

    $d = [];
    $d['eachrisk'] = [];

    $m = new Message();
    $m->action = "load";
    $m->urn = 'urn:RiskManagement:Risk:NotApproved';
    if (count($bprocess))
      $m->DirectoryBusinessProcessItem = $bprocess->urn; //
    if (count($bobject))
      $m->BusinessObjectRecordPolymorph = $bobject->urn; //
    Log::debug((string) $m, 'uniload');
    $allloaded = $m->deliver();

    foreach ($allloaded as $loaded)
    {
        $d['eachrisk'][] = ['urn'=>(string)$loaded->urn, 'RiskManagementRiskNotApproved_CONTEXT'=>$loaded->toArray(), 'createnew' => ['refineplace' => ['DirectoryBusinessProcessItem' => $loaded->toArray()['DirectoryBusinessProcessItem'], 'BusinessObjectRecordPolymorph' => $loaded->toArray()['BusinessObjectRecordPolymorph'] ] ] ];
    }

    $d['urn'] = (string) $urn;

    $json = json_encode($d);
    Log::debug($json, 'uniload');
    return $json;

};


$GLOBALS['SAVE_Protocol_Considering_Protocol_RR'] = function ($d)
{
    Log::info("SAVE_Protocol_Considering_Protocol_RR", 'unisave');

    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();
    $subjectURN = $d->urn;

    Log::info($d, 'uniloadsave');

    foreach ($d->eachrisk as $riskna)
    {
      if ($linkWithRisk = $riskna->linkwith->RiskManagementRiskApproved) // link with risk
      {
        Log::debug('LINK WITH ', 'uniloadsave');
        $m = new Message();
        $m->urn = $riskna->urn;
        $m->identified = true; //
        $m->RiskManagementRiskApproved = $linkWithRisk; //
        $m->action = "update";
        Log::debug((string) $m, 'uniloadsave');
        $m->deliver();
      }
      elseif ($createNewRisk = $riskna->createnew) // create new risk
      {
        Log::debug('CREATE NEW ', 'uniloadsave');
        $m = new Message();
        $m->urn = 'urn:RiskManagement:Risk:Approved';
        $m->action = "create";
        $m->title = $createNewRisk->title; //
        $m->DirectorySLAItem = $createNewRisk->DirectorySLAItem; //
        if ($createNewRisk->refineplace->BusinessObjectRecordPolymorph)
        {
          $m->BusinessObjectRecordPolymorph = $createNewRisk->refineplace->BusinessObjectRecordPolymorph;
        }
        elseif ($createNewRisk->refineplace->DirectoryBusinessProcessItem)
        {
          $m->DirectoryBusinessProcessItem = $createNewRisk->refineplace->DirectoryBusinessProcessItem;
        }
        $newriskcreateresult = Log::debug((string) $m, 'uniloadsave');
        $m->deliver();
        // link with newly created risk
        $m = new Message();
        $m->urn = $riskna->urn; //
        $m->identified = true; //
        $m->RiskManagementRiskApproved = $newriskcreateresult->urn; //
        $m->action = "update";
        Log::debug((string) $m, 'uniloadsave');
        $m->deliver();
      }
      else {
        Log::error('NON NEW NON LINK', 'uniloadsave');
      }
    }

    $s = new stdClass();
    $s->state = (string)$d->urn;
    return $s;

}
?>
