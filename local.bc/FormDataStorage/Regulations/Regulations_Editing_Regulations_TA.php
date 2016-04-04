<?php

$GLOBALS['LOAD_Regulations_Editing_Regulations_TA'] = function ($urn)
{
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['StudyRegulationStudyQ'=>['StudyRegulationStudyA']]);

        $json = json_encode($d);
        Log::debug('-----------', 'uniload');
        Log::debug($json, 'uniload');
        Log::debug('-----------', 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Regulations_Editing_Regulations_TA'] = function ($d)
{
    //Log::info("SAVE_Regulations_Editing_Regulations_TA", 'unisave');

    //Log::info((array)$d, 'unisave');




    if(strlen(trim($d->time)) == 0){
        $state = new stdClass();
        $state->state = 200;
        $state->nextstage = 404;
        $state->text = "Ошибка! Поле 'Врямя на ответ' не заполнено!";
        return $state;
    }else if(strlen(trim($d->percent)) == 0){
        $state = new stdClass();
        $state->state = 200;
        $state->nextstage = 404;
        $state->text = "Ошибка! Поле 'Проходной процент' не заполнено!";
        return $state;
    }else if($d->percent > 100){
        $state = new stdClass();
        $state->state = 200;
        $state->nextstage = 404;
        $state->text = "Ошибка! Поле 'Проходной процент' не может быть больше 100!";
        return $state;
    }


    //проверка на заполенение вопросами и ответами
    $fix = 0;//кол-во вопросов
    foreach ($d->StudyRegulationStudyQ as $vl){

        if(strlen($vl->content) > 0) $fix++;

        $check = 0;//количество отметок правильности ответа в вопросе
        $can = 0;//кол-во заполененых ответов
        foreach($vl->StudyRegulationStudyA as $va){
            if(strlen($va->content) > 0) $can++;
            if($va->correctly != NULL) $check++;

            if($va->correctly == NULL){
                $state = new stdClass();
                $state->state = 200;
                $state->nextstage = 404;
                $state->text = "Ошибка! Укажите правильный или нет ответ!";
                return $state;
            }
        }

        if($can < count($vl->StudyRegulationStudyA)){
            $state = new stdClass();
            $state->state = 200;
            $state->nextstage = 404;
            $state->text = "Ошибка! Не указаны ответы!";
            return $state;
        }
    }

    if($fix < count($d->StudyRegulationStudyQ)){
        $state = new stdClass();
        $state->state = 200;
        $state->nextstage = 404;
        $state->text = "Ошибка! Не заполнены все вопросы!";
        return $state;
    }



    $m = new Message((array)$d);
    $m->action = "update";
    $saved = $m->deliver();

    //Добавление вопросов
    foreach ($d->StudyRegulationStudyQ as $value){

        //Log::info($value->StudyRegulationStudyA, 'unisave');

        $m = new Message((array)$value);
        if ($value->urn)
            $m->action = "update";
        else {
            $m->urn = 'urn:Study:RegulationStudy:Q';
            $m->action = "create";
        }
        $m->DocumentRegulationsTA = (string)$saved->urn;
        $qDone = $m->deliver();

        Log::info('вопрос', 'unisave');

        //добавление ответов
        foreach($value->StudyRegulationStudyA as $a){

            Log::info('ответ', 'unisave');

            $m = new Message((array)$a);
            if ($a->urn)
                $m->action = "update";
            else {
                $m->action = "create";
                $m->urn = 'urn:Study:RegulationStudy:A';
            }
            $m->StudyRegulationStudyQ = (string)$qDone->urn;
            $m->deliver();
        }

    }





    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;

}

?>
