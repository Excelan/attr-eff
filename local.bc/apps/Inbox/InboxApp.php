<?php

class InboxApp extends WebApplication implements ApplicationAccessManaged
{
    public function request()
    {
        Utils::startTimer('load_run');
        //println($this->user); // юзер
        //println($this->employee); // сотрудник
        //println($this->managementrole); // должность
        //println($this->externalrole); // сотрудник контрагента (клиента)

        if (!count($this->managementrole)) {
            throw new Exception("Нет должности");
        }



        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Document:Regulations:SOP';
        $m->trainingdocument = 'yes';
        $sops = $m->deliver();

        $tas = array();
        foreach ($sops as $sop) {
            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Document:Regulations:TA';
            $m->trainer = $this->managementrole->urn;
            $m->DocumentRegulationsSOP = (string)$sop->urn;
            $ta = $m->deliver();

            if (count($ta) > 0) {
                $tas[] = $ta;
            }
        }

        $this->context['tas'] = $tas;

        // // INBOX
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Feed:MPETicket:InboxItem';
        //$m->currentactor = $this->managementrole->urn; // TODO Должность
        $m->ManagementPostIndividual = $this->managementrole->urn; // Должность
        $m->isvalid = true; // только активные
        $m->order = 'activateat DESC';
        $tickets = $m->deliver();
        //
        $data = array();
        foreach ($tickets as $ticket) {
            try {
                $item = $this->prepareDataRowInbox($ticket);
                $data[$item['date']][] = $item;
            //array_push($data[$item['date']], $item);
            } catch (Exception $e) {
                println($e, 1, TERM_RED);
            }
        }
        $this->context['inbox'] = $data;

        // // UNIDOCS
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:DMS:Document:Universal';
        // TODO by RBAC
        $m->order = 'created DESC';
        $items = $m->deliver();
        //
        $data = array();
        foreach ($items as $dataitem) {
            try {
                $item = $this->prepareDataRowUnidoc($dataitem);
                $data[$item['docClassTitle']][] = $item;
            //array_push($data[$item['date']], $item);
            } catch (Exception $e) {
                println($e, 1, TERM_RED);
            }
        }
        $this->context['unidocs'] = $data;


        // // myEmployeeInboxes - мои сотрудники
        $datae = [];

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Individual';
        $m->managedbypost = $this->managementrole->urn; // у которых начальник - юзер запроса
        $m->isactive = true; // только активные
        $m->order = 'title ASC';
        $postindividuals = $m->deliver();
        //println($postindividuals);
        foreach ($postindividuals as $postindividual) {
            //println($postindividual,1,TERM_GREEN);

          $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Feed:MPETicket:InboxItem';
            $m->ManagementPostIndividual = $postindividual->urn; // Должность следующего подчиненного
          $m->isvalid = true; // только активные
          $m->order = 'activateat DESC';
            $tickets = $m->deliver();
          //
          $data = array();
            foreach ($tickets as $ticket) {
                try {
                    $item = $this->prepareDataRowInbox($ticket);
                    $data[] = $item; // без группировки по датам
                } catch (Exception $e) {
                    println($e, 1, TERM_RED);
                }
            }

            $datae[] = ['post' => $postindividual, 'inboxes' => $data ];
        }

        $this->context['myEmployeeInboxes'] = $datae;


        // processEvents
        /*
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:';
        $m->order = ' DESC';
        $items = $m->deliver();
        //
        $data = array();
        foreach ($items as $dataitem) {
          try {
            $item = $this->prepareDataRowEvent($dataitem);
            $data[$item['date']][] = $item;
            //array_push($data[$item['date']], $item);
          }
          catch (Exception $e)
          {
            println($e,1,TERM_RED);
          }
        }
        $this->context['unidocs'] = $data;
        */

        $this->register_widget('title', 'pagetitle', ['title'=>array('Входящие')]);

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

    private function prepareDataRowUnidoc($unidoc, $type = '')
    {
        $created = substr($unidoc->created, 0, 16);

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->isprocess = false;
        $m->indomain = explode(':', $unidoc->document)[1];
        $m->ofclass = explode(':', $unidoc->document)[2];
        $m->oftype = explode(':', $unidoc->document)[3];
        //println($m);
        $protoobject = $m->deliver();
        if (!count($protoobject)) {
            //println($protoobject,1,TERM_RED);
        } else {
            //println($protoobject);
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:DocumentClass:ForPrototype';
        $m->name = explode(':', $unidoc->document)[2];
        //println($m);
        $docclassobject = $m->deliver();
        if (!count($docclassobject)) {
            $docClassTitle = 'NONE';
          //println($docclassobject,1,TERM_RED);
        } else {
            $docClassTitle = $docclassobject->title;
          //println($docclassobject);
        }


        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:DMS:Acquaintance:Document';
        $m->DMSDocumentUniversal = $unidoc->urn;
        $m->ManagementPostIndividual = $this->managementrole->urn;
        $seenNeededDS = $m->deliver();
        // $seenNeededDS = $unidoc->DMSAcquaintanceDocument;
        $seenNeeded = null;
        if (count($seenNeededDS)) {
            $seenNeeded = !$seenNeededDS->done;
        }
        
        $row = [
            'date' => date('d-m-Y', strtotime($created)),
            'datetime' => $created,
            'id' => $mpe->id,
            'prototype' => $protoobject,
            'code' => $unidoc->code,
            'actHREF' => $actHREF,
            'actLINK' => $actLINK,
            'docClassTitle' => $docClassTitle,
            'seenNeeded' => $seenNeeded
        ];
        return $row;
    }



    private function prepareDataRowInbox($ticket, $type = '')
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
        /*
         println($mpe->prototype);
         println($mpe->currentstage);
         println($ticket->id);
         println($mpe->metadata);
         println($mpe->metadata->parent);
         println('-----------------------------');
         println($ticket->allowopen);
       */
        if ($mpe->prototype == 'DMS:Decisions:Plan' && strpos($mpe->metadata->parent, 'DMS:Correction:CAPAInspection')) {
            $actHREF = "/calendar/capa/".$ticket->id.'?datefield=eventtime';
        } elseif ($mpe->prototype == 'DMS:Decisions:Plan' && strpos($mpe->metadata->parent, 'DMS:Process:SimpleWithPlan')) {
            $actHREF = "/calendar/".$ticket->id.'?datefield=currentcheck';
        } elseif (strpos($mpe->subject, 'Document:Regulations:ASR')) {
            if ($mpe->prototype == 'DMS:Decisions:Plan' && $mpe->currentstage == 'Planning') {
                $actHREF = "/questionnaire/calend/".$ticket->id.'?datefield=currentcheck';
            } // отдельно для обучения

            elseif ($mpe->prototype == 'DMS:Attestations:Test' && $mpe->currentstage == 'Testing') {
                $actHREF = "/questionnaire/".$ticket->id;
            } else {
                $actHREF = "/process/act/{$ticket->id}";
            } // TICKET ID
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
        } elseif ($mpe->prototype == 'DMS:Decisions:Visa' && strpos($mpe->subject, 'Document:Capa:Deviation') && $mpe->currentstage == 'Decision' && !strpos($mpe->metadata->parent, 'DMS:Correction:CAPAInspection')) {
            $actHREF = "/capa/vise/{$ticket->id}";
        } elseif ($mpe->prototype == 'DMS:Decisions:Approvement' && strpos($mpe->subject, 'Document:Capa:Deviation') && $mpe->currentstage == 'Approve' && !strpos($mpe->metadata->parent, 'DMS:Correction:CAPAInspection')) {
            $actHREF = "/capa/approving/{$ticket->id}";
        } elseif ($mpe->prototype == 'DMS:Regulation:Attestation' && $mpe->currentstage == "Testing") {
            $actHREF = "/questionnaire/{$ticket->id}";
        } else {
            $actHREF = "/process/act/{$ticket->id}"; // TICKET ID
        }
        //$actHREF = "/process/act/{$upn->uuid}"; // MPE ID
        //if ((string)$mpe->currentactor == (string)$this->managementrole->urn)
        if ($ticket->allowopen) {
            $actLINK = '';
        } else {
            if ($this->managementrole->title == 'MaxPost') {
                if ($mpe->currentstage == 'Planing' || $mpe->currentstage == 'Vising' || $mpe->currentstage == 'Approving' || $mpe->currentstage == 'Doing' || $mpe->currentstage == 'Reviewing' || $mpe->currentstage == 'CallCP') {
                    $force = "FORCE: {$mpe->currentstage}";
                } else {
                    $force = "FORCE";
                }
            } else {
                $actHREF = "#";
                $actLINK = 'class="disabled"';
            }
        }

        $subjectURN = new URN($mpe->subject);
        $subject = $subjectURN->resolve();

        $color = 'white';
        if ($mpe->done == 'y' || $mpe->done === true) {
            $color = 'green';
        }

        //$mpe->subject
        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->isprocess = false;
        $m->indomain = explode(':', $mpe->subject)[1];
        $m->ofclass = explode(':', $mpe->subject)[2];
        $m->oftype = explode(':', $mpe->subject)[3];
        $protoobject = $m->deliver();

        $title = $protoobject->title;
        if (strpos($mpe->metadata->parent, 'DMS:Correction:CAPAInspection')) {
            $title = 'Контрольная инспекция по САРА';
        }

        $row = [
            'date' => date('d-m-Y', strtotime($created)),
            'datetime' => $created,
            'id' => $mpe->id,
            'prototype' => $mpe->prototype,
            'subject' => $title, // $mpe->subject
            'actHREF' => $actHREF,
            'actLINK' => $actLINK,
            'force' => ($_GET['force']) ? $force : null,
            'stage' => $GLOBALS['MPE'][$mpe->prototype][$mpe->currentstage]
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
        return [
            ['title' => 'Кабинет', 'link' => '/inbox'],
            ['title' => 'Входящие документы', 'link' => '/inboxfeed']
        ];

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
