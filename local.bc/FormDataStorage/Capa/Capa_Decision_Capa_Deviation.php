<?php

$GLOBALS['LOAD_Capa_Decision_Capa_Deviation'] = function ($urn,$managementrole)
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

$GLOBALS['SAVE_Capa_Decision_Capa_Deviation'] = function ($d,$ticketurn)
{

    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();

    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
