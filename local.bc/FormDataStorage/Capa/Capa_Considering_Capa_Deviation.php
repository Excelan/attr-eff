<?php

$GLOBALS['LOAD_Capa_Considering_Capa_Deviation'] = function ($urn,$managementrole)
{
    // Log::info("LOAD_Capa_Considering_Capa_Deviation", 'uniload');
    //Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['RiskManagementRiskApproved_unit', 'RiskManagementRiskNotApproved', 'DocumentCorrectionCapa'=>['DocumentSolutionCorrection']]);


        $notAppRisk = [];
        foreach($d['RiskManagementRiskNotApproved'] as $nrisk){

            $m = new Message();
            $m->action = "load";
            $m->urn = $nrisk['urn'];
            $risk = $m->deliver();

            array_push($notAppRisk,['title'=>$risk->riskdescription,'urn'=>(string)$risk]);
        }
        $d['RiskManagementRiskNotApproved'] = $notAppRisk;


        $newDCC = [];
        foreach ($d['DocumentCorrectionCapa'] as $key => $dcc)
        {

            Log::debug('+++++++++++++++++++++++++++++++++++++', 'uniload');
            Log::debug($dcc['eventplace'], 'uniload');
            Log::debug('-------------------------------------', 'uniload');


            if ($dcc['controlresponsible']['urn'] == (string)$managementrole->urn)
                array_push($newDCC, $dcc);

        }
        $d['DocumentCorrectionCapa'] = $newDCC;


        $json = json_encode($d);

        Log::debug('+++++++++++++++++++++++++++++++++++++', 'uniload');
        Log::debug($json, 'uniload');
        Log::debug('-------------------------------------', 'uniload');

        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Capa_Considering_Capa_Deviation'] = function ($d,$ticketurn)
{

    foreach ($d->DocumentCorrectionCapa as $correction) {
        foreach ($correction->DocumentSolutionCorrection as $solution) {
            Log::error('realizationtype = '.$solution->realizationtype, 'capa');
            Log::error('realizationdate = '.$solution->realizationdate, 'capa');
            Log::error('cost = '.$solution->cost, 'capa');
            Log::error('descriptionsolution = '.$solution->descriptionsolution, 'capa');
            Log::error('executor = '.$solution->executor, 'capa');

            if(
                $solution->realizationtype == 'NULL' ||
                strlen(trim($solution->realizationdate)) == 0 ||
                strlen(trim($solution->descriptionsolution)) == 0 ||
                strlen(trim($solution->executor)) == 0
            ){
                $state = new stdClass();
                $state->state = 200;
                $state->nextstage = 404;
                $state->text = "Ошибка! Заполнены не все поля!";
                return $state;
            }else{

                if (($solution->realizationtype == 'without_contractor_with_money' || $solution->realizationtype == 'with_contractor_with_money' || $solution->realizationtype == 'with_contractor_without_money') && strlen(trim($solution->cost)) == 0) {
                    $state = new stdClass();
                    $state->state = 200;
                    $state->nextstage = 404;
                    $state->text = "Ошибка! Не указана ОЦЕНОЧНАЯ СТОИМОСТЬ!";
                    return $state;
                }else if(($solution->realizationtype == 'without_contractor_without_money') && strlen(trim($solution->cost)) > 0){
                    $state = new stdClass();
                    $state->state = 200;
                    $state->nextstage = 404;
                    $state->text = "Ошибка! Поле ОЦЕНОЧНАЯ СТОИМОСТЬ указывать не надо!";
                    return $state;
                }else if(($solution->realizationtype == 'without_contractor_with_money' || $solution->realizationtype == 'with_contractor_with_money' || $solution->realizationtype == 'with_contractor_without_money') && strlen(trim($solution->cost)) > 0 && !is_numeric($solution->cost)){
                    $state = new stdClass();
                    $state->state = 200;
                    $state->nextstage = 404;
                    $state->text = "Ошибка! Поле ОЦЕНОЧНАЯ СТОИМОСТЬ не число!";
                    return $state;
                }
            }

        }
    }



    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();

    //получаем капу
    $m = new Message();
    $m->action = 'load';
    $m->urn = $saved->urn;
    $caps = $m->deliver();


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
    $m->deliver();

    //получаем mpe
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:ManagedProcess:Execution:Record';
    $m->id = (string)$ticket->ManagedProcessExecutionRecord->id;
    $mpe = $m->deliver();


    //сохраняем решения
    foreach ($d->DocumentCorrectionCapa as $correction) {
        foreach ($correction->DocumentSolutionCorrection as $solution){


            Log::info('---------------URN CORRECTION-----------------', 'rznasa');
            Log::info((string)$correction->urn, 'rznasa');

            $m = new Message();
            $m->action = 'update';
            $m->urn = (string)$correction->urn;
            $m->selectsolution = 1;//отметка о том, что данное мероприятие было рассмотрено , решение предложено и отправлено
            $m->deliver();

            $m = new Message((array)$solution);

            if ($solution->urn)
                $m->action = "update";
            else {
                $m->urn = 'urn:Document:Solution:Correction';
                $m->action = "create";
                $m->DocumentCorrectionCapa = $correction->urn;
            }
            $create = $m->deliver();
        }

    }

    //проверка все ли ответственные по мероприятих предложили решение

    //получаем капу
    $m = new Message();
    $m->action = 'load';
    $m->urn = (string)$saved->urn;
    $capa = $m->deliver();

    //получаем мероприятия капы
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Document:Correction:Capa';
    $m->DocumentCapaDeviation = (string)$capa->urn;
    $correction1 = $m->deliver();


    //получаем мероприятия капы по которых предложено решение
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Document:Correction:Capa';
    $m->DocumentCapaDeviation = (string)$capa->urn;
    $m->selectsolution = 1;
    $correction2 = $m->deliver();

    //если они совпадают - переводим капу на следующий этап
    Log::info(count($correction1) .' = '. count($correction2), 'rznasa');
    if(count($correction1) == count($correction2)){

        $arrUserTicket = array();

        //сразу добавляем в список инициатора, чтобы не раздавать ему тикет
        //тикет роздаст JAVA
        $arrUserTicket[] = $mpe->initiator;

        //раздаем тикеты ответственным
        foreach ($correction1 as $v) {

            if(!in_array((string)$v->controlresponsible->urn, $arrUserTicket)) {
                $m = new Message();
                $m->action = 'create';
                $m->urn = 'urn:Feed:MPETicket:InboxItem';
                $m->ManagementPostIndividual = (string)$v->controlresponsible->urn;
                $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
                $m->activateat = date("Y-m-d H:i:s", time());
                $m->allowknowcuurentstage = true;
                $m->allowopen = true;
                $m->allowsave = true;
                $m->allowcomplete = false;
                $m->deliver();

                array_push($arrUserTicket, (string)$v->controlresponsible->urn);
                Log::info('Тикет роздан = ' . (string)$v->controlresponsible->urn, 'rznasa');
            }
        }

        //раздаем тикеты византам
        foreach($caps->basevisants as $visant){

            if(!in_array((string)$visant, $arrUserTicket)) {

                $m = new Message();
                $m->action = 'create';
                $m->urn = 'urn:Feed:MPETicket:InboxItem';
                $m->ManagementPostIndividual = (string)$visant;
                $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
                $m->activateat = date("Y-m-d H:i:s", time());
                $m->allowknowcuurentstage = true;
                $m->allowopen = true;
                $m->allowsave = true;
                $m->allowcomplete = false;
                $m->deliver();
                Log::info('Тикет роздан = '.(string)$visant, 'rznasa');
                array_push($arrUserTicket, (string)$visant);
            }
        }

        //раздаем тикеты дополнительным византам
        foreach($caps->additionalvisants as $dvisant){

            if(!in_array((string)$dvisant, $arrUserTicket)){

                $m = new Message();
                $m->action = 'create';
                $m->urn = 'urn:Feed:MPETicket:InboxItem';
                $m->ManagementPostIndividual = (string)$dvisant;
                $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
                $m->activateat = date("Y-m-d H:i:s", time());
                $m->allowknowcuurentstage = true;
                $m->allowopen = true;
                $m->allowsave = true;
                $m->allowcomplete = false;
                $m->deliver();

                Log::info('Тикет роздан = '.(string)$dvisant, 'rznasa');
                array_push($arrUserTicket, (string)$dvisant);
            }
        }

        //раздаем тикет инициатору (если еще нету)
//        $initiator = (string)$mpe->initiator;
//        if(strpos($initiator,'Post')) {
//            if (!in_array($initiator, $arrUserTicket)) {
//                $m = new Message();
//                $m->action = 'create';
//                $m->urn = 'urn:Feed:MPETicket:InboxItem';
//                $m->ManagementPostIndividual = $initiator;
//                $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
//                $m->activateat = date("Y-m-d H:i:s", time());
//                $m->allowknowcuurentstage = true;
//                $m->allowopen = true;
//                $m->allowsave = true;
//                $m->allowcomplete = false;
//                $m->deliver();
//
//                Log::info('Тикет для инициатора !!!', 'rznasa');
//                Log::info('Тикет раздан = '.$initiator, 'rznasa');
//                array_push($arrUserTicket, $initiator);
//            }
//        }else{
//            Log::info('Указан неверный инициатор = '.$initiator, 'rznasa');
//        }

        foreach($arrUserTicket as $rr) {
            Log::info('Кому посланы тикеты' . $rr, 'rznasa');
        }

        Log::info('Все ок! Переходим на next stage!', 'rznasa');


        $metad = $mpe->metadata;

        // update metadata
        $metad->decision = 'allowed';

        $m = new Message();
        $m->action = 'update';
        $m->urn = (string)$mpe->urn;
        $m->metadata = json_encode($metad);
        $m->deliver();


        // Call Java complete stage
        $url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . (string)$mpe->id;
        Log::debug($url, 'decision');
        try {
            $r = httpRequest($url, null, [], 'GET', 5);
            Log::debug($r, 'decision');
            Log::info('Все ок! Перешли','rznasa');
        } catch (Exception $e) {
            Log::debug($e->getMessage(), 'decision');
            Log::info('Не перешли!!! ОШИБКА'.$e->getMessage(),'rznasa');
        }
        Log::info('Не перешли!!! ОШИБКА','rznasa');

    }


    $state = new stdClass();
    $state->state = 0;
    $state->nextstage = 0;
    return $state;

}

?>