<?php

class CapaApp extends WebApplication implements ApplicationAccessManaged
{

    private function getFloatButton()
    {
        return ['title' => '/newcapa/create', 'link' => '/newcapa/create', 'role' => 'add']; // Создание капы
    }

    private function tabs()
    {
        return array(
            ['title' => 'Все', 'link' => '/newcapa'],
            ['title' => 'Черновики', 'link' => '/newcapa/draft'],
            ['title' => 'Новые', 'link' => '/newcapa/newnewcapa'],
            ['title' => 'Подтверждение главы отдела', 'link' => '/newcapa/needconfirmofdepartmentboss'],
            ['title' => 'Назначение исполнителя', 'link' => '/newcapa/needtosetrealization'],
            ['title' => 'Необходимо согласование инициатора', 'link' => '/newcapa/needtoauthormatching'],
            ['title' => 'Отправлено на визирование', 'link' => '/newcapa/needtovising'],
            ['title' => 'Отправлено на утверждение', 'link' => '/newcapa/needtoapproving'],
            ['title' => 'Утвержденные', 'link' => '/newcapa/alreadyapproving'],
            ['title' => 'Выполненые', 'link' => '/newcapa/done'],
            ['title' => 'Отмененные', 'link' => '/newcapa/returned']
        );
    }

    private function tableHead_capa()
    {
        return ['', 'Дата создания', 'Создатель', 'Утверждающий', 'Статус'];
    }

    private function prepareDataRow_capa($capa, $type = '')
    {
        $created = date('d-m-Y', $capa->created);

        if ( $type == 'needconfirmofdepartmentboss' ) {

            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'view_capa_defore_confirm', 'param' => $capa->id, 'title' => 'Подтвердить']
            ));

        } else if ( $type == 'needtosetrealization' ) {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'set_realization', 'param' => $capa->id, 'title' => 'Назначить исполнителей']
            ));

        } else if ( $type == 'needtoauthormatching' ) {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'view_authormatching_capa', 'param' => $capa->id, 'title' => 'Согласовать']
            ));

        } else if ( $type == 'needtovising' ) {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'vising', 'param' => $capa->id, 'title' => 'Завизировать']
            ));

        } else if ( $type == 'needtoapproving' ) {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'approving', 'param' => $capa->id, 'title' => 'Утвердить']
            ));

        } else if ( $type == 'alreadyapproving' ) {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'alreadyapproving', 'param' => $capa->id, 'title' => 'Просмотр']
            ));

        } else if ( $type == 'returned' ) {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'returned', 'param' => $capa->id, 'title' => 'Просмотр']
            ));

        } else {
            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'view_capa', 'param' => $capa->id, 'title' => 'Просмотр']
            ));
        }

        $row = [
            ['data' => $menuOrNot],
            ['data' => $created],
            ['data' => $capa->author_id],
            ['data' => $capa->approver_id],
            ['data' => $capa->workflowcapa]
        ];
        return $row;
    }



	function request()
	{
/*        Utils::startTimer('load_run');

        $data = array();

        $capa = $this->get_all_capa('request');

        foreach ($capa as $c) array_push($data, $this->prepareDataRow_capa($c));

        $this->context['data'] = $data;
        $this->context['tabs'] = $this->tabs();
        $this->context['head'] = $this->tableHead_capa();
        $this->context['floatingButton'] = $this->getFloatButton();

        $this->context['navtitle'] = 'Capa listing';

        $this->context['loadtime'] =  Utils::reportTimer('load_run');

        $this->register_widget('namespace', 'bodyaddclass', ['class' => __METHOD__]); // для индивидуализации css в рамках экрана
        $this->register_widget('title', 'pagetitle', array("title" => array('Создание капы')));

        $this->register_widget('header', 'header', array('title'=>'Все'));*/

    }




    //Views functions *********************************************************************************************************************************

    //етап дискуссии
    function correction($id)
    {
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
        $deviations = $m->deliver();

        $this->context['deviations'] = $deviations;


        //Идентифицированный риск
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($deviations->urn.':RiskManagementRiskApproved');
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:Approved';
        $m->in = $listMembers;
        $RiskManagementRiskApproved = $m->deliver();

        $this->context['RiskManagementRiskApproved'] = $RiskManagementRiskApproved;


        //Не идентифицированный риск
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($deviations->urn.':RiskManagementRiskNotApproved');
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:NotApproved';
        $m->in = $listMembers;
        $DocumentRiskNotApproved = $m->deliver();

        $this->context['DocumentRiskNotApproved'] = $DocumentRiskNotApproved;


        $subjectURN = new URN($mpe->subject);
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'title'=>'Обсуждение CAPA',
            'buttonMoreInfoHeader' => 1
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
            'level'=>'FirstLevel',
            'readcomments'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$subjectURN,
            'level'=>'FirstLevel'
        ));

    }

    function vise($id)
    {
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
        $deviations = $m->deliver();

        $this->context['deviations'] = $deviations;


        //Идентифицированный риск
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($deviations->urn.':RiskManagementRiskApproved');
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:Approved';
        $m->in = $listMembers;
        $RiskManagementRiskApproved = $m->deliver();

        $this->context['RiskManagementRiskApproved'] = $RiskManagementRiskApproved;


        //Не идентифицированный риск
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($deviations->urn.':RiskManagementRiskNotApproved');
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:NotApproved';
        $m->in = $listMembers;
        $DocumentRiskNotApproved = $m->deliver();

        $this->context['DocumentRiskNotApproved'] = $DocumentRiskNotApproved;

        $this->context['id'] = $deviations->id;

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'title'=>'Обсуждение CAPA',
            'buttonMoreInfoHeader' => 1
        ));

        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user($deviations),
            'attr'=>'modal_winname',
            'richtype'=>'ManagementPostIndividual',
            'button'=>'+добавить византа'
        ));

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link($deviations),
            'attr'=>'modal_winname',
            'richtype'=>'Document',
            'button'=>'+добавить документ'
        ));

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>$deviations->urn,
            'user'=> $this->user->id,
            'level'=>'FirstLevel',
            'readcomments'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$deviations->urn,
            'level' => 'FirstLevel'
        ));

    }


    function approving($id)
    {
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
        $deviations = $m->deliver();

        $this->context['deviations'] = $deviations;


        //Идентифицированный риск
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($deviations->urn.':RiskManagementRiskApproved');
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:Approved';
        $m->in = $listMembers;
        $RiskManagementRiskApproved = $m->deliver();

        $this->context['RiskManagementRiskApproved'] = $RiskManagementRiskApproved;


        //Не идентифицированный риск
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($deviations->urn.':RiskManagementRiskNotApproved');
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:NotApproved';
        $m->in = $listMembers;
        $DocumentRiskNotApproved = $m->deliver();

        $this->context['DocumentRiskNotApproved'] = $DocumentRiskNotApproved;

        $this->context['id'] = $deviations->id;

        $subjectURN = new URN($mpe->subject);
        $this->context['subjectURN'] = $subjectURN;
        $this->context['subject'] = $subjectURN->resolve();


        //виджет хедера
        $this->register_widget('header', 'header', array(
            'title'=>'Утверждение CAPA',
            'buttonMoreInfoHeader' => 1
        ));


        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user($deviations),
            'attr'=>'modal_winname',
            'richtype'=>'ManagementPostIndividual',
            'button'=>'+добавить византа'
        ));

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link($deviations),
            'attr'=>'modal_winname',
            'richtype'=>'Document',
            'button'=>'+добавить документ'
        ));

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>$deviations->urn,
            'user'=> $this->user->id,
            'level'=>'FirstLevel',
            'readcomments'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$deviations->urn,
            'level' => 'FirstLevel'
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
































    //Helpers functions*******************************************************************************************************************************************





}

?>
