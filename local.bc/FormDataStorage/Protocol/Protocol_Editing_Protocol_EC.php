<?php

$GLOBALS['LOAD_Protocol_Editing_Protocol_EC'] = $GLOBALS['LOAD_Protocol_Decision_Protocol_EC']= function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['commisionmembers_unit', 'boprocedure_unit']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Protocol_Editing_Protocol_EC']=$GLOBALS['SAVE_Protocol_Decision_Protocol_EC'] = function ($d)
{
    Log::info("SAVE_Protocol_Editing_Protocol_EC", 'unisave');

    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();

    $subjectURN = $d->urn;

    formDataManageListOfItemsIn($d, ['boprocedure', 'commisionmembers'], $subjectURN);

    //$ListOfEntityDir = ['commisionmembers' => 'Management:Post:Individual'];
    //formDataManageListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN);

    $s = new stdClass();
    $s->state = (string)$d->urn;

    return $s;
}

?>
