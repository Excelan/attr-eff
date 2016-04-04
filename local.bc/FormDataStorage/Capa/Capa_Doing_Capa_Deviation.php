<?php

$GLOBALS['LOAD_Capa_Doing_Capa_Deviation'] = function ($urn,$managementrole)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();




    if (count($loaded))
    {
        //                                 Мероприятие                варианты решения
        $d = $loaded->current()->toArray(['DocumentCorrectionCapa'=>['selectedsolution']]);
        //$d = $loaded->current()->toArray(['RiskManagementRiskApproved_unit', 'DocumentRiskNotApproved', 'DocumentCorrectionCapa'=>['selectedsolution']]);

        $newDCC = [];
        foreach ($d['DocumentCorrectionCapa'] as &$dcc)
        {
            if ($dcc['selectedsolution']['executor']['urn'] == (string) $managementrole->urn)
                array_push($newDCC, $dcc);
        }
        $d['DocumentCorrectionCapa'] = $newDCC;

        $json = json_encode($d);

        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Capa_Doing_Capa_Deviation'] = function ($d,$ticketurn)
{

//    $m = new Message((array)$d);
//    $m->action = "update";
//    $saved = $m->deliver();



//сохраняем поля решения
foreach ($d->DocumentCorrectionCapa as $correction) {
    foreach ($correction as $solution) {


        $m = new Message();
        $m->action = "update";
        $m->urn = $solution->urn;
        $m->datedone = $solution->datedone;
        $m->deliver();
    }
}



    //получаем тикет
    $m = new Message();
    $m->action = 'load';
    $m->urn = (string)$ticketurn;
    $ticket = $m->deliver();

    /*
    //Закрываем тикет
    $m = new Message();
    $m->action = 'update';
    $m->urn = $ticketurn;
    //$m->isvalid = false;
    $m->deliver();
*/

    //получаем mpe
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:ManagedProcess:Execution:Record';
    $m->id = (string)$ticket->ManagedProcessExecutionRecord->id;
    $mpe = $m->deliver();


    //Ставим отметку, о сделаной работе
    $subjectURN = new URN((string)$mpe->subject);
    //получаем капу
    $m = new Message();
    $m->action = 'load';
    $m->urn = $subjectURN;
    $capa = $m->deliver();

    //получем мероприятия капы
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Document:Correction:Capa';
    $m->DocumentCapaDeviation = (string)$capa->urn;
    $corrections = $m->deliver();

    //ставим отметку готово
    foreach($corrections as $correction) {
        if ((string)$correction->selectedsolution->executor->urn == (string)$ticket->ManagementPostIndividual->urn){

            Log::debug('datedone = '.$correction->selectedsolution->datedone, 'rznasa');
            Log::debug('strlen = '.strlen(trim($correction->selectedsolution->datedone)), 'rznasa');

            if(!is_null($correction->selectedsolution->datedone) && strlen(trim($correction->selectedsolution->datedone)) > 0) {
                $m = new Message();
                $m->action = 'update';
                $m->urn = (string)$correction->selectedsolution->urn;
                $m->ready = 1;//отметка
                $m->deliver();
            }
        }
    }


    $num = 0;
    foreach($corrections as $correction) {

        if($correction->selectedsolution->ready) $num++;

    }
    Log::debug('Num'.$num, 'rznasa');
    Log::debug('count'.count($corrections), 'rznasa');

    Log::debug('Num'.$num, 'capa');
    Log::debug('count'.count($corrections), 'capa');

    //если все испольните сделали работу - переходим на следующий этап
    if($num == count($corrections)){

        Log::debug('GO NEXT STAGE', 'capa');

        // Call Java complete stage
        $url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . (string)$ticket->ManagedProcessExecutionRecord->id;
        Log::debug($url, 'decision');
        try {
            $r = httpRequest($url, null, [], 'GET', 5);
            Log::debug($r, 'decision');
        } catch (Exception $e) {
            Log::debug($e->getMessage(), 'decision');
        }
    }

    $state = new stdClass();
    $state->state = $capa->urn;
    return $state;

}

?>
