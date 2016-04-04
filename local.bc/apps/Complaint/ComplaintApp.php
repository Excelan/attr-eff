<?php

class ComplaintApp extends WebApplication implements ApplicationFreeAccess //ApplicationAccessManaged
{

    private function getFloatButton()
    {
        return ['title' => '/capa/create', 'link' => '/capa/create', 'role' => 'add']; // Создание капы
    }

    private function tabs()
    {
        return array(
            ['title' => 'Мои жалобы', 'link' => '/complaint']
        );
    }

    private function tableHead_complaint()
    {
        return ['', 'Дата создания','№ Документа', 'Тип Жалобы', 'Текущий статус','Были ли приняты меры'];
    }

    private function prepareDataRow_complaint($complaint, $type = '')
    {
        $created = date('d-m-Y', $complaint->created);


        if($complaint->isresolved == 1) $isresolved = 'Были приняты';
        else $isresolved = '-';

        if ( $type == 'needconfirm' ) {


        } else {

            $menuOrNot = new MenuItems(array(
                ['icon' => 'create', 'func' => 'view_complaint', 'param' => $complaint->mirror_id, 'title' => 'Просмотр']
            ));
        }

        $row = [
            ['data' => $menuOrNot],
            ['data' => $created],
            ['data' => $complaint->mirror_id],
            ['data' => $complaint->complaintworkflow],
            ['data' => $isresolved],
            ['data' => $isresolved]
        ];
        return $row;
    }

    function view($id){

        if ( !$id ) $this->redirect('/complaint');

        Utils::startTimer('load_run');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:complaint';
        $m->mirror = $id;
        $complaint = $m->deliver();
        $this->context['complaint'] = $complaint;

        if ( !count($complaint) ) $this->redirect('/complaint');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:user';
        $m->id = $complaint->autor_id;
        $author = $m->deliver();
        $this->context['author'] = $author;

        $this->context['iscreateprotocol']=false;
        $this->context['objectstatus']="обработаная";
        if($complaint->complaintworkflow=="new"){
            $this->context['objectstatus']="новая";
        }

        $this->context['isisresolve']=false;
        $this->context['ishidemanagerbtns']=true;
        $this->context['aditionalfieldtittle']=$complaint->complainttype->titleforfield;


        if(!$complaint->isresolved){ $this->context['isisresolve']=true;}
        if($complaint->complaintworkflow=="new"){ $this->context['iscreateprotocol']=true;}
        if($complaint->complainttype->responsible->user_id == $this->user->id){  $this->context['ishidemanagerbtns']=false;}


        $post = loadEntity("urn-post", ["user"=>$this->user->id]);
        $this->context['autorTitle']="";


        if(count($post)>0){
            $this->context['autorTitle']=$post->name;
        }


        $this->context['id'] = $complaint->mirror_id;

        $this->context['tabs'] = $this->tabs();
        $this->context['head'] = $this->tableHead_complaint();
        $this->context['floatingButton'] = $this->getFloatButton();

        $this->context['navtitle'] = 'Complaint view';

        $this->context['loadtime'] =  Utils::reportTimer('load_run');

        $this->register_widget('namespace', 'bodyaddclass', ['class' => __METHOD__]); // для индивидуализации css в рамках экрана
        $this->register_widget('title', 'pagetitle', array("title" => array('Просмотр жалобы')));
    }


    function request() {

        Utils::startTimer('load_run');

        $data = array();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:bo';
        $bo = $m->deliver();

        foreach ($bo as $c) {

            array_push($data, $this->prepareDataRow_complaint($c));
        }

        $this->context['data'] = $data;
        $this->context['tabs'] = $this->tabs();
        $this->context['head'] = $this->tableHead_complaint();
        $this->context['floatingButton'] = $this->getFloatButton();

        $this->context['navtitle'] = 'Complaint listing';

        $this->context['loadtime'] =  Utils::reportTimer('load_run');

        $this->register_widget('namespace', 'bodyaddclass', ['class' => __METHOD__]); // для индивидуализации css в рамках экрана
        $this->register_widget('title', 'pagetitle', array("title" => array('Жалобы')));
        $this->register_widget('header', 'header', array('title'=>'Жалобы'));
    }

}