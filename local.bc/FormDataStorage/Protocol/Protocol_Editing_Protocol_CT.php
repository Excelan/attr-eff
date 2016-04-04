<?php

$GLOBALS['LOAD_Protocol_Editing_Protocol_CT'] =  $GLOBALS['LOAD_Protocol_Decision_Protocol_CT']= $GLOBALS['LOAD_Protocol_Approving_Protocol_CT']=function ($urn, $forlatex)
{
    Log::info("LOAD_Protocol_Editing_Protocol_CT", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        if ($forlatex)
          $d = $loaded->current()->toArray(['bo', 0, ['richtext'=>'aslatex']]);
        else
          $d = $loaded->current()->toArray(['bo']);

        $d['CONTEXT_BusinessObjectRecordPolymorph'] = $d['bo'];
        $d['CONTEXT_BusinessObjectRecordPolymorph']['bo'] = $d['bo'];
        $d['CONTEXT_BusinessObjectRecordPolymorph']['location'] = $d['bo']['location'];

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

/*
$GLOBALS['SAVE_Protocol_Editing_Protocol_CT']=$GLOBALS['SAVE_Protocol_Decision_Protocol_CT']= $GLOBALS['SAVE_Protocol_Approving_Protocol_CT'] = function ($d)
{
    Log::info("SAVE_Protocol_Editing_Protocol_CT", 'unisave');
    Log::info($d, 'unisave');

    $subjectURN = $d->urn;

    $m = new Message((array)$d);
    $m->action = "update";
    $savedSubject = $m->deliver();

    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;
}
*/
?>
