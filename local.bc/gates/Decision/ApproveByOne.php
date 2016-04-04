<?php
namespace Decision;

class ApproveByOne extends \Gate
{
	private $message;

	function gate()
	{
		if ($this->data instanceof \Message)
			$this->message = $data = json_decode(json_encode($this->data->toArray())); // вызов из теста
		else
			$this->message = $data = json_decode(json_encode($this->data)); // вызов извне

		\Log::info($this->message, 'decision');
		\Log::info($this->message, 'remap');

		$visaActorDecision = $this->message->status;

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:ManagedProcess:Execution:Record';
		$m->id = $this->message->mpeid;
		$mpe = $m->deliver();
		if (!count($mpe)) throw new \Exception("No MPE by {$this->message->mpeid}");
		if (count($mpe) > 1) throw new \Exception("No MPE by {$this->message->mpeid}");
		\Log::info((string)$mpe->current(), 'decision');

        // load metadata
        //\Log::info($mpe->metadata, 'decision');
        $metadata = $mpe->metadata;
        // update metadata
        $metadata->decision = $visaActorDecision;
        \Log::debug($metadata, 'decision');
        //
        $m = new \Message();
        $m->action = 'update';
        $m->urn = $mpe->urn;
        $m->metadata = json_encode($metadata);
        $m->deliver();

		$visedvariants = json_decode($this->message->visedvariants, true);
		if ($visaActorDecision != 'cancel' && count($visedvariants) == 1)
		{
			$m = new \Message();
			$m->action = 'update';
			$m->urn = $mpe->subject;
			$m->DocumentSolutionUniversal = $visedvariants[0];
			\Log::debug((string)$m, 'decision');
			$m->deliver();
		}

		// СОХРАНИТЬ РЕШЕНИЕ В ОДИН ИЗ СПИСКОВ
		if ($visaActorDecision == 'cancel') // visa canceled
		{
			// комментарий причины отказа
				$m = new \Message();
				$m->action = 'create';
				$m->urn = "urn:Communication:Comment:Level2withEditingSuggestion";
				$m->document = (string)$this->message->urn;
				$m->appliedstatus = 'new';
				$m->content = $this->message->text;
				$m->cancel = 1;
				$m->autor = $this->message->actorEmployee; // from App (employee)
				$m->deliver();
		}

        // доступ с двух транзакций! (java/php)
        /*
        if ($globalDecision != 'cancel')
        {
            // Call Java complete stage
            $url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . $this->message->mpeid;
            //println($url, 1, TERM_GREEN);
            \Log::debug($url, 'decision');
            try {
                $r = httpRequest($url, null, [], 'GET', 5);
                //println($r);
                \Log::debug($r, 'decision');
            }
            catch (\Exception $e)
            {
                \Log::debug($e->getMessage(), 'decision');
            }
        }
        */

		// return
		return ['mpeid' => $this->message->mpeid, 'globaldecision' => $visaActorDecision];
	}

}

?>