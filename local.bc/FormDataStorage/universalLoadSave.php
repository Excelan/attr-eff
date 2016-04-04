<?php

//$SAVE_Complaints_editing_Complaint_C_IS = function ($d)
$GLOBALS['SAVE_FALLBACK'] = function ($d)
{
    Log::info("SAVE_FALLBACK", 'uniloadsave');
    Log::debug($d, 'uniloadsave');

    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();

    $state = new stdClass();
    $state->state = $saveState->urn;
    return $state;

};

//$LOAD_Complaints_editing_Complaint_C_IS = function ()
$GLOBALS['LOAD_FALLBACK'] = function($urn)
{
    Log::info("LOAD_FALLBACK", 'uniloadsave');
    Log::debug("LOADED PROMISE {$urn}", 'uniloadsave');

    if ($urn) {
        $m = new Message();
        $m->action = "load";
        $m->urn = $urn;
        $loaded = $m->deliver();
        if (count($loaded)) {
            $json = $loaded->current()->toJSON();
            Log::debug((string)$loaded->current(), 'uniloadsave');
            Log::debug($json, 'uniloadsave');
            return $json;
        }
        else
            Log::error("No data loaded for $urn", 'uniloadsave');
    }

    Log::error("DEFAULT data loaded, no URN provided", 'uniloadsave');

    // !!!

    $d = new stdClass();
    //$d->datestart = '2015-12-12';
    //$d->actual = 'yes';
    //$d->description = "Text some<br>\nnext line";
    //$d->CompanyLegalEntityCounterparty = ["urn:Company:LegalEntity:Counterparty:123" => "Клиент Тест"];

    return $d;
};

?>
