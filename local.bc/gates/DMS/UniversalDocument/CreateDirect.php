<?php
namespace DMS\UniversalDocument;

class CreateDirect extends \Gate
{

    public function gate()
    {
        if ($this->data instanceof \Message) {
            $this->message = $data = json_decode(json_encode($this->data->toArray()));
        } else {
            $this->message = $data = json_decode(json_encode($this->data));
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->indomain = explode(':', $this->message->subjectProto)[0];
        $m->ofclass = explode(':', $this->message->subjectProto)[1];
        $m->oftype = explode(':', $this->message->subjectProto)[2];
        $definitionProto = $m->deliver();
        \Log::debug((string)$m, 'unidoc');
        \Log::debug((string)$definitionProto, 'unidoc');

        $prototypeTitle = $definitionProto->title;

        $shadowTitle = $prototypeTitle;

        $code = $definitionProto->oftype . '-' . mt_rand(100000, 999999);

        $mm = new \Message();
        $mm->action = 'create';
        $mm->urn = 'urn:DMS:Document:Universal';

        $mm->direct = true;

        $mm->DefinitionPrototypeDocument = $definitionProto->urn;

        $mm->code = $code;
        $mm->title = $shadowTitle;

        $mm->document = null;
        $mm->indexabletext = '';
        $mm->initiator = $user;

        $mm->process = null;

        \Log::debug((string)$mm, 'unidoc');

        $cou = $mm->deliver();

        return ['status' => 200, 'urn' => (string) $cou->urn, 'code' => (string) $cou->code];
    }
}
