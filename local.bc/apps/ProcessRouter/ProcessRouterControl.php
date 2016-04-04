<?php

class ProcessRouterControl extends AjaxApplication implements ApplicationFreeAccess //, ApplicationUserOptional
{

    // в конце создания SOP. Опционально начать процесс создания программы обучения
    function DMSRegulationSOP()
    {
      // решение делать ли TA Делается в Java
      $state = 'skip';

      // текущий процесс - SOP
      $r = json_decode($this->message->json, true);
      $mpe_and_subject = getMPEAndSubjectByMpeID((int)$r['mpeId']);
      $mpe = $mpe_and_subject[0];
      $subject = $mpe_and_subject[1];

      // if (opt) start process
      Log::debug($m, 'processrouter');

      /*
      // начать новый процесс Аттестации
      $processProto = 'DMS:Regulation:Attestation';
      $subjectProto = 'Document:Regulations:TA'; // Программа обучения
      $initiator = (string) 'urn:Actor:User:System:2'; // TODO
      $url = 'http://localhost:8020/startprocess/?prototype='.$processProto.'&initiator='.$initiator;
      if ($subjectProto) $url .= '&subjectPrototype='.$subjectProto;
      $r = httpRequest($url, null, [], 'GET', 5);
      if ($r['httpcode'] != 200) throw new Exception("Process start remote error");
      $newMPE_ID = explode(':',$r['json']['upn'])[4];
      $newMPE_URN = new URN('urn:ManagedProcess:Execution:Record:'.$newMPE_ID);
      $newMPE = $newMPE_URN->resolve(); // Attestation
      $newSubjectURN = new URN((string)$newMPE->subject);
      $newSubject = $newSubjectURN->resolve()->current(); // TA

      // в метаданные текущего процесса SOP внести ссылку на порожденный процесс в виде routedto
      $metadata = $mpe->metadata;
      $metadata->routedto = (string) $newMPE_URN; // Attestation
      $m = new \Message();
      $m->action = 'update';
      $m->urn = $mpe->urn;
      $m->metadata = $metadata;
      $m->deliver();
      */

      // перенос данных из SOP в TA
      $m = new \Message();
      $m->action = 'update';
      $m->urn = $newSubject->urn; // TA
      $m->DocumentRegulationsSOP = $subject->urn; // привязали SOP к TA программе обучения
      $m->trainer = $subject->ManagementPostIndividual; // перекинули Тренера с (Ответственный за создание Программы обучения)
      $m->deliver();

      // перенести из SOP в TA список на обучение из userprocedure в новый процесс можно будет только после этапа create draft, а он уже прошел сразу после старта процесса
      foreach ($subject->userprocedure as $student)
      {
          // кто должен сдать
          $m = new Message();
          $m->action = 'add';
          $m->urn = $student;
          $m->to = $newSubject->urn . ':' . 'requiredforstudents';
          Log::debug($m, 'processrouter');
          $m->deliver();
          // кто будет сдавать на первой итерации
          $m = new Message();
          $m->action = 'add';
          $m->urn = $student;
          $m->to = $newSubject->urn . ':' . 'iterationstudents';
          Log::debug($m, 'processrouter');
          $m->deliver();
      }
      // то же, но из типов должностей развернуть должности
      foreach ($subject->userproceduregroup as $groupstudent)
      {
          foreach ($groupstudent->ManagementPostIndividual as $student)
          {
          // кто должен сдать
          $m = new Message();
          $m->action = 'add';
          $m->urn = $student;
          $m->to = $newSubject->urn . ':' . 'requiredforstudents';
          Log::debug($m, 'processrouter');
          $m->deliver();
          /*
          // кто будет сдавать на первой итерации
          $m = new Message();
          $m->action = 'add';
          $m->urn = $student;
          $m->to = $newSubject->urn . ':' . 'iterationstudents';
          Log::debug($m, 'processrouter');
          $m->deliver();
          }
          */
      }

      $state = 'processstarted';


      $d = new Message();
      $d->ok = $state;

      return $d;
    }

    // в конце создания программы обучения. стартовать аттестацию
    function DMSRegulationStudy()
    {

    }

    function DMSRegulationAttestation()
    {
        /**
        <stage name="Route">
          <target>По результатам Аттестации возможно планирование 2х ивентов - 1-выдача УКД, для тех кто успешно прошел и 2-повторная аттестация, для тех кто не прошел</target>
          <delegate optional="yes" process="DMS:Regulation:UKD"/>
          <delegate optional="yes" process="DMS:Regulation:Attestation"/>
        </stage>
        */
        $r = json_decode($this->message->json, true);
        $mpe_and_subject = getMPEAndSubjectByMpeID((int)$r['mpeId']);
        $mpe = $mpe_and_subject[0];
        $subject = $mpe_and_subject[1];

        // TODO opt start DMS:Regulation:UKD

        // TODO opt start DMS:Regulation:Attestation

    }
}

?>
