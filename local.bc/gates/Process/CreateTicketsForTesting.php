<?php
namespace Process;

class CreateTicketsForTesting extends \Gate
{

	function gate()
	{


		\Log::debug('Начало создания тикетов для тестирования', 'rznasa');

		if ($this->data instanceof \Message)
			$d = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$d = json_decode(json_encode($this->data)); // вызов извне

		$mpeURN = new \URN("urn:ManagedProcess:Execution:Record:".$d->mpeId);

		$mpe = $mpeURN->resolve();

		$subjectURN = new \URN($mpe->subject);

		$studyTA = $subjectURN->resolve();


		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Document:Regulations:SOP';
		$m->id = (string)$studyTA->DocumentRegulationsSOP->id;
		$sop = $m->deliver();

		//----------------------------------------------------------------------------------------------------------------
		//получаем Участники процедуры (Должность)
		$m = new \Message();
		$m->action = 'members';
		$m->urn = new \URN((string)$sop->urn.':userprocedure');
		$listMembers = $m->deliver();

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'Management:Post:Individual';
		$m->in = $listMembers;
		$us = $m->deliver();

		//Раздаем тикеты Участники процедуры (Должность)
		foreach($us as $u) {

			$m = new \Message();
			$m->action = 'create';
			$m->urn = 'urn:Feed:MPETicket:InboxItem';
			$m->ManagementPostIndividual = (string)$u->urn;
			$m->ManagedProcessExecutionRecord = (string)$mpeURN;
			$m->activateat = date("Y-m-d H:i:s", time());
			$m->allowknowcuurentstage = true;
			$m->allowopen = true;
			$m->allowcomment = true;
			$m->allowreadcomments = true;
			$m->allowseejournal = true;
			$m->deliver();
		}
		//----------------------------------------------------------------------------------------------------------------

		//получаем Участники процедуры (Тип должности)
		$m = new \Message();
		$m->action = 'members';
		$m->urn = new \URN((string)$sop->urn.':userprocedure');
		$listMembers2 = $m->deliver();

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'Management:Post:Group';
		$m->in = $listMembers2;
		$ugroup = $m->deliver();

		foreach ($ugroup as $item) {

			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'Management:Post:Individual';
			$m->ManagementPostGroup = (string)$item->urn;
			$userLists = $m->deliver();//получаем юзеров должности

			foreach($userLists as $userLists){

				//Раздаем тикеты этим юзерам
				$m = new \Message();
				$m->action = 'create';
				$m->urn = 'urn:Feed:MPETicket:InboxItem';
				$m->ManagementPostIndividual = (string)$userLists->urn;
				$m->ManagedProcessExecutionRecord = (string)$mpeURN;
				$m->activateat = date("Y-m-d H:i:s", time());
				$m->allowknowcuurentstage = true;
				$m->allowopen = true;
				$m->allowcomment = true;
				$m->allowreadcomments = true;
				$m->allowseejournal = true;
				$m->deliver();

			}
		}


		\Log::debug('Конец создания тикетов для тестироваания', 'rznasa');

		return ['status' => 200];
	}

}

?>