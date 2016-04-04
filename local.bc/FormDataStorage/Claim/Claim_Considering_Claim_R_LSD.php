<?php

$GLOBALS['SAVE_Claim_Considering_Claim_R_LSD'] = function ($d, $ticketurn)
{
  // TODO $ticketurn to MPE
  if ($d->rtaken == 'notmyresp')
  {
    updateMPEMetadata(getInMPEByTicketURN($ticketurn), ['consideringdecision'=>'cancel']);
    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();
  }
  else
  {
    updateMPEMetadata(getInMPEByTicketURN($ticketurn), ['consideringdecision'=>'taken']);

    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();

    $ListOfEntityDir = ['solutionvariants' => 'Document:Solution:Universal'];
    $subjectURN = $d->urn;
    formDataManageListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN);

  }

  $s = new stdClass();
  $s->state = (string)$d->urn;
  return $s;

};


$GLOBALS['LOAD_Claim_Decision_Claim_R_LSD'] = $GLOBALS['LOAD_Claim_Considering_Claim_R_LSD'] = function ($urn)
{
    Log::info("LOAD_Claim_Considering_Claim_R_LSD", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded)) {

        $d = $loaded->current()->toArray(['solutionvariants']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');

};

$GLOBALS['LOAD_Claim_DoingTask_Claim_R_LSD'] = function ($urn)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['DocumentSolutionUniversal']);
        $json = json_encode($d);
        return $json;
    }
};
?>
