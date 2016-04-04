<?php

$GLOBALS['LOAD_Capa_Planning_Capa_Deviation'] = function ($urn,$managementrole)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();


    if (count($loaded))
    {
        $d = $loaded->current()->toArray();

        $json = json_encode($d);

        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Capa_Planning_Capa_Deviation'] = function ($d,$ticketurn)
{

    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
