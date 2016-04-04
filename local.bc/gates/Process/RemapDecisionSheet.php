<?php
namespace Process;

/**
 Было на создании черновика, но это давало тикеты на процесс Detective, а не Vising (было решено переносом в Vising DecisionIn, но так нельзя было добавлять новых византов в list)
 * На вхо
 */

class RemapDecisionSheet extends \Gate
{

    public function gate()
    {
        $status = 0;

        \Log::info('===========================================', 'remap');

        //\Log::info($this->data, 'remap');

        if ($this->data instanceof \Message) {
            $d = json_decode(json_encode($this->data->toArray()));
        } // вызов из теста
        else {
            $d = json_decode(json_encode($this->data));
        } // вызов извне

        \Log::debug($d, 'remap');
        //\Log::info($data['subjectURN'], 'remap');
        //\Log::info((string)$d->subjectURN, 'remap');
        //\Log::error((string)$d->rand, 'remap');

        if (!$d->mpeId) {
            throw new \Exception("No mpeId");
        }
        if (!$d->subjectURN) {
            throw new \Exception("No subjectURN");
        }

        $mpeURN = "urn:ManagedProcess:Execution:Record:".$d->mpeId;

        /*
        $m = new \Message();
        $m->action = 'create';
        $m->urn = 'urn:DMS:DecisionSheet:Signed';
        $m->document = (string) $d->subjectURN;
        $sheet = $m->deliver();
        \Log::info($sheet, 'remap');
        */

        $subjectURN = new \URN($d->subjectURN);
        $subjectProto = $subjectURN->getPrototype();
        \Log::debug((string)$subjectProto, 'remap');

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->indomain = $subjectProto->getInDomain();
        $m->ofclass = $subjectProto->getOfClass();
        $m->oftype = $subjectProto->getOfType();
        $definitionProto = $m->deliver();

        // есть само определение прототипа документа
        if (count($definitionProto)) {
            \Log::info("APPROVER? ".(string)$definitionProto->approver->urn, 'remap');
            \Log::info("VISANTS? ".(string)$definitionProto->visants_ManagementPostIndividual, 'remap');

            // есть византы в списке в прототипе документа
            if (count($definitionProto->visants_ManagementPostIndividual)) {
                $m = new \Message();
                $m->action = 'create';
                $m->urn = 'urn:DMS:DecisionSheet:Signed';
                $m->document = (string)$subjectURN;
                $sheet = $m->deliver();
                \Log::debug("+ SHEET ".(string)$sheet, 'remap');

                foreach ($definitionProto->visants_ManagementPostIndividual as $visantId) {
                    //if (!$visantId) continue; // !
                    \Log::debug("EACH VISANT ADD TO SHEET #".$visantId, 'remap');
                    $visantURN = new \URN("urn:Management:Post:Individual:{$visantId}");

                    $m = new \Message();
                    $m->action = 'update';
                    $m->urn = $sheet->urn;
                    $m->document = (string) $d->subjectURN;
                    $m->needsignfrom = ['append' => (string)$visantURN];
                    $m->deliver();
                    //
                    $m = new \Message();
                    $m->action = 'create';
                    $m->urn = 'urn:Feed:MPETicket:InboxItem';
                    $m->ManagementPostIndividual = (string) $visantURN;
                    $m->ManagedProcessExecutionRecord = (string) $mpeURN;
                    $m->activateat = date("Y-m-d H:i:s", time());
                    $m->allowknowcuurentstage = true;
                    $m->allowopen = true;
                    \Log::debug("? ".(string)$m, 'remap');
                    $ticket = $m->deliver();
                    \Log::debug("++ TICKET ".(string)$ticket, 'remap');
                }

                \Log::info("needsignfrom: ". json_encode($sheet->urn->resolve()->current()->needsignfrom), 'remap');
                $status = 200;
            } else {
                \Log::info("Blank visants in {$subjectProto}", 'remap');
                $status = 400;
            }
        } else {
            \Log::error("No {$m->urn} with visants/approver for subject proto {$subjectProto}", 'remap');
            throw new \Exception("No {$m->urn} with visants/approver for subject proto {$subjectProto}");
            $status = 500;
        }

        return ['status' => $status];
    }
}
