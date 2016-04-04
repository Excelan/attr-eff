<?php
namespace Events;

class AutoCreateField extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}


		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:ManagedProcess:Execution:Record:'.$data['mpeId'];
        $mpe = $m->deliver();

        $subjectURN = (string)$mpe->subject;
		$urn = new \URN((string)$subjectURN);

		$prototype = $urn->getPrototype();
		$indomain = $prototype->getInDomain();
		$ofclass = $prototype->getOfClass();
		$oftype = $prototype->getOfType();



        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->indomain = $indomain;
        $m->ofclass = $ofclass;
        $m->oftype = $oftype;
        $prototype = $m->deliver();

        if (count($prototype) > 1) {
            throw new \Exception('More than one prototype');
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Directory:TextFragment:ForPrototypeField';
        $m->DefinitionPrototypeDocument = (string)$prototype->urn;
        $textfragment = $m->deliver();


        foreach($textfragment as $text){

            $m = new \Message();
            $m->action = 'update';
            $m->urn = $subjectURN;
            $m->set($text->fieldname,$text->fieldtext);
            $m->deliver();

        }

		return ['status' => 501];
	}

}

?>