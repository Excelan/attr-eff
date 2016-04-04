<?php
namespace Process\Study;

class fillASRIterationFromSOPForTAInterval extends \Gate
{

    /*
    PHP переносит из SOP в лист аттестации ASR тех, для кого требуется обучение (по Должности и Типу Должности)
    в первый раз - sop.требуется_для - merge(0*ASR) = берем всех
    повторная сдача в этом интервале - всех несдавших (не в прошлом процессе, а в принципе!) sop.требуется_для - merge(ASR всех итераций-сессий этого интервала) = кому еще сдавать
    */
    public function gate()
    {
        if ($this->data instanceof \Message) {
            $d = json_decode(json_encode($this->data->toArray()));
        } // вызов из теста
        else {
            $d = json_decode(json_encode($this->data));
        } // вызов извне

        //$mpeURN = new \URN("urn:ManagedProcess:Execution:Record:".$d->mpeId);

        $mpe_and_subject = getMPEAndSubjectByMpeID((int)$d->mpeId);
        $mpeAttestation = $mpe_and_subject[0];
        $subjectASR = $mpe_and_subject[1];
        if ($subjectASR->urn->prototype->getOfType() != 'ASR') {
            throw new \Exception("Subject is not ASR but ".$subjectASR->urn->prototype->getOfType());
        }

        //println($mpeAttestation);
        //printlnd($mpeAttestation->metadata);
        \Log::debug((string)$mpeAttestation, 'processrouter');
        \Log::debug($mpeAttestation->metadata, 'processrouter');
        //println($subjectASR);
        $SOP = $subjectASR->DocumentRegulationsSOP;
        //println($SOP);
        if (count($SOP) != 1) {
            if (!$mpeAttestation->metadata->sop) {
                throw new \Exception("No SOP subject && in mpe.metadata");
            }
            $sopURN = new \URN($mpeAttestation->metadata->sop);
            $SOP = $sopURN->resolve();
            if (count($SOP) != 1) {
                $sopURN = new \URN($d->sopurn);
                $SOP = $sopURN->resolve();
                throw new \Exception("No SOP in ASR (neither SOP subject && in mpe.metadata)");
            }
        }

        $TA = $subjectASR->DocumentRegulationsTA;
        if (count($TA) != 1) {
            if (!$mpeAttestation->metadata->ta) {
                throw new \Exception("No TA subject && in mpe.metadata");
            }
            $taURN = new \URN($mpeAttestation->metadata->ta);
            $TA = $taURN->resolve();
            if (count($TA) != 1) {
                throw new \Exception("No TA in ASR (neither TA subject && in mpe.metadata)");
            }
        }

        // TODO !!! перенос данных из SOP в TA
        /*
        $m = new \Message();
        $m->action = 'update';
        $m->urn = $subjectASR->urn; // update TA
        $m->DocumentRegulationsSOP = $SOP->urn; // привязали SOP к TA программе обучения
        $m->trainer = $SOP->ManagementPostIndividual->urn; // перекинули Тренера с (Ответственный за создание Программы обучения)
        $m->deliver();
        */

        $sopNeedStudyFor = [];
        $byASRDoneStudyBy = [];

        // перенести из SOP в TA список на обучение из userprocedure в новый процесс можно будет только после этапа create draft, а он уже прошел сразу после старта процесса
        foreach ($SOP->userprocedure as $student) {
            array_push($sopNeedStudyFor, (string)$student->urn);
            // кто должен сдать
            /*
            $m = new \Message();
            $m->action = 'add';
            $m->urn = $student;
            $m->to = $subjectASR->urn . ':' . 'plannedattendees';
            $m->deliver();
            */
            /*
            $m = new \Message();
            $m->action = 'update';
            $m->urn = $subjectASR->urn;
            $m->plannedattendees = ['append' => (string)$student->urn];
            $r = $m->deliver();
            \Log::debug((string)$student->urn, 'processrouter');
            \Log::debug((string)$m, 'processrouter');
            \Log::debug((string)$r, 'processrouter');
            */
            $i++;
        }
        // то же, но из типов должностей развернуть должности
        foreach ($SOP->userproceduregroup as $groupstudent) {
            foreach ($groupstudent->ManagementPostIndividual as $student) {
                array_push($sopNeedStudyFor, (string)$student->urn);
                $i++;
            }
        }


        /*
        foreach ($sopNeedStudyFor as $student)
        {
            $m = new \Message();
            $m->action = 'update';
            $m->urn = $subjectASR->urn;
            $m->plannedattendees = ['append' => $student];
            $m->deliver();
            \Log::debug("++".(string)$student, 'processrouter');
        }
        */

        //$ASR = $subjectASR->resolve();
        //\Log::debug($ASR->plannedattendees, 'processrouter');
        //\Log::debug((string)$ASR->plannedattendees, 'processrouter');

        \Log::debug("PREV".$mpeAttestation->metadata->prev, 'processrouter');
        $prev = $mpeAttestation->metadata->prev;
        while ($prev) {
            $pc++;
            if ($pc > 10) {
                break;
            }
            $mpe_and_subject_prev = getMPEAndSubjectByMpeID((int)explode(':', $prev)[4]);
            $mpeAttestation_prev = $mpe_and_subject_prev[0];
            $subjectASR_prev = $mpe_and_subject_prev[1];
            \Log::debug("PREV $pc ".(string)$mpeAttestation_prev, 'processrouter');
            \Log::debug("PREV $pc ".(string)$subjectASR_prev, 'processrouter');
            foreach ($subjectASR_prev->successpassed as $studentok) {
                \Log::debug('$subjectASR_prev->successpassed '.$studentok . ' in ' . $subjectASR_prev->urn, 'processrouter');
                array_push($byASRDoneStudyBy, $studentok);
            }
            $prev = $mpeAttestation_prev->metadata->prev;
        }



        /*
        $m = new \Message();
        $m->urn = 'urn:Document:Regulations:ASR';
        $m->action = 'load';
        $m->DocumentRegulationsSOP = $SOP->urn; // $d->sopurn;
        $asrs = $m->deliver();
        \Log::debug((string)$m, 'processrouter');
        \Log::debug('ASR TOTAL '.count($asrs), 'processrouter');

        foreach ($asrs as $asr) {
            //\Log::debug('ASR '.$asr, 'processrouter');
            if (count($asr->successpassed)) \Log::info($asr->successpassed, 'processrouter');
            array_push($byASRDoneStudyBy, (string)$asr->successpassed);
        }
        */

        \Log::debug('$sopNeedStudyFor '.json_encode($sopNeedStudyFor), 'processrouter');
        \Log::debug('$byASRDoneStudyBy '.json_encode($byASRDoneStudyBy), 'processrouter');
        $nowFor = array_values(array_diff($sopNeedStudyFor, $byASRDoneStudyBy));

        foreach ($nowFor as $student) {
            $m = new \Message();
            $m->action = 'update';
            $m->urn = $subjectASR->urn;
            $m->plannedattendees = ['append' => $student];
            $m->deliver();
            \Log::debug('>>> '.$m, 'processrouter');
            $j++;
        }
        \Log::debug('DIFF '.json_encode($nowFor), 'processrouter');



        \Log::debug('OK '.$i.' '.$j, 'processrouter');

        return ['status' => 200];
    }
}
