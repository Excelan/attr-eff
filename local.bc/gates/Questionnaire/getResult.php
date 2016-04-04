<?php
namespace Questionnaire;

class getResult extends \Gate
{

    public function gate()
    {
        \Log::info("GET RESULT", 'study');

        $data = $this->data;
        if (!is_array($data)) {
            $data=$data->toArray();
        }

        //println('S---'.date('Y-m-d H:i:s',strtotime($data['startTime']).'---'));
        //println('E+++'.date('Y-m-d H:i:s',strtotime($data['endTime']).'++++'));

        //загрузка всех ответов/вопросов
        $m = new \Message();
        $m->action = 'load';
        $m->urn = $data['urn'];
        $questionnaire = $m->deliver();

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Study:RegulationStudy:Q';
        $m->DocumentRegulationsTA = $questionnaire->urn;
        $questions = $m->deliver();

        $countQ = count($questions);//количество вопросов

        $count = 0;//количество ответов
        foreach ($questions as $question) {
            $m = new \Message();
            $m->action = 'load';
            $m->urn = 'urn:Study:RegulationStudy:A';
            $m->StudyRegulationStudyQ = $question->urn;
            $m->correctly = 'yes';
            $answers = $m->deliver();

            if (count($count)>0) {
                $count += count($answers);
            }
        }

        //общее количество правильных ответов $count


        //Датчики привильности ответа на вопрос
        $trueA = 0;
        $falseA = 0;

        //старый принцип подщета ищет количество праивльных и неправильных ответов в общем по всем вопросам потом результат сравнивается
        //новый же щитает правильно или нет 100% дан ответ на вопрос, если хоть один неверно - неправильный отет +1
        /*
        for($i = 0; $i < count($data['result']); $i++){
            if($data['result'][$i]['question']){

                $m = new \Message();
                $m->action = 'load';
                $m->urn = 'urn:Study:RegulationStudy:A';
                $m->correctly = 'yes';
                $m->StudyRegulationStudyQ = $data['result'][$i]['question'];
                $as = $m->deliver();

                $m = new \Message();
                $m->action = 'load';
                $m->urn = 'urn:Study:RegulationStudy:A';
                $m->correctly = 'no';
                $m->StudyRegulationStudyQ = $data['result'][$i]['question'];
                $asNo = $m->deliver();

                if($data['result'][$i]['answer']){

                    for ($j = 0; $j < count($data['result'][$i]['answer']); $j++) {


                        foreach($as as $a){

                            if($a->urn == $data['result'][$i]['answer'][$j]){
                                $trueA ++;
                            }

                        }

                        foreach($asNo as $a){

                            if($a->urn == $data['result'][$i]['answer'][$j]){
                                $falseA ++;
                            }

                        }


                    }

                }

            }
        }

*/



        for ($ii = 0; $ii < count($data['result']); $ii++) {
            if ($data['result'][$ii]['question']) {
                $m = new \Message();
                $m->action = 'load';
                $m->urn = 'urn:Study:RegulationStudy:A';
                $m->correctly = 'yes';
                $m->StudyRegulationStudyQ = $data['result'][$ii]['question'];
                $trueAnswer = $m->deliver();

                $nums = 0;
                for ($v = 0; $v < count($data['result'][$ii]['answer']); $v++) {
                    foreach ($trueAnswer as $o) {
                        if ((string)$o->urn == $data['result'][$ii]['answer'][$v]) {
                            $nums++;
                        }
                    }
                }

                //\Log::info($nums, 'rznasa');
            }
        }








        for ($ii = 0; $ii < count($data['result']); $ii++) {
            if ($data['result'][$ii]['question']) {
                $m = new \Message();
                $m->action = 'load';
                $m->urn = 'urn:Study:RegulationStudy:A';
                $m->correctly = 'yes';
                $m->StudyRegulationStudyQ = $data['result'][$ii]['question'];
                $trueAnswer = $m->deliver();


                $m = new \Message();
                $m->action = 'load';
                $m->urn = 'urn:Study:RegulationStudy:A';
                $m->correctly = 'no';
                $m->StudyRegulationStudyQ = $data['result'][$ii]['question'];
                $falseAnswer = $m->deliver();



                if ($data['result'][$ii]['answer']) {
                    $num = 0;//сюда записываем количество правильных ответов пользователя
                    $ca = 0;//щетчик неправильных ответов

                    for ($v = 0; $v < count($data['result'][$ii]['answer']); $v++) {
                        foreach ($trueAnswer as $o) {
                            if ((string)$o->urn == $data['result'][$ii]['answer'][$v]) {
                                $num++;
                            }
                        }

                        foreach ($falseAnswer as $ot) {

                            //если дан неправильный ответ либо количество правильных ответов меньше общего количества правильных ответов
                            if ($ot->urn == $data['result'][$ii]['answer'][$v]) {
                                $falseA++;
                                $ca++;
                                break 2;
                            }
                        }
                    }
                    //\Log::info('-----', 'rznasa');
                    if ($num < count($trueAnswer) && $ca == 0) {
                        $falseA++;
                    }
                    unset($ca);
                    unset($num);
                } else {
                    $falseA++;
                }
            }
        }



        $trueA = $countQ-$falseA;//правильные ответ  = количетво вопросов минус количество неправильных ответов

        $percentT = round(($trueA*100)/$countQ, 2);
        $percentF = round(($falseA*100)/$countQ, 2);
        $percent = $percentT;

        $done = '0';

        //если минусовое значение-обнуляем
        if ($percent < 0) {
            $done = '0';
            $percent = 0;
        } else {
            if ($questionnaire->percent <= $percent) {
                $done = '1';
            } //если процент больше или равен проходимому
            else {
                $done = '0';
            }
        }

        $m = new \Message();
        $m->action = 'create';
        $m->urn = 'urn:Study:RegulationStudy:R';
        $m->useranswer = $data['result'];
        $m->trua = $trueA;
        $m->falsea = $falseA;
        $m->alla = $count;
        $m->done = $done;
        $m->questionnaire = explode(':', $data['urn'])[4];
        $m->user = $data['managementrole'];
        $m->starttime = strtotime($data['startTime']);
        $m->endtime = strtotime($data['endTime']);
        $results = $m->deliver();

        $res = '';
        if ($done == '0') {
            $res = 'Нет';
        }
        if ($done == '1') {
            $res = 'Есть';
        }






        //добавление в ASR
        $m = new \Message();
        $m->action = 'load';
        //$m->urn = 'urn:Document:Regulations:ASR';
        //$m->DocumentRegulationsTA = (string)$questionnaire->urn;
        $m->urn = $data['subjectURN'];
        $ASR = $m->deliver();

        $user = 'urn:Management:Post:Individual:'.$data['managementrole'];

        if ($done == '1') {
            \Log::info("done == 1", 'study');
            //если юзер успешно прошел атестацию - добаляем его в список successpassed
            $totalUserSuccesspassed = $ASR->successpassed;
            \Log::info("$user->urn passed", 'study');
            if (!in_array($user, $totalUserSuccesspassed)) {
                $m = new \Message();
                $m->action = 'update';
                $m->urn = (string)$ASR->urn;
                $m->successpassed = ['append' => $user];
                $m->deliver();
                \Log::info("$user->urn added to passed", 'study');
            }
        } else {
            //если юзер не прошел атестацию - добаляем его в список failedpassed
            $totalUserFailedpassed = $ASR->failedpassed;
            \Log::info("$user->urn failed", 'study');
            if (!in_array($user, $totalUserFailedpassed)) {
                $m = new \Message();
                $m->action = 'update';
                $m->urn = (string)$ASR->urn;
                $m->failedpassed = ['append' => $user];
                $m->deliver();
                \Log::info("$user->urn added to failed", 'study');
            }
            \Log::info("done != 1", 'study');
        }




//		$m = new \Message();
//		$m->action = 'update';
//		$m->urn = (string)$data['ticketurn'];
//		$m->isvalid = false;
//		$m->allowknowcuurentstage = false;
//		$m->allowopen = false;
//		$m->allowsave = false;
//		$m->allowcomplete = false;
//		$m->deliver();


        \Log::info("DONE RESULT", 'study');
        return ['true' => $trueA.'/'.$percentT.'%', 'false'=>$falseA.'/'.$percentF.'%' , 'percent' => round($percent, 2).'%', 'done'=> $res];
    }
}
