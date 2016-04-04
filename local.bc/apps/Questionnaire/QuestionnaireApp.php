<?php

class QuestionnaireApp extends WebApplication implements ApplicationAccessManaged
{

    //таблица с опросниками
    function request()
    {
        Utils::startTimer('load_run');

        if (!count($this->managementrole)) throw new Exception("Нет должности");

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Document:Regulations:TA';
        $m->order = 'created DESC';
        $questionnaires = $m->deliver();

        $data = array();
        foreach ($questionnaires as $questionnaire) {
            array_push($data, $this->prepareDataRow($questionnaire));
        }
        $this->context['data'] = $data;

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI' => $this->context['currentURI'],
            'floatingButton' => $this->getFloatButton('add'),
            'tabs' => $this->tabs(),
            'title' => 'Список опросников',
            'modal' => $this->modal()
        ));

        $this->context['head'] = $this->tableHead();
        $this->context['loadtime'] = Utils::reportTimer('load_run');
    }


    //страница опросника по ід
    function resource($id){

        if (!count($this->managementrole)) throw new Exception("Нет должности");

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Feed:MPETicket:InboxItem:'.$id;
        $ticket = $m->deliver();

        if (!$ticket->allowopen) $this->redirect('/inbox');

        $this->context['ticketurn'] = $ticket->urn;
        $this->context['ticket'] = $ticket;

        $m = new Message();
        $m->action = 'load';
        $m->urn = (string)$ticket->ManagedProcessExecutionRecord->urn;
        $mpe = $m->deliver();

        $this->context['mpe'] = $mpe;


        $m = new Message();
        $m->action = 'load';
        $m->urn = $mpe->subject;
        $at = $m->deliver();

        $this->context['AT'] = $at;
        $this->context['id'] = $at->id;
        $id = $at->id;

        $subjectURN = new URN($mpe->subject);
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();

        $subject = $subjectURN->resolve();



        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'title'=> 'Тестирование',
        ));

        //виджет византов
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user(),
            'attr'=>'modal_static',
            'button'=>'+добавить византа'
        ));

        //виджет докумнетов
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link(),
            'attr'=>'modal_static',
            'button'=>'+добавить документ'
        ));

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=> 'urn:Document:Regulations:TA:'.$id,
            'user'=> $this->user->id,
            'level'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=> 'urn:Document:Regulations:TA:'.$id
        ));
    }


    function questionnaire(){


        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'stage'=>'Визирование','number'=>'RD_N 125'
        ));

        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user(),
            'attr'=>'modal_static',
            'button'=>'+добавить византа'
        ));

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link(),
            'attr'=>'modal_static',
            'button'=>'+добавить документ'
        ));
    }

    function calend($id){

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Feed:MPETicket:InboxItem:'.$id;
        $ticket = $m->deliver();

        $this->context['ticketurn'] = $ticket->urn;
        $this->context['ticket'] = $ticket;

        $m = new Message();
        $m->action = 'load';
        $m->urn = (string)$ticket->ManagedProcessExecutionRecord->urn;
        $mpe = $m->deliver();
        $this->context['mpe'] = $mpe;

        $m = new Message();
        $m->action = 'load';
        $m->urn = $mpe->subject;
        $sop = $m->deliver();

        $this->context['sop'] = $sop->DocumentRegulationsSOP;
        $this->context['id'] = $sop->DocumentRegulationsSOP->id;

        $subjectURN = new URN($mpe->subject);
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();

        $subject = $subjectURN->resolve();

        if(strlen($sop->DocumentRegulationsSOP->title) > 0) $nameW = $sop->DocumentRegulationsSOP->title; else $nameW = '-';

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'name'=> $nameW,
            'title'=> 'Планирование',
            'modal'=>$this->modal($subjectURN)
        ));

        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user($subject->DocumentRegulationsTA),
            'attr'=>'modal_winname',
            'richtype'=>'ManagementPostIndividual',
            'button'=>'+добавить византа'
        ));

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link($subject->DocumentRegulationsTA),
            'attr'=>'modal_winname',
            'richtype'=>'Document',
            'button'=>'+добавить документ'
        ));


        if($ticket->allowcomment) $allowcomment = 'FirstLevel';
        else $allowcomment = 'Access denied';

        if($ticket->allowreadcomments) $allowreadcomments = 'FirstLevel';
        else $allowreadcomments = 'Access denied';

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>$subject->DocumentRegulationsTA,
            'user'=> $this->user->id,
            'level'=>$allowcomment,
            'readcomments'=>$allowreadcomments
        ));


        if($ticket->allowseejournal) $allowseejournal = 'FirstLevel';
        else $allowseejournal = 'Access denied';

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$subject->urn,
            'level'=>$allowseejournal
        ));

    }


    function result($id){

        $this->context['id'] = $id;

        //загрузка результатов по ід опросника
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Study:RegulationStudy:R';
        $m->questionnaire = $id;
        if($_GET['post']){
            $m->user = $_GET['post'];
            $m->order = 'created desc';
            $m->last = 1;
        }
        $results = $m->deliver();
        $this->context['results'] = $results;


        $subjectURN = "urn:Document:Regulations:TA:".$this->uri(2);

        if($_GET['post']) {

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:People:Employee:Internal';
            $m->ManagementPostIndividual = 'urn:Management:Post:Individual:'.$_GET['post'];
            $useremployee = $m->deliver();

            $this->context['useremployee'] = $useremployee;

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Management:Post:Individual:'.$_GET['post'];
            $userpost = $m->deliver();
            $this->context['userpost'] = $userpost;

        }


        //загрузка опросника по ід
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Document:Regulations:TA:'.$id;
        $questionnaire = $m->deliver();
        $this->context['questionnaire'] = $questionnaire;

        //загрузка вопросов
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Study:RegulationStudy:Q';
        $m->DocumentRegulationsTA = $questionnaire->urn;
        $questions = $m->deliver();
        $this->context['questions'] = $questions;



        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'name'=> $questionnaire->DocumentRegulationsSOP->title,
            'title'=> 'Результат аттестации для сотрудников',
            'modal'=> $this->modal($subjectURN)
        ));

//        //виджет правого блока
//        $this->register_widget('rightbox', 'rightbox', array(
//            'title'=>'Византы',
//            'user'=>$this->user(),
//            'attr'=>'modal_static',
//            'button'=>'+добавить византа'
//        ));

        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user($this->context['subject']),
            'attr'=>'modal_winname',
            'richtype'=>'ManagementPostIndividual',
            'button'=>'+добавить византа'
        ));

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link(),
            'attr'=>'modal_static',
            'button'=>'+добавить документ'
        ));

        //виджет правого блока для статусов
        $this->register_widget('status', 'rightbox', array(
            'title'=>'Информация по объекту',
            'user'=>$this->status($id)
        ));
    }












    //Вспомагательные функции ------------------------------------------------------------------------------------------------------

    private function status($id)
    {

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Document:Regulations:TA:'.$id;
        $qa = $m->deliver();

        return array(
            ['name' => 'Статус', 'post' => 'new'],
            ['name' => 'Дата саздания объекта', 'post' => date('Y-m-d H:i',$qa->created)],
            ['name' => 'ID объекта', 'post' => $id]
        );
    }

    private function user($subject)
    {
        $visants = [];
        foreach ($subject->basevisants as $key => $value) {
            $postURN = new URN($value);
            $post = $postURN->resolve();
            $employee = $post->employee;
            if (count($employee))
                array_push($visants, ['name' => $employee->title, 'post' => $post->title]);
            else
                Log::error("No employee in post {$post->urn}", "strange");
        }
        foreach ($subject->additionalvisants as $key => $value) {
            $postURN = new URN($value);
            $post = $postURN->resolve();
            $employee = $post->employee;
            if (count($employee))
                array_push($visants, ['name' => $employee->title, 'post' => $post->title]);
            else
                Log::error("No employee in post {$post->urn}", "strange");
        }
        return $visants;
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



    private function getFloatButton($param)
    {
        if ($param == 'add') return ['title' => '', 'link' => '', 'role' => 'add', 'data-openwindow' => 'tosatelement', 'data-call' => 'selectchange'];
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

    private function tableHead()
    {
        return ['', 'Дата создания','Врямя на ответ','Проходной процент'];
    }


    private function prepareDataRow($questionnaire, $type = '')
    {
        if (!count($questionnaire)) return [];

        $created = date('d-m-Y', $questionnaire->created);

        $menuOrNot = new MenuItems(array(
            ['icon' => 'database', 'func' => 'tested_qa', 'param' => $questionnaire->id, 'title' => 'Аттестация'],
            ['icon' => 'database', 'func' => 'tested_qa_result', 'param' => $questionnaire->id, 'title' => 'Результаты']
        ));


        $row = [
            ['data' => $menuOrNot],
            ['data' => $created],
            ['data' => $questionnaire->time],
            ['data' => $questionnaire->percent]
        ];
        return $row;
    }


    private function tabs()
    {
        return array(
            ['title' => 'Все', 'link' => '/']
        );
    }

}