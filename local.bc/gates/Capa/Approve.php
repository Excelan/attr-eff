<?php
namespace Capa;

class Approve extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		//если количество выбраных решений не равно количеству мероприятий по капе - возвращаем 404
		$c1 = count($data['selected_variants']);

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Document:Correction:Capa';
		$m->DocumentCapaDeviation = $data['urn'];
		$corrections = $m->deliver();

		$c2 = count($corrections);

		if($c1 != $c2){
			\Log::info('количество выбраных решений не равно количеству мероприятий по капе','capa');
			return ['status' => '404'];
		}


		\Log::debug('--------------URN решений которым проставляем статус утвержден -----------', 'capa');
		foreach ($data['selected_variants'] as $v) {


			$m = new \Message();
			$m->action = 'update';
			$m->urn = $v['correctionUrn'];
			$m->selectedsolution = $v['solutionUrn'];
			$m->deliver();

			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'urn:Document:Solution:Correction';
			$m->DocumentCorrectionCapa = $v['correctionUrn'];
			$solutions = $m->deliver();

			//удаляем все существующие, после чего добавляем пришедшие с js
			foreach($solutions as $solution){
				$m = new \Message();
				$m->action = 'update';
				$m->urn = $solution->urn;
				$m->approveded = 0;
				$m->deliver();
			}



			\Log::debug($v['solutionUrn'], 'capa');

			$m = new \Message();
			$m->action = 'update';
			$m->urn = $v['solutionUrn'];
			$m->approveded = 1;
			$m->deliver();

		}
		\Log::debug('-------------- проставлено -----------', 'capa');


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



		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:ManagedProcess:Execution:Record';
		$m->id = $data['mpeid'];
		$mpe = $m->deliver();


		// Call Java complete stage
		$url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . (string)$ticket->ManagedProcessExecutionRecord->id;
		\Log::debug($url, 'decision');
		try {
			$r = httpRequest($url, null, [], 'GET', 5);
			\Log::debug($r, 'decision');
		} catch (\Exception $e) {
			\Log::debug($e->getMessage(), 'decision');
		}
/*
		//розсылка тикета на выполнение
		foreach($corrections as $correction) {
			$m = new \Message();
			$m->action = 'create';
			$m->urn = 'urn:Feed:MPETicket:InboxItem';
			$m->ManagementPostIndividual = (string)$correction->selectedsolution->executor->urn;
			\Log::debug((string)$correction->selectedsolution->executor->urn, 'rznasa');
			$m->ManagedProcessExecutionRecord = (string)$mpe->urn;
			$m->activateat = date("Y-m-d H:i:s", time());
			$m->allowknowcuurentstage = true;
			$m->allowopen = true;
			$m->allowcomment = true;
			$m->allowreadcomments = true;
			$m->allowseejournal = true;
			$ticket = $m->deliver();

		}
*/

		return ['status' => 501];
	}

}

?>