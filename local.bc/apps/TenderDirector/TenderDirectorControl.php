<?php

class TenderDirectorControl extends AjaxApplication implements ApplicationFreeAccess //, ApplicationUserOptional
{
    public function BindingPositionToParticipants($path)
    {
        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];
        //$MPE_ID = $this->message->mpeId;

        Log::debug((string)$MPE_ID, 'rznasa');

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();




        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();

        Log::debug((string)$subject->urn, 'rznasa');

        if($mpe->metadata->decision != 'forward') {

            foreach ($subject->DirectoryTenderPositionSimple as $position) {
                Log::debug((string)$position->urn, 'rznasa');
                foreach ($subject->DirectoryTenderBidderSimple as $bidder) {
                    Log::debug((string)$bidder->urn, 'rznasa');

                    $m = new Message();
                    $m->action = 'create';
                    $m->urn = 'urn:Document:Tender:TableAdditional';
                    $m->titleposition = (string)$position->titleposition;
                    $m->DirectoryTenderBidderSimple = (string)$bidder->urn;
                    $tenderBidder = $m->deliver();
                }
            }

        }


        //перенос с таска в таблицу работ или материалов(зависит от таска)
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = explode(':', $mpe->metadata->parent)[4];
        $mpeTask = $m->deliver();


        if($mpe->metadata->decision != 'forward') {

            if (strpos($mpeTask->subject, 'ForWorks')) {
                $m = new Message();
                $m->action = 'load';
                $m->urn = 'urn:Directory:TechnicalTask:ForWorks';
                $m->DocumentTechnicalTaskForWorks = $mpeTask->subject;
                Log::debug($m, 'rznasa');
                $listWorks = $m->deliver();

                Log::debug(count($listWorks), 'rznasa');

                /**
                //новые не добавляются!!! Если тендер завернули с визироваания или апрувинга
                //для фиксинга можно создать поле сет в $listWorks (перенесен в новую таблицу или нет)
                //потом в цикле написать иф и обновлять єто поле при добавлении или пропускать если уже добалено
                 */

                foreach ($subject->DirectoryTenderBidderSimple as $bdr) {
                    foreach ($listWorks as $one) {
                        Log::debug((string)$one->urn, 'rznasa');
                        Log::debug((string)$one->name, 'rznasa');

                        $m = new Message();
                        $m->action = 'create';
                        $m->urn = 'urn:Document:Tender:Table';
                        $m->titleposition = $one->name;
                        $m->DirectoryTenderBidderSimple = (string)$bdr->urn;
                        $m->deliver();
                    }
                }
            } elseif (strpos($mpeTask->subject, 'ForMaterials')) {
                $listURN = $mpeTask->subject . ':DirectoryTechnicalTaskMaterials';

                $m = new Message();
                $m->action = 'members';
                $m->urn = new URN($listURN);
                $listMembers = $m->deliver();

                $m = new Message();
                $m->action = 'load';
                $m->urn = 'urn:Directory:TechnicalTask:Materials';
                $m->in = $listMembers;
                $resList = $m->deliver();


                foreach ($subject->DirectoryTenderBidderSimple as $bdr) {
                    foreach ($resList as $one) {
                        Log::debug((string)$one->urn, 'rznasa');
                        Log::debug((string)$one->name, 'rznasa');

                        $m = new Message();
                        $m->action = 'create';
                        $m->urn = 'urn:Document:Tender:Table';
                        $m->titleposition = $one->name;
                        $m->DirectoryTenderBidderSimple = (string)$bdr->urn;
                        $m->deliver();
                    }
                }
            }
        }

        //
        $d = new Message();
        $d->ok = 'done';

        return $d;
    }

    public function createRelationsTenderTask()
    {
        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        Log::debug($this->message->mpeId, 'rznasa');

        $MPE_ID = (int)$r['mpeId'];
        //$MPE_ID = $this->message->mpeId;

        Log::debug((string)$MPE_ID, 'rznasa');

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        $subjectURNTender = new URN((string)$mpe->subject);
        $subjectTender = $subjectURNTender->resolve()->current();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = explode(':', $mpe->returntopme)[4];
        $mpeTask = $m->deliver();


        $subjectURNTask = new URN((string)$mpeTask->subject);
        $subjectTask = $subjectURNTask->resolve()->current();

        $m = new Message();
        $m->action = 'update';
        $m->urn = (string)$subjectTender->urn;
        $m->docpermitsneed = (string)$subjectTask->docpermitsneed;
        $updated = $m->deliver();


        // Участники тендера
        $listURN = (string)$subjectTask->urn.':CompanyLegalEntityCounterparty';

        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($listURN);
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Company:LegalEntity:Counterparty';
        $m->in = $listMembers;
        $resList = $m->deliver();

        //переносим участников с таска в тендер
        foreach ($resList as $item) {
            $m = new Message();
            $m->action = 'create';
            $m->urn = 'urn:Directory:TenderBidder:Simple';
            $m->DocumentTenderExtended = (string)$subjectTender->urn;
            $m->CompanyLegalEntityCounterparty = (string)$item->urn;
            Log::debug($m, 'rznasa');
            $m->deliver();
        }



        //
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }


    public function checkTenderToCancel2Tour()
    {
        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];
        //$MPE_ID = $this->message->mpeId;

        Log::debug((string)$MPE_ID, 'rznasa');

        if (!$MPE_ID) {
            throw new Exception('No mpe id');
        }

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        Log::debug('$mpe = '.(string)$mpe->urn, 'rznasa');

        $subjectURNTender = new URN((string)$mpe->subject);
        $subjectTender = $subjectURNTender->resolve()->current();



        // Участники тендера
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Directory:TenderBidder:Simple';
        $m->DocumentTenderExtended = (string)$subjectTender->urn;
        $tenderBidders = $m->deliver();

        //проверяем участников на участие во втором туре
        $count = 0;
        foreach ($tenderBidders as $item) {
            if ($item->biddersolution == 'clarification') {
                $count++;
            }
            // $item->biddersolution == 'no' || 
            Log::debug('biddersolution = '.$item->biddersolution, 'rznasa');
        }

        Log::debug('$count = '.$count, 'rznasa');

        $result = 'FORWARD';


        $metadata = $mpe->metadata;

        Log::debug('metadata', 'rznasa');

        if ($count > 0) {
            // update metadata
          $metadata->decision = 'cancel';
            $result = 'BACK';
          //возврат на предыдущий этап
          Log::debug('$count > 0', 'rznasa');
        } else {
            $metadata->decision = 'forward';
        }
        Log::debug('metadata decision', 'rznasa');


        $m = new \Message();
        $m->action = 'update';
        $m->urn = (string)$mpe->urn;
        $m->metadata = json_encode($metadata);
        Log::debug($m, 'rznasa');

        $m->deliver();



        //
        $d = new Message();
        $d->ok = 'checked';
        $d->result = $result;

        Log::debug((string)$d, 'rznasa');

        return $d;
    }
}
