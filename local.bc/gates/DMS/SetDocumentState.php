<?php
namespace DMS;

class SetDocumentState extends \Gate
{

	function gate()
	{
		if ($this->data instanceof \Message)
			$this->message = $data = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$this->message = $data = json_decode(json_encode($this->data)); // вызов извне

		try {
			\Log::info($this->message, 'setstate');

			$mm = new \Message();
			$mm->action = 'update';
			$mm->urn = $this->message->urn;
			$mm->state = $this->message->state;
			$mm->deliver();
			\Log::info((string)$mm, 'setstate');
			$status = 200;
		}
		catch (Exception $e)
		{
			\Log::error($e->getMessage(), 'setstate');
			$status = 500;
		}

		$data = $this->data;
		return ['status' => $status];
	}

}

?>
