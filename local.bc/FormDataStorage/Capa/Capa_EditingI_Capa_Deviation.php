<?php

$GLOBALS['LOAD_Capa_EditingI_Capa_Deviation'] = function ($urn,$managementrole)
{
    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();




    if (count($loaded))
    {
        //                                 Мероприятие                варианты решения
        $d = $loaded->current()->toArray(['approver','basevisants','additionalvisants','RiskManagementRiskApproved','RiskManagementRiskNotApproved','DocumentCorrectionCapa'=>['selectedsolution']]);

        $urn = new URN($d['urn']);
        $prototype = $urn->getPrototype();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->isprocess = false;
        $m->indomain = $prototype->getInDomain();
        $m->ofclass = $prototype->getOfClass();
        $m->oftype = $prototype->getOfType();

        $protoobject = $m->deliver();

        $d['approver'] = ['title'=>$protoobject->approver->title,'urn'=>$protoobject->approver->urn];


        $notAppRisk = [];
        foreach($d['RiskManagementRiskNotApproved'] as $nrisk){

            $m = new Message();
            $m->action = "load";
            $m->urn = $nrisk['urn'];
            $risk = $m->deliver();

            array_push($notAppRisk,['title'=>$risk->riskdescription,'urn'=>(string)$risk]);
        }
        $d['RiskManagementRiskNotApproved'] = $notAppRisk;


        $baseVisantArr = [];
        foreach($d['basevisants'] as $visant){

            $m = new Message();
            $m->action = "load";
            $m->urn = $visant;
            $post = $m->deliver();

            array_push($baseVisantArr,['title'=>$post->title,'urn'=>$visant]);
        }
        $d['basevisants'] = $baseVisantArr;


        $additionaVisantArr = [];
        foreach($d['additionalvisants'] as $visant){

            $m = new Message();
            $m->action = "load";
            $m->urn = $visant;
            $post = $m->deliver();

            array_push($additionaVisantArr,['title'=>$post->title,'urn'=>$visant]);
        }
        $d['additionalvisants'] = $additionaVisantArr;




        $newDCC = [];
        foreach ($d['DocumentCorrectionCapa'] as &$dcc)
        {

            if ($dcc['selectedsolution']['approveded'] == 1)
                array_push($newDCC, $dcc);
        }
        $d['DocumentCorrectionCapa'] = $newDCC;


        $json = json_encode($d);

        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

$GLOBALS['SAVE_Capa_EditingI_Capa_Deviation'] = function ($d,$ticketurn)
{

    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();

    $cancel = 0;

    foreach($d->DocumentCorrectionCapa as $corection){
        Log::info($corection->selectedsolution->matches, 'rznasa');

        $m = new Message();
        $m->action = "update";
        $m->urn = (string)$corection->selectedsolution->urn;
        $m->matches = $corection->selectedsolution->matches;
        $m->comment = $corection->selectedsolution->comment;
        $solution = $m->deliver();

        if($corection->selectedsolution->confirmed == 'notmatch' || $corection->selectedsolution->confirmed == 'matchparcel'){
            $cancel++;
        }
    }

    /*
    if($cancel == 0){

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



        // Call Java cancel stage
        $url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . (string)$ticket->ManagedProcessExecutionRecord->id;
        Log::debug($url, 'decision');
        try {
            $r = httpRequest($url, null, [], 'GET', 5);
            Log::debug($r, 'decision');
        } catch (Exception $e) {
            Log::debug($e->getMessage(), 'decision');
        }
    }
*/


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
