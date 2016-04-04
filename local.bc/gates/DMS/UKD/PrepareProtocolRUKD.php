<?php
namespace DMS\UKD;

class PrepareProtocolRUKD extends \Gate
{

    public function gate()
    {
        if ($this->data instanceof \Message) {
            $this->message = $data = json_decode(json_encode($this->data->toArray()));
        } else {
            $this->message = $data = json_decode(json_encode($this->data));
        }

        \Log::info($this->message, 'ukd');

        $rukdURN = new \URN($this->message->rukdurn);

        $sopURN = new \URN($this->message->sopurn);
        $sop = $sopURN->resolve();

        $asrURN = new \URN($this->message->asrurn);
        $asr = $asrURN->resolve();

        // Goldcut feature request: transfer lists, filter, sort etc. PL/SQL
        // $asr->successpassed >> $rukd->plannedreceivers

        foreach ($asr->successpassed as $successpassedPost) {
            $transfered++;
            \Log::info($successpassedPost, 'rukd');
            $m = new \Message();
            //$m->urn = $rukdURN;
            //$m->action = 'update';
            //$m->plannedreceivers = ['append' => (string) $successpassedPost];
            $m->action = 'add';
            $m->urn = $successpassedPost;
            $m->to = $rukdURN.':plannedreceivers';
            \Log::info($m, 'rukd');
            $r = $m->deliver();
            //\Log::info($r, 'rukd');
        }

        $rukd = $rukdURN->resolve();
        $copyids = [];
        foreach ($rukd->plannedreceivers as $plannedreceiver) {
            $plannedreceiverURN = $plannedreceiver->urn;
            $m = new \Message();
            $m->urn = 'urn:DMS:Copy:Controled';
            $m->action = 'create';
            $m->isvalid = true;
            $m->wfstate = 'generated';
            $m->pdflink = null;
            $m->holder = $plannedreceiverURN;
            $m->master = null;
            $m->DocumentRegulationsSOP = $sop->urn;
            $m->issueDocumentProtocolRUKD = $rukd->urn;
            \Log::info($m, 'rukd');
            $copy = $m->deliver();
            \Log::info((string)$copy, 'rukd');
            \Log::debug($copy->urn->uuid, 'rukd');
            array_push($copyids, $copy->urn->uuid);

            $m = new \Message();
            $m->urn = 'urn:Directory:UKDState:IssueRecord';
            $m->action = 'create';
            $m->DocumentProtocolRUKD = $rukdURN;
            $m->holder = $plannedreceiverURN;
            $m->DMSCopyControled = $copy->urn;
            $m->issued = 'no';
            $m->withdrawal = 'na';
            \Log::info((string)$m, 'rukd');
            $m->deliver();
        }



        return ['status' => 200, 'transfered'=>$transfered, 'copyids' => join(',', $copyids)];
    }
}
