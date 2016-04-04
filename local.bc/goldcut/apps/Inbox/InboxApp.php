<?php

class InboxApp extends WebApplication implements ApplicationAccessManaged
{
    public function request()
    {
        Utils::startTimer('load_run');
        // println($this->user); // юзер
        // println($this->employee); // сотрудник
        // println($this->managementrole); // должность
        // println($this->externalrole); // сотрудник контрагента (клиента)

        if (!count($this->managementrole)) {
            throw new Exception("Нет должности");
        }

        $m = new Message();
        $m->action = 'load';
        //$m->urn = 'urn:ManagedProcess:Execution:Record';
        //$m->currentactor = $this->managementrole->urn; // TODO Должность
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        $m->ManagementPostIndividual = $this->managementrole->urn; // TODO Должность
        $m->isvalid = true; // только активные
        $m->order = 'activateat DESC';
        $tickets = $m->deliver();

        $data = array();
        foreach ($tickets as $ticket) {
            try {
                array_push($data, $this->prepareDataRow($ticket));
            } catch (Exception $e) {
                println($e, 1, TERM_RED);
            }
        }
        $this->context['data'] = $data;

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI' => $this->context['currentURI'],
            'floatingButton' => $this->getFloatButton('add'),
            'tabs' => $this->tabs(),
            'title' => 'Входящие',
            'modal' => $this->modal()
        ));

        $this->context['head'] = $this->tableHead();
        $this->context['loadtime'] = Utils::reportTimer('load_run');
    }




    //Вспомагательные функции

    private function prepareDataRow($ticket, $type = '')
    {
        $mpe = $ticket->ManagedProcessExecutionRecord;
        if (!count($mpe)) {
            return [];
        }

        //$created = date('d-m-Y', $mpe->created);
        $created = substr($mpe->created, 0, 16);

        $menuOrNot = new MenuItems(array(
            ['icon' => 'database', 'func' => 'inbox_go_to_process', 'param' => $ticket->urn, 'title' => 'Просмотр']
        ));

        try {
            $upn = $mpe->urn;//new URN($data->urn);
        } catch (Exception $e) {
        }

//        println($mpe->prototype);
//        println($mpe->currentstage);
//        println($ticket->id);

        if ($mpe->prototype == 'DMS:Decisions:Plan' && $mpe->currentstage == 'Planning') {
            $actHREF = "/plan/dates?ticket={$ticket->id}"; // TODO Коля
        } elseif ($mpe->prototype == 'DMS:Correction:CAPA') {
            // TODO спец экраны Обучение, Календарь

            if ($mpe->currentstage == 'Considering') {
                $actHREF = "/process/act/{$ticket->id}";
            } // TICKET ID
            elseif ($mpe->currentstage == 'Correction') {
                $actHREF = "/capa/correction/{$ticket->id}";
            } else {
                $actHREF = "/process/act/{$ticket->id}";
            }
        } elseif ($mpe->prototype == 'DMS:Decisions:Visa' && strpos($mpe->subject, 'Document:Capa:Deviation') && $mpe->currentstage == 'Decision') {
            $actHREF = "/capa/vise/{$ticket->id}";
        } elseif ($mpe->prototype == 'DMS:Decisions:Approvement' && strpos($mpe->subject, 'Document:Capa:Deviation') && $mpe->currentstage == 'Approve') {
            $actHREF = "/capa/approving/{$ticket->id}";
        } else {
            $actHREF = "/process/act/{$ticket->id}"; // TICKET ID
        }
        //$actHREF = "/process/act/{$upn->uuid}"; // MPE ID
        //if ((string)$mpe->currentactor == (string)$this->managementrole->urn)
        if ($ticket->allowopen) {
            $actLINK = "<a href='$actHREF'>{$mpe->currentstage}</a>";
        } else {
            if ($this->managementrole->title == 'MaxPost') {
                if ($mpe->currentstage == 'Planing' || $mpe->currentstage == 'Vising' || $mpe->currentstage == 'Approving' || $mpe->currentstage == 'Doing' || $mpe->currentstage == 'Reviewing' || $mpe->currentstage == 'CallCP') {
                    $actLINK = "FORCE: {$mpe->currentstage}";
                } else {
                    $actLINK = "<a href='$actHREF'>FORCE: {$mpe->currentstage}</a>";
                }
            } else {
                $actLINK = "{$mpe->currentstage}";
            }
        }

        $subjectURN = new URN($mpe->subject);
        $subject = $subjectURN->resolve();

        $color = 'white';
        if ($mpe->done == 'y' || $mpe->done === true) {
            $color = 'green';
        }

        $row = [
            ['data' => $menuOrNot],
            ['data' => $created],
            //['data' => ],
            //['data' => $mpe->initiator .' / '.$subject->initiator],
            ['data' => $mpe->id],
            ['data' => $mpe->prototype],
            ['data' => $mpe->subject],
            ['data' => $actLINK] //, 'bgcolor' => $color
        ];
        return $row;
    }

    private function tableHead()
    {
        return ['', 'Дата',  'UPN ID', 'Процесс', 'Документ', 'Этап']; // 'Инициатор',
    }


    private function getFloatButton($param)
    {
        if ($param == 'add') {
            return ['title' => '', 'link' => '', 'role' => 'add', 'data-openwindow' => 'tosatelement', 'data-call' => 'selectchange'];
        }
    }


    private function modal()
    {
        return array(
            [
                'data-link1' => '/processrbac/processesICanStart',
                'data-link2' => '/processrbac/processesICanStart'
            ]
        );
    }

    private function tabs()
    {
        return array(
            ['title' => 'Все', 'link' => '/inbox'],
            ['title' => 'Черновики', 'link' => '/inbox/draft'],
            ['title' => 'Новые', 'link' => '/inbox/newcapa'],
            ['title' => 'Утвержденные', 'link' => '/inbox/alreadyapproving'],
            ['title' => 'Выполненые', 'link' => '/inbox/done'],
            ['title' => 'Отмененные', 'link' => '/inbox/returned']
        );
    }
}
