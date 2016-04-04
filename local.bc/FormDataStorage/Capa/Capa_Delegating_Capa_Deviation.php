<?php

$GLOBALS['LOAD_Capa_Delegating_Capa_Deviation'] = function ($urn, $managementrole)
{
    Log::info("LOAD_Capa_Delegating_Capa_Deviation", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    /*
    $m = new Message();
    $m->urn = 'urn:Document:Correction:Capa';
    $m->action = "load";
    $m->controlresponsible =
    $DocumentCorrectionCapa_ds = $m->deliver();
    */

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['DocumentCorrectionCapa','RiskManagementRiskNotApproved','RiskManagementRiskApproved_unit']);   // 'responsible'=>['CompanyStructureDepartment'] ,'deviations'=>['approvedrisks_unit','notapprovedrisks']

        $newDCC = [];
        foreach ($d['DocumentCorrectionCapa'] as $key => $dcc)
        {
          if ($dcc['controlresponsible']['urn'] == (string)$managementrole->urn)
            array_push($newDCC, $dcc);

        }
        $d['DocumentCorrectionCapa'] = $newDCC;

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Capa_Delegating_Capa_Deviation'] = function ($d,$ticketurn)
{
    Log::info("SAVE_Capa_Delegating_Capa_Deviation", 'unisave');

    $cc = 0;
    foreach ($d->DocumentCorrectionCapa as $correctionevent){

        Log::info('Выбраное значение = '.$correctionevent->realizationtype.'!', 'capa');
        if ($correctionevent->realizationtype == 'NULL'){
            $cc++;
        }
    }

    Log::info('Общее количество требуемых решений= '.count($d->DocumentCorrectionCapa), 'capa');
    Log::info('Не выбрано = '.$cc, 'capa');

    if($cc > 0){

        //не все варианты выбрано
        $state = new stdClass();
        $state->state = (string)$d->urn;
        $state->nextstage = 404;
        $state->text = "Не выбрано всех решений! Сделайте выбор!";
        return $state;
    }




    $nextstage = 0;

    $m = new Message((array)$d);
    $m->urn = $d->urn;
    $m->action = "update";
    $saved = $m->deliver();

    //получаем тикет
    $m = new Message();
    $m->action = 'load';
    $m->urn = $ticketurn;
    $ticket = $m->deliver();

    //Закрываем тикет
    $m = new Message();
    $m->action = 'update';
    $m->urn = $ticketurn;
    $m->isvalid = false;
    //закрытие тикеты сразу по посхранению
    // по сути даже если была отмена по одному из тикетов-другие тикеты остаются активными и рещения по них
    //не нуждаются в повторном выборе
    //todo проверить на практике действительно ли так =)
    $m->deliver();

    //получаем mpe
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:ManagedProcess:Execution:Record';
    $m->id = (string)$ticket->ManagedProcessExecutionRecord->id;
    $mpe = $m->deliver();


    //получем емплоее для записи комментария
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:People:Employee:Internal';
    $m->ManagementPostIndividual = (string)$ticket->ManagementPostIndividual->urn;
    $employee = $m->deliver();


    //обновляем полученные данные по мероприятиях
    $num = 0;
    foreach ($d->DocumentCorrectionCapa as $correctionevent)
    {

        $m = new Message((array)$correctionevent);
        $m->action = "update";
        $m->selecttype = 1;//отметка о том, что данное мероприятие было рассмотрено (независисмо отклонено или подтверждено)
        $m->deliver();

        if ($correctionevent->realizationtype == 'notmyresp'){
            //запись комантария отмены

            $m = new Message();
            $m->action = "update";
            $m->urn = (string)$correctionevent->urn;
            $m->cancelstat = 1;
            $m->deliver();

            Log::info('---------------Коммент отмены-----------------', 'capa');
            Log::info('urn = '.(string)$correctionevent->urn, 'capa');
            Log::info('comment = '.$correctionevent->comment, 'capa');
            Log::info('realizationtype = '.$correctionevent->realizationtype, 'capa');

            $m = new Message();
            $m->action = 'create';
            $m->urn = "urn:Communication:Comment:Level2withEditingSuggestion";
            $m->document = (string)$saved->urn;
            $m->appliedstatus = 'new';
            $m->cancel = 1;
            $m->content = $correctionevent->comment;
            $m->autor = (string)$employee->urn;
            $m->deliver();
        }


        //загрузка всех мероприятий капы
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Document:Correction:Capa';
        $m->DocumentCapaDeviation = (string)$saved->urn;
        $c1 = $m->deliver();

        //загрузка всех рассмотренных мероприятий капы
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Document:Correction:Capa';
        $m->DocumentCapaDeviation = (string)$saved->urn;
        $m->selecttype = 1;
        $c2 = $m->deliver();

        Log::info('--------------------------------', 'capa');
        Log::info(count($c1) . ' - Всего', 'capa');
        Log::info('--------------------------------', 'capa');
        Log::info(count($c2) . ' - рассмотреных', 'capa');
        Log::info('--------------------------------', 'capa');

        //если количество мероприятий каппы равно количеству рассмотренных мероприятий капы
        //и если мероприятия назначено по ошибке, или другим нюансам - возвратить на этап Editing
        foreach($c1 as $k) {
            if ($k->realizationtype == 'notmyresp' && count($c1) == count($c2)) {
                $num++;
                Log::info('Попали в ИФ на возврат этапа на едитинг', 'capa');
                Log::info('$num='.$num, 'capa');
                Log::info('realizationtype = '.$k->realizationtype, 'capa');
                //Log::info($k->realizationtyp, 'capa');


//                $m = new Message((array)$correctionevent);
//                $m->action = "update";
//                $m->cancelstat = 1;
//                $m->deliver();


                //todo надо или нет отменять все тикеты на стадии делегетинг?
                $metadata = $mpe->metadata;

                // update metadata
                $metadata->decision = 'cancel';

                Log::debug($metadata, 'decision');

                $m = new Message();
                $m->action = 'update';
                $m->urn = $mpe->urn;
                $m->metadata = json_encode($metadata);
                $m->deliver();


                // Call Java cancel stage
                $url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . (string)$ticket->ManagedProcessExecutionRecord->id;
                Log::debug($url, 'decision');
                try {
                    $r = httpRequest($url, null, [], 'GET', 5);
                    Log::debug($r, 'decision');
                } catch (Exception $e) {
                    Log::debug($e->getMessage(), 'decision');
                }
                break;
            }
        }
    }

    Log::info($num . ' - NUM', 'capa');
    if($num == 0) {

        $metad = $mpe->metadata;

        // update metadata
        $metad->decision = 'allowed';

        $m = new Message();
        $m->action = 'update';
        $m->urn = $mpe->urn;
        $m->metadata = json_encode($metad);
        $m->deliver();


        //Загружаем все мироприятия капы
        $m = new Message();
        $m->action = "load";
        $m->urn = 'urn:Document:Correction:Capa';
        $m->DocumentCapaDeviation = (string)$saved->urn;
        $correcctions = $m->deliver();

        //проверяем на отказы от ответственности
        $coof = 0;
        foreach ($correcctions as $correcction) {
            Log::info('realizationtype = '.$correcction->realizationtype, 'capa');
            if (($correcction->realizationtype == 'myrespwilldelegateordo' || $correcction->realizationtype == 'myself') && $correcction->selecttype == 1) $coof++;
        }

        Log::info('--------------------------------', 'capa');
        Log::info($coof . ' - Подтвердили', 'capa');
        Log::info('--------------------------------', 'capa');
        Log::info(count($correcctions) . ' - Всего', 'capa');

        //если колличество меропритиий равно колличеству подтвержденных мероприятий - завершаем этап процесса
        if (count($correcctions) == $coof) {


            //раздаем тикеты ответственным
            $userForCorr = [];//масив юзеров которым раздаем тикеты(надо для фильтрафии одинаковых)
            foreach($correcctions as $correcction) {

                if(!in_array((string)$correcction->controlresponsible->urn,$userForCorr)) {
                    $m = new Message();
                    $m->action = 'create';
                    $m->urn = 'urn:Feed:MPETicket:InboxItem';
                    $m->ManagementPostIndividual = (string)$correcction->controlresponsible->urn;
                    $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
                    $m->activateat = date("Y-m-d H:i:s", time());
                    $m->allowknowcuurentstage = true;
                    $m->allowopen = true;
                    $m->allowsave = true;
                    $m->allowcomplete = false;
                    $m->deliver();

                    array_push($userForCorr,(string)$correcction->controlresponsible->urn);
                }
            }

            Log::info('Раздали тикеты на консидеринг для'.$userForCorr, 'capa');

            Log::info('Все ок! '.$coof.' = '.count($correcctions).'. Переходим на next stage!', 'capa');

            $nextstage = 1; //индентификтора для js о переходе на следующий этап (если 0 - не перейдет)

            Log::info('nextstage = '.$nextstage, 'capa');

        }
    }

    $state = new stdClass();
    $state->state = (string)$saved->urn;
    $state->nextstage = $nextstage;
    return $state;

}

?>
