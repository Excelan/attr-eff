<?php

$GLOBALS['LOAD_Protocol_Editing_Protocol_EA']=$GLOBALS['LOAD_Protocol_Decision_Protocol_EA'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');


    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['commisionmembers_unit','boprocedure_unit']);
        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Protocol_Editing_Protocol_EA'] = $GLOBALS['SAVE_Protocol_Decision_Protocol_EA']= function ($d)
{
    Log::info("SAVE_Protocol_Editing_Protocol_EA", 'unisave');

    $m = new Message((array)$d);
    $m->action = "update";
    $saveState = $m->deliver();

    $subjectURN = $d->urn;


    formDataManageListOfItemsIn($d, ['commisionmembers'], $subjectURN);
    formDataManageListOfItemsIn($d, ['boprocedure'], $subjectURN);

/*
    if($d->commisionmembers) {
        $listURN = $saveState->urn . ':commisionmembers';
        // Участники процедуры
        //удаление существующих
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($listURN);
        $listMembers = $m->deliver();

        //Log::info('$d->userproceduregroup'.$d->userproceduregroup, 'unisave');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Individual';
        $m->in = $listMembers;
        $resList = $m->deliver();

        foreach ($resList as $v) {
            $m = new Message();
            $m->action = 'remove';
            $m->urn = (string)$v->urn;
            $m->from = new URN($listURN);
            $m->deliver();
        }

        //добавление выбраных
        foreach ($d->commisionmembers as $userprocedureg) {

            $m = new Message();
            $m->action = 'exists';
            $m->urn = (string)$userprocedureg;
            $m->in = $listURN;
            $e = $m->deliver();


            if (!$e->exists) {
                $m = new Message();
                $m->action = 'add';
                $m->urn = (string)$userprocedureg;
                $m->to = $listURN;
                $added = $m->deliver();
            } else
                Log::info($e, 'unisave');
        }
    }


*//*

    if($d->boprocedure) {
        $listURN2 = $saveState->urn . ':boprocedure';
        // Участники процедуры
        //удаление существующих
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($listURN2);
        $listMembers2 = $m->deliver();

        //Log::info('$d->userproceduregroup'.$d->userproceduregroup, 'unisave');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Individual';
        $m->in = $listMembers2;
        $resList2 = $m->deliver();

        foreach ($resList2 as $v) {
            $m = new Message();
            $m->action = 'remove';
            $m->urn = (string)$v->urn;
            $m->from = new URN($listURN2);
            $m->deliver();
        }

        //добавление выбраных
        foreach ($d->boprocedure as $bp) {

            $m = new Message();
            $m->action = 'exists';
            $m->urn = (string)$bp;
            $m->in = $listURN2;
            $e = $m->deliver();


            if (!$e->exists) {
                $m = new Message();
                $m->action = 'add';
                $m->urn = (string)$bp;
                $m->to = $listURN2;
                $added = $m->deliver();
            } else
                Log::info($e, 'unisave');
        }
    }

*/













    $s = new stdClass();
    $s->state = (string)$d->urn;

    return $s;

}
?>
