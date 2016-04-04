<?php

class ProcessApp extends WebApplication implements ApplicationAccessManaged
{
    public function exclusive()
    {
        //$this->layout = 'nextgen';

        if (!is_numeric($this->uri(2)) xor $_GET['urn']) {
            throw new Exception("No numeric process id");
        }

        // real mode - /process/act/mpe_id

        $ticketId = $this->uri(2);
        if ($ticketId && is_numeric($ticketId)) {
            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Feed:MPETicket:InboxItem:' . $ticketId;
            $ticket = $m->deliver();
            $mpe = $ticket->ManagedProcessExecutionRecord;
        } else {
            throw new Exception("NO VALID ticketId: $ticketId");
        }

        /*
        $mpeid = $this->uri(2);
        if ($mpeid && is_numeric($mpeid)) {
            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:ManagedProcess:Execution:Record:' . $this->uri(2);
            //$m->currentactor = $this->managementrole->urn; // on visa im not current actor
            // TODO check ticket!
            $mpe = $m->deliver();
        }
        else
        {
            throw new Exception("NO VALID MPEID: $mpeid");
        }
        */
        if (!count($mpe)) {
            throw new Exception("NO TICKET FOR $ticketId");
        }


        /*
         * <status name="allowopen" default="no" title="allowopen"/>
        <status name="allowsave" default="no" title="allowsave"/>
        <status name="allowcomplete" default="no" title="allowcomplete"/>
        <status name="allowcomment" default="no" title="allowcomment"/>
        <status name="allowreadcomments" default="no" title="allowreadcomments"/>
        <status name="allowknowcuurentstage" default="no" title="allowknowcuurentstage"/>
        <status name="allowseejournal" default="no" title="allowseejournal"/>
         */


        $this->context['mpeurn'] = $mpe->urn;
        $this->context['ticketurn'] = $ticket->urn;
        $this->context['processproto'] = $mpe->prototype;
        $this->context['currentstage'] = $mpe->currentstage;

        // Показать блок визирования утверждения
        $this->context['decisionScreen'] = ($mpe->currentstage == 'Decision' || $mpe->currentstage == 'Approve' || $mpe->currentstage == 'Review') ? true : false;

        if ($this->managementrole->title == 'MaxPost') {
            $this->context['allowsave'] = true;
            $this->context['allowcomplete'] = true;
            $this->context['allowcomment'] = true;
            $this->context['allowreadcomments'] = true;
            $this->context['allowseejournal'] = true;
            $this->context['allowearly'] = true;
        } else {
            $this->context['allowsave'] = $ticket->allowsave;
            $this->context['allowcomplete'] = $ticket->allowcomplete;
            $this->context['allowcomment'] = $ticket->allowcomment;
            $this->context['allowreadcomments'] = $ticket->allowreadcomments;
            $this->context['allowseejournal'] = $ticket->allowseejournal;
            $this->context['allowearly'] = $ticket->allowearly;
        }


        if ($mpe->currentstage == 'DoingTask' || $mpe->currentstage == 'Review') {
            $this->context['allowsave'] = false;
        }

        // показать CTA "Завершить этап"
        if (!$this->context['decisionScreen']) {
            $this->context['dataController'] = 'nextenabled';
        }

        $subjectURN = new URN($mpe->subject);
        Log::info((string)$subjectURN, 'uniloadsave');
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();
        //println($mpe->currentstage);
        //printlnd($subjectURN);
        //println($mpe->prototype);

        $subjectURN = new URN($subjectURN);
        $subjectProto = $subjectURN->getPrototype();
        $subjectClass = $subjectProto->getOfClass();
        $subjectType = $subjectProto->getOfType();
        $stage = $mpe->currentstage;

        // FORM PATH
        $stageEffective = $stage;
        if ($stage == 'Approve') {
            $stageEffective = 'Decision';
            $this->context['decisiontype'] = 'Approve';
            $this->context['decisiongate'] = '/Decision/ApproveByOne';
        } elseif ($stage == 'Decision') {
            $this->context['decisiontype'] = 'Visa';
            $this->context['decisiongate'] = '/Decision/VisaByOne';
        } elseif ($stage == 'Review') {
            $this->context['decisiontype'] = 'Review';
            $this->context['decisiongate'] = '/Decision/ReviewByOne';
        }
        if ($stage == 'Review') {
            $stageEffective = 'DoingTask';
        }

        $formpath = $subjectClass.'/'.$stageEffective.'/'.$subjectClass.'_'.$subjectType;
        $this->context['formpath'] = $formpath;

        $pageTitle = $E = Entity::ref((string)$subjectURN->getPrototype())->title['ru'];

        $pageNavTitle = $GLOBALS['MPE'][$mpe->prototype][$mpe->currentstage]; // TODO

        $this->register_widget('title', 'pagetitle', ['title'=>array($pageNavTitle, $pageTitle)]);

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'name'=> $pageTitle,
            'title'=> $pageNavTitle . ' ' . $this->context['subject']->code, // TODO Куда код ставить? (Коля)
            'modal'=>$this->modal($subjectURN)
        ));


        if($ticket->allowcomment) $allowcomment = 'FirstLevel';
        else $allowcomment = 'Access denied';

        if($ticket->allowreadcomments) $allowreadcomments = 'FirstLevel';
        else $allowreadcomments = 'Access denied';

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>$subjectURN,
            'user'=> $this->user->id,
            'level'=>'FirstLevel',
            //'level'=>$allowcomment,
            //'readcomments'=>$allowreadcomments //todo вернуть обратно после того как в тикетах будут нормально проставлены права
            'readcomments'=>'FirstLevel'
        ));


        if($ticket->allowseejournal) $allowseejournal = 'FirstLevel';
        else $allowseejournal = 'Access denied';

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$subjectURN,
            //'level'=>$allowseejournal //todo вернуть обратно после того как в тикетах будут нормально проставлены права
            'level'=>'FirstLevel'
        ));

        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user($this->context['subject']),
            'attr'=>'modal_winname',
            'richtype'=>'ManagementPostIndividual',
            'button'=>'+добавить византа'
        ));


        //виджет правого блока
        $this->register_widget('approver', 'rightbox', array(
            'title'=>'Утверждающий',
            'user'=>$this->approver($subjectURN)

        ));

        //блок скрытых коментов отмены
        $this->register_widget('lastcomment', 'lastcomments', array());

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link($this->context['subject']),
            'attr'=>'modal_winname',
            'richtype'=>'Document',
            'button'=>'+добавить документ'
        ));


        try {
            $urn = new URN($this->context['subject']->urn);
            $prototype = $urn->getPrototype();

            $m = new \Message();
            $m->action = 'load';
            $m->urn = 'urn:Definition:Prototype:Document';
            $m->isprocess = false;
            $m->indomain = $prototype->getInDomain();
            $m->ofclass = $prototype->getOfClass();
            $m->oftype = $prototype->getOfType();

            $protoobject = $m->deliver();

            //$pdfLatex = buildLatexPDF($urn);
            //$latex = $pdfLatex[0];
            //$pdfURI = $pdfLatex[1];
            //$this->context['pdf'] = "<a style='color: #555;' href='$pdfURI'>PDF</a>";
            if ($protoobject->withhardcopy) {
                $this->context['pdf'] = true;
            }
        } catch (Exception $e) {
            println($e->getMessage(), 1, TERM_RED);
        }



        $this->context['tabs'] = $this->tabs();
        $this->context['floatingButton'] = $this->getFloatButton($param);
    }

    private function getFloatButton($param)
    {
        if ($param == 'add') {
            return ['title' => '', 'link' => '', 'role' => 'add','data-openwindow'=>'tosatelement', 'data-call'=>'selectchange'];
        }
    }

    private function tabs()
    {
        return array(
            ['title' => 'Все', 'link' => '/capa'],
            ['title' => 'Черновики', 'link' => '/capa/draft'],
            ['title' => 'Новые', 'link' => '/capa/newcapa'],
            ['title' => 'Утвержденные', 'link' => '/capa/alreadyapproving'],
            ['title' => 'Выполненые', 'link' => '/capa/done'],
            ['title' => 'Отмененные', 'link' => '/capa/returned']
        );
    }

    private function user($subject)
    {
        $visants = [];
        foreach ($subject->basevisants as $key => $value) {
            $postURN = new URN($value);
            $post = $postURN->resolve();
            $employee = $post->employee;
            if (count($employee)) {
                array_push($visants, ['name' => $employee->title, 'post' => $post->title]);
            } else {
                Log::error("No employee in post {$post->urn}", "strange");
            }
        }
        foreach ($subject->additionalvisants as $key => $value) {
            $postURN = new URN($value);
            $post = $postURN->resolve();
            $employee = $post->employee;
            if (count($employee)) {
                array_push($visants, ['name' => $employee->title, 'post' => $post->title]);
            } else {
                Log::error("No employee in post {$post->urn}", "strange");
            }
        }
        return $visants;
    }

    private function approver($subject)
    {
        $approv = [];

        $subjectURN = new \URN($subject);
        $subjectProto = $subjectURN->getPrototype();

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->indomain = $subjectProto->getInDomain();
        $m->ofclass = $subjectProto->getOfClass();
        $m->oftype = $subjectProto->getOfType();
        $definitionProto = $m->deliver();


        $employee = $definitionProto->approver->employee;

        if (count($employee)) {
            array_push($approv, ['name' => $employee->title, 'post' => $definitionProto->approver->title]);
        } else {
            Log::error("No employee in post {$definitionProto->approver->urn}", "strange");
        }

        return $approv;
    }



    private function link($subject)
    {
        $docs = array(
            ['link' => $subject->code, 'href' => '#']
        );
        foreach ($subject->related as $value) {
            $docURN = new URN($value);
            $doc = $docURN->resolve();
            array_push($docs, ['link' => $doc->code, 'href' => '#']);
        }
        return $docs;
    }

    private function modal($urn)
    {
        return array(
            [
                'data-link1' => '/processrbac/processesICanStart',
                'data-link2' => '/processrbac/processesICanStart',
                'urndoc' => $urn
            ]
        );
    }
}
