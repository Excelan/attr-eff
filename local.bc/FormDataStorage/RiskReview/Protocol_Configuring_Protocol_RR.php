<?php

$GLOBALS['LOAD_Protocol_Configuring_Protocol_RR'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['commissionmember_unit']); // ['commisionmembers_unit']
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');

};


$GLOBALS['SAVE_Protocol_Configuring_Protocol_RR'] = function ($d)
{
    Log::info("SAVE_Protocol_Configuring_Protocol_RR", 'unisave');

    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();

    $subjectURN = $d->urn;

    formDataManageListOfItemsIn($d, ['commissionmember'], $subjectURN);

    /*
    $ListOfEntityDir = ['commisionmembers' => 'Management:Post:Individual'];
    $subjectURN = $d->urn;
    formDataManageListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN);
    */

    $s = new stdClass();
    $s->state = (string)$d->urn;

    return $s;

}
?>
