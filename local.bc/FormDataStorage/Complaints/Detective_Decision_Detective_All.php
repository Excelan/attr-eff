<?php

$GLOBALS['LOAD_Detective_Decision_Detective_C_IS'] =
$GLOBALS['LOAD_Detective_Decision_Detective_C_IV'] =
$GLOBALS['LOAD_Detective_Decision_Detective_C_IW'] =
$GLOBALS['LOAD_Detective_Decision_Detective_C_LB'] =
$GLOBALS['LOAD_Detective_Decision_Detective_C_LC'] =
$GLOBALS['LOAD_Detective_Decision_Detective_C_LP'] =
$GLOBALS['LOAD_Detective_Decision_Detective_C_LT']=
function ($urn)
{
    Log::info((string)$urn, 'uniload');
    $code = $urn->getPrototype()->getOfType();
    Log::info("LOAD_Detective_Decision_Detective_{$code}", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']]);

        //$DocumentComplaint_Par_URN = new URN(key($d['DocumentComplaint'.$code]));
        $DocumentComplaint_Par_URN = new URN($d['DocumentComplaint'.$code]['urn']);
        $DocumentComplaintPar = $DocumentComplaint_Par_URN->resolve()->current();
        $d['CONTEXT_DocumentComplaint'.$code] = $DocumentComplaintPar->toArray();

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

?>
