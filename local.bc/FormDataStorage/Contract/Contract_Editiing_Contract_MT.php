<?php

$GLOBALS['LOAD_Contract_Editing_Contract_MT']=$GLOBALS['LOAD_Contract_Considering_Contract_MT']=$GLOBALS['LOAD_Contract_Decision_Contract_MT']=$GLOBALS['LOAD_Contract_Approving_Contract_MT'] = function ($urn)
{
  Log::info("LOAD_Contract_Editing_Contract_MT", 'uniload');
  Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

  $m = new Message();
  $m->action = "load";
  $m->urn = $urn;
  $loaded = $m->deliver();

  if (count($loaded))
  {
    $d = $loaded->current()->toArray(['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']]);   // 'responsible'=>['CompanyStructureDepartment'] ,'deviations'=>['approvedrisks_unit','notapprovedrisks']
    $json = json_encode($d);
    Log::debug($json, 'uniload');
    return $json;
  }
  else
  Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Contract_Editing_Contract_MT'] = $GLOBALS['SAVE_Contract_Considering_Contract_MT']=$GLOBALS['SAVE_Contract_Decision_Contract_MT']=$GLOBALS['SAVE_Contract_Approving_Contract_MT'] =function ($d)
{
  Log::info("XX SAVE_Contract_Editing_Contract_MT", 'unisave');

  $m = new Message((array)$d);
  $m->urn = $d->urn;
  $m->action = "update";
  //$m->descriptiondeviation = $d->deviation->descriptiondeviation;
  $contact_saved = $m->deliver();


  $subjectURN = $d->urn;
  formDataManageListOfItemsIn($d, ['notifyusercompany'], $subjectURN);


  foreach ($d->contractapplication as $contractapp)
  {
    Log::info("! contractapp", 'unisave');
    Log::info($contractapp, 'unisave');
    $m = new Message((array)$contractapp);
    if ($contractapp->urn)
    $m->action = "update";
    else {
      $m->urn = 'urn:Document:ContractApplication:Universal';
      $m->action = "create";
    }
    $conapp_saved = $m->deliver();

    $listURN = $contact_saved->urn.':contractapplication';

    //  $narisk = $m->deliver();
    // TODO add if created
    //  Log::info($m, 'unisave');
    $m = new Message();
    $m->action = 'exists';
    $m->urn = $conapp_saved->urn;
    $m->in = $listURN;
    Log::debug($m, 'unisave');
    $e = $m->deliver();

    if (!$e->exists)
    {
      $m = new Message();
      $m->action = 'add';
      $m->urn = $conapp_saved->urn;
      $m->to = $listURN;
      Log::info($m, 'unisave');
      $added = $m->deliver();
      //Log::info($added, 'unisave');
    }

    foreach ($contractapp->MediaAttributed as $MediaAttributed)
    {
      Log::info("!! MediaAttributed", 'unisave');
      Log::info($MediaAttributed, 'unisave');
      $m = new Message((array)$MediaAttributed);
      if ($MediaAttributed->urn)
      $m->action = "update";
      else {
        $m->urn = 'urn:Directory:Media:Attributed';
        $m->action = "create";
      }
      $saved_mediaattr = $m->deliver();

      $listURN = $conapp_saved->urn.':MediaAttributed';

      //  $narisk = $m->deliver();
      // TODO add if created
      //  Log::info($m, 'unisave');
      $m = new Message();
      $m->action = 'exists';
      $m->urn = $saved_mediaattr->urn;
      $m->in = $listURN;
      Log::debug($m, 'unisave');
      $e = $m->deliver();

      if (!$e->exists)
      {
        $m = new Message();
        $m->action = 'add';
        $m->urn = $saved_mediaattr->urn;
        $m->to = $listURN;
        Log::info($m, 'unisave');
        $added = $m->deliver();
        //Log::info($added, 'unisave');
      }

    }

  }

  $state = new stdClass();
  $state->state = $saved->urn;
  return $state;
}
?>
