<?php

// SAVE 2
$GLOBALS['SAVE_Claim_Editing_Claim_R_PAI'] = $GLOBALS['SAVE_Claim_Considering_Claim_R_PAI'] = function ($d, $ticketurn)
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

      formDataManageListOfItemsIn($d, ['employees'], $subjectURN);
    }

    $s = new stdClass();
    $s->state = (string)$d->urn;
    return $s;
};

// SAVE 3+1
$GLOBALS['LOAD_Claim_Editing_Claim_R_PAI'] = $GLOBALS['LOAD_Claim_Decision_Claim_R_PAI'] = $GLOBALS['LOAD_Claim_Considering_Claim_R_PAI'] = function ($urn)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['solutionvariants','employees_unit']); // SPEC ,'warehouse'=>0
        $json = json_encode($d);
        return $json;
    }
};

$GLOBALS['LOAD_Claim_DoingTask_Claim_R_PAI'] = function ($urn)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['DocumentSolutionUniversal', 'employees_unit']);
        $json = json_encode($d);
        return $json;
    }
};
?>
