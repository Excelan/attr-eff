<?php

$GLOBALS['LOAD_Capa_Editing_Capa_Deviation'] = function ($urn)
{
    Log::info("LOAD_Capa_Editing_Capa_Deviation", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['RiskManagementRiskApproved_unit', 'RiskManagementRiskNotApproved', 'DocumentCorrectionCapa']);

        $cancelCapa = 0;//проверка капы на отмену из делегейтинг по мероприятих
        foreach ($d['DocumentCorrectionCapa'] as $key => $dcc)
        {
            if ($dcc['selecttype'] == 1) {
                $cancelCapa++;
                break;
            }
        }

        if($cancelCapa > 0) {

            Log::error("Капа попала в Едититинг из отмены из визирования или апрувинга!", 'capa');
            Log::error("Выводим только отменненые пункты .....", 'capa');

            $newDCC = [];
            foreach ($d['DocumentCorrectionCapa'] as $key => $dcc) {
                if ($dcc['cancelstat'] == 1)
                    $newDCC[] = $dcc;
            }
            $d['DocumentCorrectionCapa'] = $newDCC;

            Log::error("...... Готово", 'capa');
        }

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Capa_Editing_Capa_Deviation'] = function ($d,$ticket)
{
    Log::info("SAVE_Capa_Editing_Capa_Deviation", 'unisave');

    /**
     При отклонении с визирования или апрвувинга
     в VisingIn и в AppOut сразу проставляем статуси отмены и статус рассмотрений в ноль
     */

    foreach($d->RiskManagementRiskNotApproved as $nrisk){

        $nrc = 0;
        if(strlen(trim($nrisk->BusinessObjectRecordPolymorph)) == 0){
            $nrc++;
        }

        if($nrisk->DirectoryBusinessProcessItem == 'NULL'){
            $nrc++;
        }

        if($nrc == 2 || $nrc == 0) {
            $state = new stdClass();
            $state->state = 200;
            $state->nextstage = 404;
            $state->text = "Ошибка! Для не идентифицированного риска выберите или 'Объект' или 'Процесс'";
            return $state;
        }
    }


    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();

    $subjectURN = $d->urn;
    formDataManageListOfItemsIn($d, ['RiskManagementRiskApproved'], $subjectURN);

    $ListOfEntityDir = ['RiskManagementRiskNotApproved' => 'RiskManagement:Risk:NotApproved'];
    $document = 'documentoforigin';
    formDataManageHasmanyListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN, $document);


    Log::debug('cохранение мероприятий ......', 'capa');

    foreach ($d->DocumentCorrectionCapa as $correctionevent)
    {
        $m = new Message((array)$correctionevent);
        if ($correctionevent->urn)
            $m->action = "update";
        else {
            $m->urn = 'urn:Document:Correction:Capa';
            $m->action = "create";
        }

        if($correctionevent->cancelstat == true) $m->cancelstat = 1;
        else $m->cancelstat = 0;


        $m->DocumentCapaDeviation = $saved->urn;
        $createdCorrectionEvent = $m->deliver();

        Log::debug('......', 'capa');

    }
    Log::debug('....... сохранено!!!', 'capa');

    $state = new stdClass();
    $state->state = (string)$saved->urn;
    $state->nextstage = 1;
    return $state;

}

?>
