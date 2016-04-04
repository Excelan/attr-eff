<?php

class UniversalDocumentApp extends WebApplication implements ApplicationAccessManaged
{
    public function request()
    {
    }

    public function resource($code)
    {
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:DMS:Document:Universal';
        $m->code = $code;
        $unicDoc = $m->deliver();
        $this->context['unidoc'] = $unicDoc->current();

        $this->context['allowsave'] = false;
        $this->context['allowcomplete'] = false;
        $this->context['allowcomment'] = false;
        $this->context['allowreadcomments'] = true;
        $this->context['allowseejournal'] = true;
        $this->context['allowearly'] = false;

        if ($unicDoc->document) {
            $subjectURN = new URN($unicDoc->document);
            $this->context['subjectURN'] = $subjectURN;
            $this->context['subject'] = $subject = $subjectURN->resolve();

            $subjectProto = $subjectURN->getPrototype();
            $subjectClass = $subjectProto->getOfClass();
            $subjectType = $subjectProto->getOfType();

            $pageTitle = $E = Entity::ref((string)$subjectURN->getPrototype())->title['ru'];
        } else {
            $DefinitionPrototypeDocument = $unicDoc->DefinitionPrototypeDocument;
            println($DefinitionPrototypeDocument);
            $subjectProto = $DefinitionPrototypeDocument->indomain;
            $subjectClass = $DefinitionPrototypeDocument->ofclass;
            $subjectType = $DefinitionPrototypeDocument->oftype;
        }

        // FORM PATH
        $stageEffective = 'Decision';

        $formpath = $subjectClass.'/'.$stageEffective.'/'.$subjectClass.'_'.$subjectType;
        $this->context['formpath'] = $formpath;

        //$pageNavTitle = $GLOBALS['MPE'][$mpe->prototype][$mpe->currentstage]; // TODO

        $this->register_widget('title', 'pagetitle', ['title'=>array($unicDoc->code, $pageTitle)]);

      //виджет хедера
      $this->register_widget('header', 'header', array(
          'currentURI'=>$this->context['currentURI'],
          'add'=>$this->getFloatButton('add'),
          'name'=> $pageTitle,
          'title'=> $pageNavTitle . ' ' . $this->context['subject']->code, // TODO Куда код ставить? (Коля)
          'modal'=>$this->modal($subjectURN)
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
