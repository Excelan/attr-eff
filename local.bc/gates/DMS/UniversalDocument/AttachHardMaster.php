<?php
namespace DMS\UniversalDocument;

class AttachHardMaster extends \Gate
{

    public function gate()
    {
        if ($this->data instanceof \Message) {
            $this->message = $data = json_decode(json_encode($this->data->toArray()));
        } // вызов из теста
        else {
            $this->message = $data = json_decode(json_encode($this->data));
        } // вызов извне

        $m = new \Message();
        $m->urn = $this->message->UniversalDocumentURN;
        $m->action = 'update';
        $m->hardmaster = ['append' => (string) $this->message->hardmasterURN];
        \Log::info($m, 'hardcopy');
        $r = $m->deliver();
        \Log::info($r, 'hardcopy');

        return ['status' => 200];
    }
}
