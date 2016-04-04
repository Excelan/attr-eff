<?php

$GLOBALS['LOAD_Protocol_Editing_Protocol_VT'] =
$GLOBALS['LOAD_Protocol_Decision_Protocol_VT']=
$GLOBALS['LOAD_Protocol_Approving_Protocol_VT']=
    function ($urn)
{
    Log::info("LOAD_Protocol_Editing_Protocol_VT", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['bo','DirectoryResponsibletwoSimple','DirectoryFixedassetSimple']);

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


$GLOBALS['SAVE_Protocol_Editing_Protocol_VT'] =
$GLOBALS['SAVE_Protocol_Decision_Protocol_VT']=
$GLOBALS['SAVE_Protocol_Approving_Protocol_VT'] =
    function ($d)
{
    Log::info("XX SAVE_Protocol_Editing_Protocol_VT", 'unisave');
    Log::info($d, 'unisave');
    Log::info($d->DirectoryFixedassetSimple, 'unisave');

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();

    $subjectURN = $d->urn;
    $document = 'DocumentProtocolVT';

    $ListOfEntityDir = ['DirectoryResponsibletwoSimple' => 'Directory:Responsibletwo:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN, $document);

    $ListOfEntityDir2 = ['DirectoryFixedassetSimple' => 'Directory:Fixedasset:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir2, $subjectURN, $document);


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;
}
?>
