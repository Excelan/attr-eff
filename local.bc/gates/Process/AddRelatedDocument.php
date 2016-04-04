<?php
namespace Process;

class AddRelatedDocument extends \Gate
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
		$m->related = ['append' => (string)$this->message->documentURN];
		$m->deliver();

		return ['status' => 200];
	}

}

?>
