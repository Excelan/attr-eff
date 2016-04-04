<?php

$GLOBALS['LOAD_Protocol_Editing_Protocol_SI'] =
$GLOBALS['LOAD_Protocol_Vising_Protocol_SI']=
$GLOBALS['LOAD_Protocol_Approving_Protocol_SI']=
    function ($urn)
{
    Log::info("LOAD_Protocol_Editing_Protocol_SI", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['riskselfinspection']);   // 'responsible'=>['CompanyStructureDepartment'] ,'deviations'=>['approvedrisks_unit','notapprovedrisks']
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Protocol_Editing_Protocol_SI']=$GLOBALS['SAVE_Protocol_Vising_Protocol_SI']= $GLOBALS['SAVE_Protocol_Approving_Protocol_SI'] = function ($d)
{
    Log::info("XX SAVE_Protocol_Editing_Protocol_SI", 'unisave');

    Log::info($d->riskselfinspection, 'unisave');

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();

       foreach ($d->riskselfinspection as $result)
        {
          foreach ($result->DirectoryRiskProtocolSolutionSI as $k) {
          $m = new Message((array)$k);
        if ($k->urn)
            $m->action = "update";
        else {
            $m->urn = "urn:Directory:RiskProtocolSolution:SI";
            $m->action = "create";
        }
    //    $m->riskselfinspection=$saved->urn;
        $done = $m->deliver();
        }
      }

  Log::info($m, 'unisave');

    


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;
}
?>
