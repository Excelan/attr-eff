<?php

/*  $GLOBALS['LOAD_Regulations_Decision_Regulations_SOP'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['boprocedure_unit','userprocedure_unit','DirectoryAdditionalSectionSimple']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Decision_Regulations_SOP'] = function ($d)
{
    Log::info("SAVE_Regulations_Editing_Regulations_SOP", 'unisave');
    //Log::info($d, 'unisave');


    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}
 */
?>
