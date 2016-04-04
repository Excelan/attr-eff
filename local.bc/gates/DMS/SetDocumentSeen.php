<?php
namespace DMS;

class SetDocumentSeen extends \Gate
{

    public function gate()
    {
        if ($this->data instanceof \Message) {
            $this->message = $data = json_decode(json_encode($this->data->toArray()));
        } // вызов из теста
        else {
            $this->message = $data = json_decode(json_encode($this->data));
        } // вызов извне

        try {
            \Log::info($this->message, 'setstate');


            $unidocURN = new \URN($this->message->unidoc);
            $unidoc = $unidocURN->resolve();

            //$seenNeededDS = $unidoc->DMSAcquaintanceDocument;
                        $m = new \Message();
            $m->action = 'load';
            $m->urn = 'urn:DMS:Acquaintance:Document';
            $m->DMSDocumentUniversal = $unidoc->urn;
            $m->ManagementPostIndividual = $this->managementrole->urn;
            $seenNeededDS = $m->deliver();

            if (count($seenNeededDS) == 1) {
                $mm = new \Message();
                $mm->action = 'update';
                $mm->urn = $seenNeededDS->urn;
                $mm->done = true;
                $mm->deliver();
                \Log::info((string)$mm, 'setstate');
                $status = 200;
            }
        } catch (Exception $e) {
            \Log::error($e->getMessage(), 'setstate');
            $status = 500;
        }

        $data = $this->data;
        return ['status' => $status];
    }
}
