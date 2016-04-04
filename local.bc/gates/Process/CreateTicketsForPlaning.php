<?php
namespace Process;

class CreateTicketsForPlaning extends \Gate
{

	function gate()
	{

		return ['status' => 200];








		\Log::debug('Начало создания тикетов для PLANING', 'rznasa');

		if ($this->data instanceof \Message)
			$d = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$d = json_decode(json_encode($this->data)); // вызов извне

		$mpeURN = new \URN("urn:ManagedProcess:Execution:Record:".$d->mpeId);


		\Log::debug('$d->mpeId = '.$d->mpeId, 'rznasa');

		\Log::debug('$mpeURN = '.(string)$mpeURN, 'rznasa');

		$mpe = $mpeURN->resolve();

		$subjectURN = new \URN($mpe->subject);

		$studyTA = $subjectURN->resolve();

		\Log::debug('$studyTA = '.(string)$studyTA->urn, 'rznasa'); // TODO ASR found!


		\Log::debug('DocumentRegulationsSOP = '.(string)$studyTA->DocumentRegulationsSOP->id, 'rznasa');

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Document:Regulations:SOP';
		$m->id = $studyTA->DocumentRegulationsSOP->id;
		\Log::debug('$sop = '.(string)$m, 'rznasa');
		$sop = $m->deliver();

		\Log::debug('$sop = '.(string)$sop->urn, 'rznasa');

		//----------------------------------------------------------------------------------------------------------------
		//получаем  ответственного и раздаем тикет

		$m = new \Message();
		$m->action = 'create';
		$m->urn = 'urn:Feed:MPETicket:InboxItem';
		$m->ManagementPostIndividual = (string)$sop->ManagementPostIndividual->urn;
		$m->ManagedProcessExecutionRecord = (string)$mpeURN;
		$m->activateat = date("Y-m-d H:i:s", time());
		$m->allowknowcuurentstage = true;
		$m->allowopen = true;
		$m->allowcomment = true;
		$m->allowreadcomments = true;
		$m->allowseejournal = true;
		$m->deliver();

		\Log::debug('$sop->ManagementPostIndividual->urn = '.(string)$sop->ManagementPostIndividual->urn, 'rznasa');


		\Log::debug('Конец создания тикетов для PLANING', 'rznasa');

		return ['status' => 200];
	}

}

?>
