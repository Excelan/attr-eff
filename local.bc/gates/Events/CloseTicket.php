<?php
namespace Events;

class CloseTicket extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}


		if(!$data['ticketurn']) return ['status' => 404];


		$m = new \Message();
		$m->action = 'update';
		$m->urn = (string)$data['ticketurn'];
		$m->isvalid = false;
		$m->allowknowcuurentstage = false;
		$m->allowopen = false;
		$m->allowsave = false;
		$m->allowcomplete = false;
		$m->deliver();

		return ['status' => 501];
	}

}

?>