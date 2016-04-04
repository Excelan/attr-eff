<?php

$GLOBALS['LOAD_Regulations_Decision_Regulations_TA'] = function ($urn)
{
    //Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');
    Log::debug('-----------------------------'.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['StudyRegulationStudyQ'=>['StudyRegulationStudyA']]);

        $json = json_encode($d);
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Decision_Regulations_TA'] = function ($d)
{
    //Log::info("SAVE_Regulations_Editing_Regulations_TA", 'unisave');

    //Log::info((array)$d, 'unisave');


    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();





    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
