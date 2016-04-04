<?php
namespace Selector\process;

class SubjectProto extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Definition:Prototype:System';
		$m->isprocess = false;
		$m->order = 'indomain asc, ofclass asc, oftype asc';
		$data = $m->deliver();

		$arr = array();
		foreach($data as $d){
			array_push($arr, ['value' => (string) $d->urn, 'title' => $d->title]);
		}

		return [
				'options' => $arr
		];
	}

}

?>
