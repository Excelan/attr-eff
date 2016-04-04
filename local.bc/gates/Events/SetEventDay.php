<?php
namespace Events;

class SetEventDay extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}


		$m= new \Message();
		$m->action = 'update';
		$m->urn = $data['subjecturn'];
		$m->set($data['datefield'], $data['eventDate']);
		$m->deliver();

		return ['status' => 501];
	}

}

?>
