<?php

$GLOBALS['LOAD_Regulations_Editing_Regulations_SOP']=$GLOBALS['LOAD_Regulations_Decision_Regulations_SOP'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['boprocedure_unit','userprocedure_unit',  'userproceduregroup_unit','DirectoryAdditionalSectionSimple']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Editing_Regulations_SOP'] = $GLOBALS['SAVE_Regulations_Decision_Regulations_SOP'] = function ($d)
{
    Log::info("SAVE_Regulations_Editing_Regulations_SOP", 'unisave');
    Log::info("----------------------------", 'unisave');
    Log::info($d, 'unisave');
    Log::info("----------------------------", 'unisave');
    //Log::info($d, 'unisave');


    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();


    // Объекты процедуры
    $listURN = $saved->urn.':boprocedure';



    $subjectURN = $d->urn;


    formDataManageListOfItemsIn($d, ['boprocedure'], $subjectURN);
    formDataManageListOfItemsIn($d, ['userprocedure'], $subjectURN);
    formDataManageListOfItemsIn($d, ['userproceduregroup'], $subjectURN);//todo multiple select

    $document = 'DocumentRegulationsSOP';
    $ListOfEntityDir = ['DirectoryAdditionalSectionSimple' => 'Directory:AdditionalSection:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN, $document);

    // DMSDocumentUniversal


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>