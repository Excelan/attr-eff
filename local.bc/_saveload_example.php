<?php
$GLOBALS['LOAD_'] = function ($urn)
{
    Log::info("LOAD_", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded)) {

        $d = $loaded->current()->toArray(['',''=>[''=>0,'']]);

        $Context_URN = new URN(key($d['???']));
        $Context = $Context_URN->resolve()->current();

        $d['CONTEXT_???'] = $Context->toArray();

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');

};
?>
