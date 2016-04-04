<?php
namespace Events;

class SetCapaDay extends \Gate
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

		$m->eventplace = $data['place'];
		//$m->eventtime = strtotime("{$data['time']}".':00 '."{$data['eventDate']}");
		$m->eventtime = strtotime($data['eventDate'].' '.$data['time']);
		\Log::info($m,'rznasa');
		$m->deliver();

		return ['status' => 501];
	}

}

?>