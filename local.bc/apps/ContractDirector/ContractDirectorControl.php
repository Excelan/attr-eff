<?php

class ContractDirectorControl extends AjaxApplication implements ApplicationFreeAccess //, ApplicationUserOptional
{
    function CreateTicketsForConsidering()
    {

        if (!$this->message) Log::debug(json_encode($_POST), 'director');

        $r = json_decode($this->message->json, true);

        $MPE_ID = (int)$r['mpeId'];
        //$MPE_ID = $this->message->mpeId;

        Log::debug((string)$MPE_ID, 'rznasa');

        if (!$MPE_ID) throw new Exception('No mpe id');

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        Log::debug('$mpe = '.(string)$mpe->urn, 'rznasa');

        $subjectURN = new URN((string)$mpe->subject);
        $subject = $subjectURN->resolve()->current();


        //загрузка Сотрудники компании для уведомления
        $listURN = (string)$subject->urn.':notifyusercompany';

        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN($listURN);
        $listMembers = $m->deliver();

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Individual';
        $m->in = $listMembers;
        $resList = $m->deliver();//список

        foreach($resList as $item){

            Log::debug('urn = '.(string)$item->urn, 'rznasa');

            $m = new Message();
            $m->action = 'create';
            $m->urn = 'urn:Feed:MPETicket:InboxItem';
            $m->ManagementPostIndividual = (string)$item->urn;
            $m->ManagedProcessExecutionRecord = (string)$mpe->urn;
            $m->activateat = date("Y-m-d H:i:s", time());
            $m->allowknowcuurentstage = true;
            $m->allowopen = true;
            $m->allowcomment = true;
            $m->allowreadcomments = true;
            $m->allowseejournal = true;
            $m->deliver();
        }






        //
        $d = new Message();
        $d->ok = 'ticketscreated';

        return $d;
    }
}

?>