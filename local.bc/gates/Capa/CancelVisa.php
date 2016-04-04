<?php
namespace Capa;

class CancelVisa extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		if(strlen(trim($data['text'])) == 0){
			return ['status' => 404];
		}



		//запись комантария отмены
		$m = new \Message();
		$m->action = 'create';
		$m->urn = "urn:Communication:Comment:Level2withEditingSuggestion";
		$m->document = $data['urn'];
		$m->appliedstatus = 'new';
		$m->cancel = 1;
		$m->content = $data['text'];
		$m->autor = $data['actorEmployee'];
		$m->deliver();





		//получаем тикет
		$m = new \Message();
		$m->action = 'load';
		$m->urn = $data['ticketurn'];
		$ticket = $m->deliver();

		//Закрываем тикет
		$m = new \Message();
		$m->action = 'update';
		$m->urn = $data['ticketurn'];
		$m->isvalid = false;
		$m->deliver();






		//возврат на предыдущий этап

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:ManagedProcess:Execution:Record';
		$m->id = (string)$ticket->ManagedProcessExecutionRecord->id;
		$mpe = $m->deliver();

		$metadata = $mpe->metadata;

		// update metadata
		$metadata->decision = 'cancel';

		\Log::debug($metadata, 'decision');

		$m = new \Message();
		$m->action = 'update';
		$m->urn = $mpe->urn;
		$m->metadata = json_encode($metadata);
		$m->deliver();


		// Call Java cancel stage
		$url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . (string)$ticket->ManagedProcessExecutionRecord->id;
		\Log::debug($url, 'decision');
		try {
			$r = httpRequest($url, null, [], 'GET', 5);
			\Log::debug($r, 'decision');
		}
		catch (\Exception $e)
		{
			\Log::debug($e->getMessage(), 'decision');
		}



		return ['status' => 501];
	}

}

?>
