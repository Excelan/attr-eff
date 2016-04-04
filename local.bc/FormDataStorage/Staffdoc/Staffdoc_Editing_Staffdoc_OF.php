<?php

/*$GLOBALS['LOAD_Staffdoc_Editing_Staffdoc_OF'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['commisionmembers_unit']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Staffdoc_Editing_Staffdoc_OF'] = function ($d)
{
    Log::info("SAVE_Protocol_Editing_Protocol_EA", 'unisave');

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();
    Log::info($saved, 'unisave');


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}
 */
?>
