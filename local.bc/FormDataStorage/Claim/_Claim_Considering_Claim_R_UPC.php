<?php

// SAVE 2
$GLOBALS['SAVE_Claim_Editing_Claim_R_UPC'] = $GLOBALS['SAVE_Claim_Considering_Claim_R_UPC'] = function ($d, $ticketurn)
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

    formDataManageListOfItemsIn($d, ['skduser'], $subjectURN);
  }

  $s = new stdClass();
  $s->state = (string)$d->urn;
  return $s;

};

// LOAD 3+1
$GLOBALS['LOAD_Claim_Editing_Claim_R_UPC'] = $GLOBALS['LOAD_Claim_Decision_Claim_R_UPC'] = $GLOBALS['LOAD_Claim_Considering_Claim_R_UPC'] = function ($urn)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['solutionvariants','skduser_unit']); // SPEC
        $json = json_encode($d);
        return $json;
    }
};

$GLOBALS['LOAD_Claim_DoingTask_Claim_R_UPC'] = function ($urn)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['DocumentSolutionUniversal','skduser_unit']);
        $json = json_encode($d);
        return $json;
    }
};
?>
