<?php

$GLOBALS['SAVE_TechnicalTask_Editing_TechnicalTask_ForMaterials'] =
$GLOBALS['SAVE_TechnicalTask_Editing_TechnicalTask_ForWorks'] =
    function ($d) {
        $urn = new URN($d->urn);
        $code = $urn->getPrototype()->getOfType();
        Log::info("SAVE_TechnicalTask_Editing_TechnicalTask_{$code} {$d->urn}", 'unisave');

        $subjectURN = $d->urn;

        $m = new Message((array)$d);
        $m->action = "update";
        $savedSubject = $m->deliver();

        formDataManageListOfItemsIn($d, ['CompanyLegalEntityCounterparty'], $subjectURN);

        if ($code == 'ForMaterials') {
          $ListOfEntityDir = ['DirectoryTechnicalTaskMaterials' => 'Directory:TechnicalTask:Materials'];

          formDataManageListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN);
        }

        foreach ($d->DirectoryTechnicalTaskForWorks as $value){

            $m = new Message((array)$value);
            if ($value->urn)
                $m->action = "update";
            else {
                $m->urn = 'urn:Directory:TechnicalTask:ForWorks';
                $m->action = "create";
            }
            $m->DocumentTechnicalTaskForWorks = (string)$savedSubject->urn;
            $done = $m->deliver();
        }


        $s = new stdClass();
        $s->state = (string)$savedSubject->urn;

        return $s;

    };

// Document:TechnicalTask:ForMaterials
$GLOBALS['LOAD_TechnicalTask_Editing_TechnicalTask_ForMaterials'] =
$GLOBALS['LOAD_TechnicalTask_Editing_TechnicalTask_ForWorks'] =
$GLOBALS['LOAD_TechnicalTask_Decision_TechnicalTask_ForMaterials'] =
$GLOBALS['LOAD_TechnicalTask_Decision_TechnicalTask_ForWorks'] =
    function ($urn) {
        $code = $urn->getPrototype()->getOfType();
        Log::info("LOAD_TechnicalTask_Editing_TechnicalTask_{$code} {$urn}", 'uniload');

        $m = new Message();
        $m->action = "load";
        $m->urn = $urn;
        $loaded = $m->deliver();

        if (count($loaded)) {
            $d = $loaded->current()->toArray(['CompanyLegalEntityCounterparty_unit','DirectoryTechnicalTaskMaterials','DirectoryTechnicalTaskForWorks']);

            $json = json_encode($d);
            Log::debug($json, 'uniload');
            return $json;
        } else
            Log::error("No data loaded for $urn", 'uniload');
    };

?>
