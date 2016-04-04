<?php
namespace Process;

class CreateTicketForApprover extends \Gate
{

    public function gate()
    {
        \Log::debug('Начало создания тикетов для утверждения', 'director');

        if ($this->data instanceof \Message) {
            $d = json_decode(json_encode($this->data->toArray()));
        } // вызов из теста
        else {
            $d = json_decode(json_encode($this->data));
        } // вызов извне

        $mpeURN = new \URN("urn:ManagedProcess:Execution:Record:".$d->mpeId);

        $mpe = $mpeURN->resolve();

        $subjectURN = new \URN($mpe->subject);
        //$subject = $subjectURN->resolve();
        $subjectProto = $subjectURN->getPrototype();
        \Log::debug((string)$subjectProto, 'remap');

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->indomain = $subjectProto->getInDomain();
        $m->ofclass = $subjectProto->getOfClass();
        $m->oftype = $subjectProto->getOfType();
        $definitionProto = $m->deliver();

        if (count($definitionProto)) {
            $approverURN = $definitionProto->approver->urn;
            \Log::info("APPROVER? ".(string) $approverURN, 'remap');
            \Log::info("APPROVER? ".(string) $approverURN, 'director');
            //
            $m = new \Message();
            $m->action = 'create';
            $m->urn = 'urn:Feed:MPETicket:InboxItem';
            $m->ManagementPostIndividual = (string) $approverURN;
            $m->ManagedProcessExecutionRecord = (string) $mpeURN;
            $m->activateat = date("Y-m-d H:i:s", time());
            $m->allowknowcuurentstage = true;
            $m->allowopen = true;
            $m->allowcomment = true;
            $m->allowreadcomments = true;
            $m->allowseejournal = true;
            $ticket = $m->deliver();
            \Log::debug("? ".(string)$m, 'remap');
            \Log::debug("++ TICKET ".(string)$ticket, 'remap');
        }

        \Log::debug('Конец создания тикетов для утверждения', 'director');

        return ['status' => 200];
    }
}
