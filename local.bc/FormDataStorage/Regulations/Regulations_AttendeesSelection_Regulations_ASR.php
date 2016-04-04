<?php

$GLOBALS['LOAD_Regulations_AttendeesSelection_Regulations_ASR'] = function ($urn)
{
    Log::debug('LOAD Regulations_AttendeesSelection_Regulations_ASR', 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray();
        //$d = $loaded->current()->toArray(['DocumentRegulationsSOP'=>['userprocedure']]);
        //$d = $loaded->current()->toArray(['plannedattendees']);

        $arr = array();
        foreach($loaded->plannedattendees as $u){
            $m = new Message();
            $m->action = "load";
            $m->urn = $u;
            $user = $m->deliver();
            $arr[] = $user->current()->toArray();
        }

        $d['plannedattendees'] = $arr;

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_AttendeesSelection_Regulations_ASR'] = function ($d)
{
    Log::info("SAVE_Regulations_AttendeesSelection_Regulations_ASR", 'unisave');

    Log::debug($d, 'uniload');

    $subjectURN = $d->urn;




    $m = new Message();
    $m->action = 'load';
    $m->urn = $subjectURN;
    $ASR = $m->deliver();

    //Log::debug((string)$ASR->DocumentRegulationsSOP->urn, 'uniload');

    $sopURN = (string)$ASR->DocumentRegulationsSOP->urn;

    foreach($d->plannedattendees as $user) {
        //добавление в ASR

        $totalUser = $ASR->plannedattendees;

        if (!in_array($user, $totalUser)) {
            $m = new Message();
            $m->action = 'update';
            $m->urn = $subjectURN;
            $m->plannedattendees = ['append' => $user];
            $m->deliver();
        }

    }




    $listURN = $sopURN.':userprocedure';

    //удаление существующих
    $m = new Message();
    $m->action = 'members';
    $m->urn = new URN($listURN);
    $listMembers = $m->deliver();

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
    foreach ($d->plannedattendees as $boprocedure) {
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




    /*
        foreach($d->DocumentRegulationsSOP->userprocedure as $user){
            formDataManageListOfItemsIn($d->DocumentRegulationsSOP, ['userprocedure'], $subjectSOPURN);

            //добавление в ASR
            $m = new Message();
            $m->action = 'load';
            $m->urn = $subjectURN;
            $ASR = $m->deliver();

            $totalUser = $ASR->plannedattendees;

            if(!in_array($user,$totalUser)) {
                $m = new Message();
                $m->action = 'update';
                $m->urn = $subjectURN;
                $m->plannedattendees = ['append' => $user];
                $m->deliver();
            }
        }
    */
    $state = new stdClass();
    $state->state = $d->urn;
    return $state;

}

?>
