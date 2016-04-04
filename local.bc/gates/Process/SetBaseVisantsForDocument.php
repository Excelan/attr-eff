<?php
namespace Process;

class SetBaseVisantsForDocument extends \Gate
{

    public function gate()
    {
        if ($this->data instanceof \Message) {
            $d = json_decode(json_encode($this->data->toArray()));
        } // вызов из теста
        else {
            $d = json_decode(json_encode($this->data));
        } // вызов извне

        $subjectURN = new \URN($d->subjectURN);
        $subjectProto = $subjectURN->getPrototype();
        \Log::debug((string)$subjectProto, 'remap');

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->indomain = $subjectProto->getInDomain();
        $m->ofclass = $subjectProto->getOfClass();
        $m->oftype = $subjectProto->getOfType();
        $m->unmanaged = 0; // ТОЛЬКО ДЛЯ ДОКУМЕНТОВ С ВИЗИРОВАНИЕМ
        $definitionProto = $m->deliver();

        // есть само определение прототипа документа
        if (count($definitionProto)) {
            \Log::info("APPROVER? ".(string)$definitionProto->approver->urn, 'remap');
            \Log::info("VISANTS? ".(string)$definitionProto->visants_ManagementPostIndividual, 'remap');

            // есть византы в списке в прототипе документа
            if (count($definitionProto->visants_ManagementPostIndividual)) {
                foreach ($definitionProto->visants_ManagementPostIndividual as $visantId) {
                    //if (!$visantId) continue; // !
                    \Log::debug("EACH VISANT ADD TO SHEET #".$visantId, 'remap');
                    $visantURN = new \URN("urn:Management:Post:Individual:{$visantId}");

                    $m = new \Message();
                    $m->action = 'update';
                    $m->urn = $subjectURN;
                    $m->basevisants = ['append' => (string)$visantURN];
                    $m->deliver();
                    //
                }
                //\Log::info("needsignfrom: ". json_encode($sheet->urn->resolve()->current()->needsignfrom), 'remap');
                $status = 200;
            } else {
                \Log::error("!!! Blank visants in {$subjectProto}", 'remap');
                $status = 400;
            }
        } else {
            \Log::error("No {$m->urn} with visants/approver for subject proto {$subjectProto}", 'remap');
            //throw new \Exception("No {$m->urn} with visants/approver for subject proto {$subjectProto}");
            $status = 404;
        }


        return ['status' => 200];
    }
}
