<?php
namespace Process;

class CreateTicketFor extends \Gate
{

	function gate()
	{
		if ($this->data instanceof \Message)
			$d = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$d = json_decode(json_encode($this->data)); // вызов извне

		$mpeURN = new \URN("urn:ManagedProcess:Execution:Record:".$d->mpeId);

		$m = new \Message();
		$m->action = 'create';
		$m->urn = 'urn:Feed:MPETicket:InboxItem';
		$m->ManagementPostIndividual = (string) $d->postURN;
		$m->ManagedProcessExecutionRecord = (string) $mpeURN;
		$m->activateat = date("Y-m-d H:i:s", time());
		$m->allowknowcuurentstage = true;
		$m->allowopen = true;
		$m->allowcomment = true;
		$m->allowreadcomments = true;
		$m->allowseejournal = true;
		$ticket = $m->deliver();
		\Log::debug("? ".(string)$m, 'remap');
		\Log::debug("++ TICKET ".(string)$ticket, 'remap');

		return ['status' => 200];
	}

}

?>
