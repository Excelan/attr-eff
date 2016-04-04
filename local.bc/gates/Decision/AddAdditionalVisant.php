<?php
namespace Decision;

class AddAdditionalVisant extends \Gate
{

	function gate()
	{
		if ($this->data instanceof \Message)
			$this->message = $data = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$this->message = $data = json_decode(json_encode($this->data)); // вызов извне

		$m = new \Message();
		$m->urn = $this->message->subjectURN;
		$m->action = 'update';
		$m->additionalvisants = ['append' => (string)$this->message->postURN];
		$m->deliver();

		return ['status' => 200];
	}

}

?>
