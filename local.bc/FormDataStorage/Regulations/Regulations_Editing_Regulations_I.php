<?php

$GLOBALS['LOAD_Regulations_Editing_Regulations_I'] = $GLOBALS['LOAD_Regulations_Decision_Regulations_I'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['boprocedure_unit', 'userprocedure_unit', 'DirectoryAdditionalSectionSimple']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Editing_Regulations_I'] = function($d)
{
    Log::info("SAVE_Regulations_Editing_Regulations_I", 'unisave');

    Log::info($d, 'unisave');
    $m = new Message((array)$d);
    $m->action = "update";
    //Log::info($m, 'unisave');
    $saved = $m->deliver();


  /*  $ListOfEntityDir = ['boprocedure' => 'BusinessObject:Record:Polymorph'];
    $subjectURN = $d->urn;
    formDataManageListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN); */


    $subjectURN = $d->urn;

    formDataManageListOfItemsIn($d, ['boprocedure'], $subjectURN);
    formDataManageListOfItemsIn($d, ['userprocedure'], $subjectURN);

    $document = 'DocumentRegulationsI';
    $ListOfEntityDir = ['DirectoryAdditionalSectionSimple' => 'Directory:AdditionalSection:Simple'];
    formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN,$document);
    /*
   if($d->boprocedure) {
        $listURN = $saved->urn.':boprocedure';

        //удаление существующих
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($listURN);
        $listMembers = $m->deliver();

        Log::info($listMembers, 'unisave');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:BusinessObject:Record:Polymorph';
        $m->in = $listMembers;
        $resList = $m->deliver();

        foreach ($resList as $v) {
            $m = new Message();
            $m->action = 'remove';
            $m->urn = $v->urn;
            $m->from = new URN($listURN);
            $m->deliver();
        }

        //добавление выбраных
        foreach ($d->boprocedure as $boprocedure) {
            $m = new Message();
            $m->action = 'exists';
            $m->urn = $boprocedure;
            $m->in = $listURN;
            $e = $m->deliver();

            if (!$e->exists) {
                $m = new Message();
                $m->action = 'add';
                $m->urn = $boprocedure;
                $m->to = $listURN;
                $added = $m->deliver();
            } else
                Log::info($e, 'unisave');
        }
    }
*/
    /*
    if($d->userprocedure) {
        $listURN = $saved->urn.':userprocedure';

        //удаление существующих
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($listURN);
        $listMembers = $m->deliver();

        Log::info($listMembers, 'unisave');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Individual';
        $m->in = $listMembers;
        $resList = $m->deliver();

        foreach ($resList as $v) {
            $m = new Message();
            $m->action = 'remove';
            $m->urn = $v->urn;
            $m->from = new URN($listURN);
            $m->deliver();
        }

        //добавление выбраных
        foreach ($d->userprocedure as $userprocedure) {
            $m = new Message();
            $m->action = 'exists';
            $m->urn = $userprocedure;
            $m->in = $listURN;
            $e = $m->deliver();

            if (!$e->exists) {
                $m = new Message();
                $m->action = 'add';
                $m->urn = $userprocedure;
                $m->to = $listURN;
                $added = $m->deliver();
                //Log::info($added, 'unisave');
            } else
                Log::info($e, 'unisave');
        }
    }
*/
    /*
    foreach ($d->DirectoryAdditionalSectionSimple as $value){
        Log::info($value, 'unisave');
        $m = new Message((array)$value);
        if ($value->urn)
            $m->action = "update";
        else {
            $m->urn = 'urn:Directory:AdditionalSection:Simple';
            $m->action = "create";
        }
        $m->DocumentRegulationsI = $saved->urn;
        //Log::info($m, 'unisave');
        $done = $m->deliver();

        Log::info($done, 'unisave');
    }
*/



    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
