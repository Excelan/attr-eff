<?php

class CalendarApp extends WebApplication implements ApplicationAccessManaged
{
    function request($id){

    }

    function resource($id){

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

        $this->context['sop'] = $sop;
        $this->context['id'] = $sop->id;

        $subjectURN = new URN($mpe->subject);
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();

        $subject = $subjectURN->resolve();


        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'name'=> 'df' ,
            'title'=> 'Планирование',
            'modal'=>$this->modal($subjectURN)
        ));



    }




    function capa($id){

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

        $this->context['sop'] = $sop;
        $this->context['id'] = $sop->id;

        $subjectURN = new URN($mpe->subject);
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();

        $subject = $subjectURN->resolve();


        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'name'=> 'df' ,
            'title'=> 'Планирование',
            'modal'=>$this->modal($subjectURN)
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
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link($this->context['subject']),
            'attr'=>'modal_winname',
            'richtype'=>'Document',
            'button'=>'+добавить документ'
        ));

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>$subjectURN,
            'user'=> $this->user->id,
            'level'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$subjectURN
        ));

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
?>