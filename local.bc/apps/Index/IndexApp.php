<?php

class IndexApp extends WebApplication implements ApplicationAccessManaged
{

    function textarea()
    {

    }
    function calend(){

        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследования етап1'));
    }

    function newuser(){

        $this->context['user'] = $this->user;

        //виджет правого блока
        /*
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
        */

        // $this->context['floatingButton'] = $this->getFloatButton();

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'stage'=>'Администрирование',
            //'number'=>'RD_N 125',
            'name'=>'Внесение существующего сотрудника',
            'modal'=>$this->modal()
        ));

    }

    function newdepartment(){

        $this->context['user'] = $this->user;

        //виджет хедера
        $this->register_widget('header', 'header', array(
            'currentURI'=>$this->context['currentURI'],
            'add'=>$this->getFloatButton('add'),
            'stage'=>'Администрирование',
            //'number'=>'RD_N 125',
            'name'=>'Внесение департаментов и отделов',
            'modal'=>$this->modal()
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



    function questionnaireresult($id){


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

        //виджет правого блока для статусов
        $this->register_widget('status', 'rightbox', array(
            'title'=>'Информация по объекту',
            'user'=>$this->status($id)
        ));
    }

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

    function test(){
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследования етап1'));

        //виджет комментариев
        $this->register_widget('comments', 'comments', array(
            'urn'=>'urn:Document:Claim:R_QDC:1097264970',
            'user'=>$this->user->id,
            'level'=>'FirstLevel'
        ));

        //виджет журнала
        $this->register_widget('journal', 'journal', array(
            'urn'=>'urn:Document:Detective:C_IS:33849'
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

    function account(){
        //$this->register_widget('header', 'header', array('title'=>'Аккаунт'));

        if (!count($this->managementrole)) throw new Exception("Нет должности");

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
          }
          catch (Exception $e)
          {
            println($e,1,TERM_RED);
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

    }

    private function prepareDataRow($ticket, $type = '')
    {
        $mpe = $ticket->ManagedProcessExecutionRecord;
        if (!count($mpe)) return [];

        //$created = date('d-m-Y', $mpe->created);
        $created = substr($mpe->created,0,16);

        $menuOrNot = new MenuItems(array(
            ['icon' => 'database', 'func' => 'inbox_go_to_process', 'param' => $ticket->urn, 'title' => 'Просмотр']
        ));

        try {
            $upn = $mpe->urn;//new URN($data->urn);
        }
        catch (Exception $e) {

        }

    //        println($mpe->prototype);
    //        println($mpe->currentstage);
    //        println($ticket->id);

        if ($mpe->prototype == 'DMS:Decisions:Plan' && $mpe->currentstage == 'Planning')
        {
          $actHREF = "/plan/dates?ticket={$ticket->id}&setfieldinsubject=planneddate"; // TODO Коля
        }
        elseif ($mpe->prototype == 'DMS:Correction:CAPA') // TODO спец экраны Обучение, Календарь
        {
            if($mpe->currentstage == 'Considering')  $actHREF = "/process/act/{$ticket->id}"; // TICKET ID
            elseif($mpe->currentstage == 'Correction') $actHREF = "/capa/correction/{$ticket->id}";
            else $actHREF = "/process/act/{$ticket->id}";
        }
        elseif($mpe->prototype == 'DMS:Decisions:Visa' && strpos($mpe->subject,'Document:Capa:Deviation') && $mpe->currentstage == 'Decision'){
            $actHREF = "/capa/vise/{$ticket->id}";
        }
        elseif($mpe->prototype == 'DMS:Decisions:Approvement' && strpos($mpe->subject,'Document:Capa:Deviation') && $mpe->currentstage == 'Approve'){
            $actHREF = "/capa/approving/{$ticket->id}";
        }
        else {
          $actHREF = "/process/act/{$ticket->id}"; // TICKET ID
        }
        //$actHREF = "/process/act/{$upn->uuid}"; // MPE ID
        //if ((string)$mpe->currentactor == (string)$this->managementrole->urn)
        if ($ticket->allowopen)
            $actLINK = "<a href='$actHREF'>{$mpe->currentstage}</a>";
        else {
            if ($this->managementrole->title == 'MaxPost')
            {
                if ($mpe->currentstage == 'Planing' || $mpe->currentstage == 'Vising' || $mpe->currentstage == 'Approving' || $mpe->currentstage == 'Doing' || $mpe->currentstage == 'Reviewing' || $mpe->currentstage == 'CallCP')
                    $actLINK = "FORCE: {$mpe->currentstage}";
                else
                    $actLINK = "<a href='$actHREF'>FORCE: {$mpe->currentstage}</a>";
            }
            else
                $actLINK = "{$mpe->currentstage}";
        }

        $subjectURN = new URN($mpe->subject);
        $subject = $subjectURN->resolve();

        $color = 'white';
        if ($mpe->done == 'y' || $mpe->done === true)
          $color = 'green';

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
    private function tabs()
    {
        return [
            ['title' => 'Кабинет', 'link' => '/inbox'],
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





	function request()
	{
        $this->redirect('/inbox');
  //    $this->register_widget('title', 'pagetitle', array("title" => array('Biocon ERP')));

//      $file_path = BASE_DIR."/doctohtml.odt";
//      $file_html_path = BASE_DIR."/doctohtml.html";

//      exec("abiword --to=html {$file_path} --exp-props='html5: yes; embed-css: yes'");

//      $html = file_get_contents($file_html_path);

//      println($html);

//      $options = array();
//      $options['HTML.Allowed'] = 'p,br,i,b,strong,em,table,tr,td,a';

//      $filtered_html = Security::html_purify($html, $options);

//      file_put_contents($file_html_path, $filtered_html);
	}

	function formgather()
	{
//		$this->layout = 'pure';
	}

    function form3()
    {
    }

    function doc() {

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:user';
        $user = $m->deliver();

        foreach ($user as $u) {

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:document';
            $m->last = 1;
            $doc = $m->deliver();

            $general = $this->parse_html($doc->body);
//            $functional = $this->parse_html($doc->functional);
//            $rights = $this->parse_html($doc->rights);
//            $responsibility = $this->parse_html($doc->responsibility);
//            $condition = $this->parse_html($doc->condition);

            // barcode gen
            $code = "{$u->id} ".translit($u->name);
//            $code = $u->id;
            $url = "http://localhost:7070/?code=$code";
            $filename = BASE_DIR. "/tmp/barcode{$u->id}.png";
            $fp = fopen ($filename, 'w+');//This is the file where we save the    information
            $ch = curl_init(str_replace(" ","%20",$url));//Here is the file we are downloading, replace spaces with %20
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch); // get curl response
            curl_close($ch);
            fclose($fp);

            $data = array();

            $data['template_name'] = 'instruction_document.odt';
            $data['filename'] = $u->id;
            $data['username'] = $u->name;
//            $data['title'] = $doc->title;
            $data['general'] = $general;
//            $data['functional'] = $functional;
//            $data['rights'] = $rights;
//            $data['responsibility'] = $responsibility;
//            $data['condition'] = $condition;
//            $data['img'] = BASE_DIR.'/python/gates/Documents/barcode.png';
            $data['img'] = $filename;

            $m = new \Message($data);
            $m->gate = 'Documents/Documents';
            $r = $m->send();

            $r = mt_rand(0, 100000);

            println("<h3>".$u->email." -&raquo; <a href=/tmp/{$u->id}.odt?{$r}>ODT</a> <a href=/tmp/{$u->id}.pdf?{$r}>PDF</a></h3>");
        }

    }

    private function parse_html($html_string) {

        require_once BASE_DIR.'/lib/simple_html_dom/simple_html_dom.php';

        //создаём новый объект
        $html = new simple_html_dom();

        //загружаем в него данные
        $html = str_get_html($html_string);

        if ( !$html ) return '';
        if ( $html->innertext == '' ) return '';

        //находим все ссылки на странице и...
        $ul_list = $html->find('ul', 0);

        $result = $this->get_li($ul_list, array());

        //освобождаем ресурсы
        $html->clear();
        unset($html);

        return $result;
    }

    private function get_li($ul, $result) {

        foreach ($ul->children as $li) {

            if ( $li->tag != 'li' ) continue;

            foreach ($li->children as $li_child) {

                if ( $li_child->tag == 'ul' ) {

                    array_push($result, array('ul' => $this->get_li($li_child, array())));

                } else if ( $li_child->getAttribute('already_parsed') != 1 )  {

                    array_push($result, array('li' => $li_child->plaintext));
                    $li_child->setAttribute('already_parsed', 1);
                }
            }
        }

        return $result;
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

    private function link()
    {
        return array(
            ['link' => 'Документ №34545', 'href' => '#'],
            ['link' => 'Документ №34545', 'href' => '#'],
            ['link' => 'Документ №34545', 'href' => '#'],
            ['link' => 'Документ №34545', 'href' => '#']

        );
    }

    private function getFloatButton($param)
    {
        if($param == 'add')return ['title' => '', 'link' => '', 'role' => 'add','data-openwindow'=>'tosatelement', 'data-call'=>'selectchange'];
    }

    private function modal()
    {
        return array(
            [
                'data-link1' => '/config/process/icanstartprocessdc.json',
                'data-link2' => '/config/process/icanstartprocessdct.json',
                'urndoc'=>'urn-doc-32132132',
                'doctitle'=>'TD_REQ008 Заявка на поставку материалов'
            ]
        );
    }






    function nameofemployee(){

    }


    function capafix(){

    }










}

?>
