<?php

$GLOBALS['LOAD_Protocol_Configuring_Protocol_RUKD'] = $GLOBALS['LOAD_Protocol_Planning_Protocol_RUKD'] = $GLOBALS['LOAD_Protocol_Print_Protocol_RUKD'] = $GLOBALS['LOAD_Protocol_Issue_Protocol_RUKD'] =
    function ($urn) {

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded)) {
        $d = $loaded->current()->toArray(['plannedreceivers_unit', 'DirectoryUKDStateIssueRecord']);

        //$d['CONTEXT_BusinessObjectRecordPolymorph'] = $d['bo'];

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    } else {
        Log::error("No data loaded for $urn", 'uniload');
    }
};


$GLOBALS['SAVE_Protocol_Configuring_Protocol_RUKD'] =
    function ($d) {
    Log::info($d, 'unisave');

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();

    $subjectURN = $d->urn;

    formDataManageListOfItemsIn($d, ['plannedreceivers'], $subjectURN);

    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;
};

$GLOBALS['SAVE_Protocol_Issue_Protocol_RUKD'] =
    function ($d) {

    Log::info($d, 'unisave');

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();

    $subjectURN = $d->urn;
    $document = 'DocumentProtocolRUKD';

    $ListOfEntityDir = ['DirectoryUKDStateIssueRecord' => 'Directory:UKDState:IssueRecord'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN, $document);

    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;
};
