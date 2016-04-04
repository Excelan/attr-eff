<?php

class CapaDirectorControl extends AjaxApplication implements ApplicationFreeAccess //, ApplicationUserOptional
{
    public function CreateTicketsForDelegates($path)
    {
        Log::debug('=========================================================================', 'director');

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);
        Log::debug($r, 'director');
        Log::debug($r['mpeId'], 'director');

        //$UPN = explode(':',$r['upn']);
        //$MPE_ID = $UPN[4];
        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        Log::debug('MPE', 'director');
        Log::debug((string)$mpe->current(), 'director');
        Log::debug('subject:'.(string)$mpe->subject, 'director');


        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();

        $arrUser = [];//масив юзеров которым раздается тикет(для фильтрации одинаковых)
        foreach ($subject->DocumentCorrectionCapa as $correctionMeropri) {

            //todo проверка на повторность раздачи тикетов после отмены делегирования по статусу - не моя сфера

            $m = new Message();
            $m->action = 'update';
            $m->urn = (string)$correctionMeropri->urn;
            $m->selecttype = 0;
            $m->cancelstat = 0;

            /**
            Если Моя сфера, но делегирую на своего подчиненного или не моя сфера
            очищать поле выбора решения и комментария для участников
             */
            Log::info('--------------------------------------------------------', 'rznasa');
            Log::info((string)$correctionMeropri->urn, 'rznasa');
            Log::info($correctionMeropri->realizationtype, 'rznasa');

            if($correctionMeropri->realizationtype == 'notmyresp' || $correctionMeropri->realizationtype == 'myrespwilldelegateordo'){

                Log::info('+++++++++++++++++++++++', 'rznasa');

                $m->realizationtype = '0';
                $m->comment = '';
            }

            Log::info($m, 'rznasa');
            $doneC =  $m->deliver();
            Log::info((string)$doneC, 'rznasa');



            foreach ($correctionMeropri->controlresponsible as $controlresponsible) {
                if (!in_array((string)$controlresponsible->urn, $arrUser)) {


                    $m = new \Message();
                    $m->action = 'create';
                    $m->urn = 'urn:Feed:MPETicket:InboxItem';
                    $m->ManagementPostIndividual = (string)$controlresponsible->urn;
                    $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
                    $m->activateat = date("Y-m-d H:i:s", time());
                    $m->allowknowcuurentstage = true;
                    $m->allowopen = true;
                    $m->allowsave = true;
                    $m->allowcomplete = false;
                    $ticket = $m->deliver();
                    Log::debug("? " . (string)$m, 'director');
                    Log::debug("++ TICKET " . (string)$ticket, 'director');
                    array_push($arrUser, (string)$controlresponsible->urn);
                }
            }
        }

        // процесс уже исполняет Actor System User 0 !!!
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }


    public function DeactiveTicketsForCorrection($path)
    {
        Log::debug('Начало деактивации тикетов', 'director');


        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();


        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
        $tickets = $m->deliver();

//        $m = new Message();
//        $m->action = 'load';
//        $m->urn = 'urn:Feed:MPETicket:InboxItem';
//        $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
//        $m->allowopen = 0;
//        $tickets1 = $m->deliver();


        //масив со списком всех византов
        $allVisant = array();

        //получаем капу
        $m = new Message();
        $m->action = 'load';
        $m->urn = $subjectURN;
        $capa = $m->deliver();


//        foreach ($capa->basevisants as $visant) {
//            array_push($allVisant, (string)$visant);
//        }
//
//        foreach ($capa->additionalvisants as $addvisant) {
//            array_push($allVisant, (string)$addvisant);
//        }



        Log::debug('-----------------------------------------------', 'director');

        foreach ($tickets as $ticket) {
            //if ((string)$mpe->initiator != (string)$ticket->ManagementPostIndividual->urn || in_array((string)$ticket->ManagementPostIndividual->urn, $allVisant)) {
            Log::debug((string)$ticket->urn, 'director');

            $m = new Message();
            $m->action = 'update';
            $m->urn = (string)$ticket->urn;
            if((string)$mpe->initiator != (string)$ticket->ManagementPostIndividual->urn) $m->isvalid = false;
            $m->allowknowcuurentstage = false;
            $m->allowopen = false;
            $m->allowsave = false;
            $m->allowcomplete = false;
            $m->deliver();

            //array_push($allVisant, (string)$ticket->ManagementPostIndividual->urn);
            // }
        }


        Log::debug('-----------------------------------------------', 'director');
        Log::debug('Конец деактивации тикетов', 'director');

        // процесс уже исполняет Actor System User 0 !!!
        $d = new Message();
        $d->ok = 'ticketsDeactive';

        return $d;
    }


    public function CreateTicketsForVising($path)
    {
        Log::debug('Начало создания тикетов для визирования', 'director');

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();


        //получаем капу
        $m = new Message();
        $m->action = 'load';
        $m->urn = $subjectURN;
        $capa = $m->deliver();

        Log::debug('-----------------------------------------------', 'director');
        foreach ($capa->basevisants as $visant) {
            Log::debug($visant, 'director');

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
        }

        foreach ($capa->additionalvisants as $addvisant) {
            Log::debug($addvisant, 'director');

            $m = new Message();
            $m->action = 'create';
            $m->urn = 'urn:Feed:MPETicket:InboxItem';
            $m->ManagementPostIndividual = (string)$addvisant;
            $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
            $m->activateat = date("Y-m-d H:i:s", time());
            $m->allowknowcuurentstage = true;
            $m->allowopen = true;
            $m->allowsave = true;
            $m->allowcomplete = false;
            $m->deliver();
        }


        Log::debug('initiator ticket create ....', 'director');

        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        $m->ManagementPostIndividual = (string)$mpe->initiator;
        $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
        $m->activateat = date("Y-m-d H:i:s", time());
        $m->allowknowcuurentstage = true;
        $m->allowopen = true;
        $m->allowsave = true;
        $m->allowcomplete = false;
        $m->deliver();

        Log::debug('.... done', 'director');



        Log::debug('-----------------------------------------------', 'director');
        Log::debug('Конец создания тикетов для визирования', 'director');

        // процесс уже исполняет Actor System User 0 !!!
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }


    public function CreateTicketsForDoing()
    {
        Log::debug('Начало создания тикетов для DOING', 'director');

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();


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

        //масив кому отправлены тикеты
        $allVisant = array();

        Log::debug('-----------------------------------------------', 'director');
        foreach ($corrections as $correction) {
            if (!in_array((string)$correction->selectedsolution->executor->urn, $allVisant)) {
                Log::debug((string)$correction->selectedsolution->executor->urn, 'director');

                $m = new Message();
                $m->action = 'create';
                $m->urn = 'urn:Feed:MPETicket:InboxItem';
                $m->ManagementPostIndividual = (string)$correction->selectedsolution->executor->urn;
                $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
                $m->activateat = date("Y-m-d H:i:s", time());
                $m->allowknowcuurentstage = true;
                $m->allowopen = true;
                $m->allowsave = true;
                $m->allowcomplete = false;
                $m->deliver();

                array_push($allVisant, (string)$correction->selectedsolution->executor->urn);
            }
        }



        Log::debug('-----------------------------------------------', 'director');
        Log::debug('Конец создания тикетов для DOING', 'director');

        // процесс уже исполняет Actor System User 0 !!!
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }

    public function DeactiveTicketsForDoing()
    {
        Log::debug('Начало деактивации тикетов для DOING', 'director');

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();


        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        $m->ManagedProcessExecutionRecord = 'urn:ManagedProcess:Execution:Record:'.$MPE_ID;
        $tickets = $m->deliver();

        foreach ($tickets as $ticket) {
            if ((string)$mpe->initiator != (string)$ticket->ManagementPostIndividual->urn) {
                Log::debug((string)$ticket->urn, 'rznasa');

                $m = new Message();
                $m->action = 'update';
                $m->urn = (string)$ticket->urn;
                $m->isvalid = false;
                $m->allowknowcuurentstage = false;
                $m->allowopen = false;
                $m->allowsave = false;
                $m->allowcomplete = false;
                $m->deliver();
            } else {
                Log::debug((string)$ticket->urn.' - тикет инициатора', 'director');
            }
        }



        Log::debug('-----------------------------------------------', 'director');
        Log::debug('Конец деактивации тикетов для DOING', 'director');

        // процесс уже исполняет Actor System User 0 !!!
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }


    public function CreateTicketsForCapaInspectionEditing()
    {
        Log::debug('Начало создания тикетов для CapaInspectionEditing', 'director');

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        $subjectURN = new URN((string)$mpe->subject);


        $urn = new URN((string)$subjectURN);
        $prototype = $urn->getPrototype();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->isprocess = false;
        $m->indomain = $prototype->getInDomain();
        $m->ofclass = $prototype->getOfClass();
        $m->oftype = $prototype->getOfType();

        $protoobject = $m->deliver();


        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        $m->ManagementPostIndividual = (string)$protoobject->approver->urn;
        $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
        $m->activateat = date("Y-m-d H:i:s", time());
        $m->allowknowcuurentstage = true;
        $m->allowopen = true;
        $m->allowsave = true;
        $m->allowcomplete = false;
        $m->deliver();


        Log::debug('Создан для - '.(string)$protoobject->approver->urn, 'director');
        Log::debug('Конец создания тикетов для CapaInspectionEditing', 'director');

        // процесс уже исполняет Actor System User 0 !!!
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }



    public function setCapaDefault(){

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();


        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        $m->ManagementPostIndividual = (string)$mpe->initiator;
        $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
        $MPETickets = $m->deliver();

        foreach($MPETickets as $MPETicket){
            $m = new Message();
            $m->action = 'delete';
            $m->urn = (string)$MPETicket->urn;
//            $m->isvalid = false;
//            $m->allowopen = false;
//            $m->allowsave = false;
//            $m->allowcomplete = false;
//            $m->allowcomment = false;
//            $m->allowreadcomments = false;
//            $m->allowknowcuurentstage = false;
//            $m->allowseejournal = false;
//            $m->allowearly = false;
            $m->deliver();
        }


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


        Log::debug('Ставим статусы капы в 0 ........', 'capa');

        foreach($corrections as $correction){


            $m = new Message();
            $m->action = 'update';
            $m->urn = (string)$correction->urn;
            $m->selecttype = false;
            $m->cancelstat = false;
            $m->selectsolution = false;
            $m->selectedsolution = '';
            $m->deliver();

            Log::debug('урн мероприятия = '.(string)$correction->urn, 'capa');

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Document:Solution:Correction';
            $m->DocumentCorrectionCapa = (string)$correction->urn;
            $solutions = $m->deliver();


            Log::debug('количество решений = '.count($solutions), 'capa');
            Log::debug('urn решений которым ставим approveded/ready = 0', 'capa');

            if(count($solutions) > 0) {
                foreach ($solutions as $solution) {
                    $m = new Message();
                    $m->action = 'update';
                    $m->urn = (string)$solution->urn;
                    $m->approveded = false;
                    $m->ready = false;
                    $m->deliver();

                    Log::debug((string)$solution->urn, 'capa');
                }
            }

        }

        Log::debug('......... done', 'capa');

    }
}