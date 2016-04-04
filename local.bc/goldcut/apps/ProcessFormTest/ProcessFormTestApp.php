<?php

class ProcessFormTestApp extends WebApplication implements ApplicationAccessManaged
{
    function exclusive()
    {
        $this->view = '../../Process/view/exclusive';

        if ($_GET['urn']) // test form with draft
        {
            Log::info($_GET['urn'], 'uniloadsave');
            $this->context['subjectURN'] = new URN($_GET['urn']);
        }

        // Показать блок визирования утверждения
        $this->context['decisionScreen'] = false;
        // показать CTA "Завершить этап"
        $this->context['dataController'] = 'nextenabled';

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'name'=>'Title',
            'title'=> 'Navigation',
            'modal'=>$this->modal()
        ));

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>$this->context['subjectURN'],
            'user'=> $this->user->id,
            'level'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>$this->context['subjectURN']
        ));

        //виджет правого блока
        $this->register_widget('rightbox', 'rightbox', array(
            'title'=>'Византы',
            'user'=>$this->user(),
            'attr'=>'modal_winname',
            'richtype'=>'ManagementPostIndividual',
            'button'=>'+добавить византа'
        ));

        //виджет правого блока
        $this->register_widget('link', 'rightbox', array(
            'title'=>'Документы',
            'link'=>$this->link(),
            'attr'=>'modal_winname',
            'richtype'=>'Document',
            'button'=>'+добавить документ'
        ));

        //виджет правого блока для статусов
        $this->register_widget('status', 'rightbox', array(
            'title'=>'Информация по объекту',
            'user'=>$this->status()
        ));

        $this->context['rand'] = rand(1000,9999);

        $this->context['tabs'] = $this->tabs();
        $this->context['floatingButton'] = $this->getFloatButton();
        $formpath = $this->uriComponents();
        array_shift($formpath);
        $this->context['formpath'] = join('/',$formpath);
    }

    private function getFloatButton($param)
    {
        if ($param == 'add') return ['title' => '', 'link' => '', 'role' => 'add', 'data-openwindow' => 'tosatelement', 'data-call' => 'selectchange'];
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

    private function user()
    {
        return array(
            ['name' => 'Джоли Анджелина', 'post' => 'Ответственный за обработку жалоб всех типов '],
            ['name' => 'Джоли Анджелина', 'post' => 'Ответственный за обработку жалоб всех типов '],
            ['name' => 'Джоли Анджелина', 'post' => 'Ответственный за обработку жалоб всех типов '],
            ['name' => 'Джоли Анджелина', 'post' => 'Ответственный за обработку жалоб всех типов '],
            ['name' => 'Джоли Анджелина', 'post' => 'Ответственный за обработку жалоб всех типов ']
        );
    }

    private function status()
    {
        $arr = explode(':',$this->context['subjectURN']);

        $m = new Message();
        $m->action = 'load';
        $m->urn = $this->context['subjectURN'];
        $subject = $m->deliver();

        $state = '';
        if($subject->privatedraft == 't') $state = 'privatedraft';
        else if($subject->returned == 't') $state = 'returned';
        else if($subject->done == 't') $state = 'done';
        else if($subject->archived == 't') $state = 'archived';
        else if($subject->vised == 't') $state = 'vised';
        else if($subject->approved == 't') $state = 'approved';
        else $state = 'no';

        return array(
            ['name' => 'Статус', 'post' => $state],
            ['name' => 'Дата саздания объекта', 'post' => date('Y-m-d H:i',$subject->created)],
            ['name' => 'ID объекта', 'post' => $arr[4]]
        );
    }

    private function link()
    {
        return array(
            ['link' => 'Документ №34545', 'href' => '#'],
            ['link' => 'Документ №34545', 'href' => '#'],
            ['link' => 'Документ №34545', 'href' => '#'],
            ['link' => 'Документ №34545', 'href' => '#']

        );
    }

    private function modal()
    {
        return array(
            [
                'data-link1' => '/processrbac/processesICanStart',
                'data-link2' => '/processrbac/processesICanStart',
                'urndoc'=>'urn-doc-32132132'
            ]
        );
    }
}

?>