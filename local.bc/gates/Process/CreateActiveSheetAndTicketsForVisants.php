<?php
namespace Process;

class CreateActiveSheetAndTicketsForVisants extends \Gate
{

	function gate()
	{
		if ($this->data instanceof \Message)
			$d = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$d = json_decode(json_encode($this->data)); // вызов извне

		$mpeURN = new \URN("urn:ManagedProcess:Execution:Record:".$d->mpeId);

		$mpe = $mpeURN->resolve();

		$subjectURN = new \URN($mpe->subject);
		$subject = $subjectURN->resolve();

		$m = new \Message();
		$m->action = 'create';
		$m->urn = 'urn:DMS:DecisionSheet:Signed';
		$m->document = (string) $subject->urn;
		$sheet = $m->deliver();
		\Log::debug("+ SHEET ".(string)$sheet, 'remap');

		if (!count($subject->basevisants))
		{
			\Log::error("!!! NO BASEVISANTS IN ".(string) $sheet, 'remap');
		}

		foreach ($subject->basevisants as $visantURN)
		{
			\Log::debug("EACH VISANT ADD TO SHEET #".$visantURN, 'remap');
			//
			$m = new \Message();
			$m->action = 'update';
			$m->urn = $sheet->urn;
			$m->document = (string) $d->subjectURN;
			$m->needsignfrom = ['append' => (string)$visantURN];
			$m->deliver();
			//
			$m = new \Message();
			$m->action = 'create';
			$m->urn = 'urn:Feed:MPETicket:InboxItem';
			$m->ManagementPostIndividual = (string) $visantURN;
			$m->ManagedProcessExecutionRecord = (string) $mpeURN;
			$m->activateat = date("Y-m-d H:i:s", time());
			$m->allowknowcuurentstage = true;
			$m->allowopen = true;
			$ticket = $m->deliver();
			\Log::debug("? ".(string)$m, 'remap');
			\Log::debug("++ TICKET ".(string)$ticket, 'remap');
		}

		foreach ($subject->additionalvisants as $visantURN)
		{
			\Log::debug("EACH additionalvisants VISANT ADD TO SHEET #".$visantURN, 'remap');
			//
			$m = new \Message();
			$m->action = 'update';
			$m->urn = $sheet->urn;
			$m->document = (string) $d->subjectURN;
			$m->needsignfrom = ['append' => (string)$visantURN];
			$m->deliver();
			//
			$m = new \Message();
			$m->action = 'create';
			$m->urn = 'urn:Feed:MPETicket:InboxItem';
			$m->ManagementPostIndividual = (string) $visantURN;
			$m->ManagedProcessExecutionRecord = (string) $mpeURN;
			$m->activateat = date("Y-m-d H:i:s", time());
			$m->allowknowcuurentstage = true;
			$m->allowopen = true;
			$ticket = $m->deliver();
			\Log::debug("? ".(string)$m, 'remap');
			\Log::debug("++ TICKET ".(string)$ticket, 'remap');
		}
		// TODO additional visants

		return ['status' => 200];
	}

}

?>
