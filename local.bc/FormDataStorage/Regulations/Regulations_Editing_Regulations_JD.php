<?php

$GLOBALS['LOAD_Regulations_Editing_Regulations_JD'] = $GLOBALS['LOAD_Regulations_Decision_Regulations_JD'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['userprocedure_unit']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Editing_Regulations_JD'] = function ($d)
{
    Log::info("SAVE_Regulations_Editing_Regulations_JD", 'unisave');

    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();
    Log::info($saved, 'unisave');

    $subjectURN = $d->urn;

    formDataManageListOfItemsIn($d, ['userprocedure'], $subjectURN);

    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
