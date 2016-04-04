<?php

/*$GLOBALS['LOAD_Staffdoc_Editing_Staffdoc_SD'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Staffdoc_Editing_Staffdoc_SD'] = function ($d)
{
    Log::info("SAVE_Staffdoc_Editing_Staffdoc_SD+-", 'unisave');

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
