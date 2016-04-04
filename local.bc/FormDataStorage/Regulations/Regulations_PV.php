<?php

$GLOBALS['LOAD_Regulations_Editing_Regulations_PV'] =
$GLOBALS['LOAD_Regulations_Decision_Regulations_PV'] =
    function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['DirectoryResponsibleSimple','DirectoryMaterialbaseSimple','DirectoryOptionsSimple']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Editing_Regulations_PV'] = function ($d)
{
    Log::info("SAVE_Regulations_Editing_Regulations_PV", 'unisave');
    Log::info($d, 'unisave');

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();
    //Log::info($saved, 'unisave');

    $subjectURN = $d->urn;
    $document = 'DocumentRegulationsPV';

    //Ответственные
    $ListOfEntityDir = ['DirectoryResponsibleSimple' => 'Directory:Responsible:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN,$document);

    // Материальная база
    $ListOfEntityDir2 = ['DirectoryMaterialbaseSimple' => 'Directory:Materialbase:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir2, $subjectURN,$document);

    //Параметры
    $ListOfEntityDir3 = ['DirectoryOptionsSimple' => 'Directory:Options:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir3, $subjectURN,$document);


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
