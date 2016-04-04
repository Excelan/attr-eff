<?php
namespace Decision;

class VisaByOne extends \Gate
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
        $globalDecision = 'pending';

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $this->message->mpeid;
        $mpe = $m->deliver();
        if (!count($mpe)) throw new \Exception("No MPE by {$this->message->mpeid}");
        if (count($mpe) > 1) throw new \Exception("No MPE by {$this->message->mpeid}");
        \Log::info((string)$mpe->current(), 'decision');

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:DMS:DecisionSheet:Signed';
        $m->document = $mpe->subject;
        //$m->closed = false; // TODO NOT WORKS NOW
        $m->order = 'created DESC';
        $m->last = 1;
        $sheet = $m->deliver();
        if (!count($sheet)) throw new \Exception("No DecisionSheet by mpe.subject {$mpe->subject}");
        if (count($sheet) > 1) throw new \Exception("More then 1 non closed DecisionSheet by mpe.subject {$mpe->subject}");

//        println(count($sheet->needsignfrom),1,TERM_YELLOW);
//        println((count($sheet->hassignfrom) + count($sheet->hascancelfrom)),1,TERM_YELLOW);
//        printlnd($sheet->hassignfrom);
//        printlnd($sheet->hascancelfrom);

        // удалить старое решение 1
        $m = new \Message();
        $m->urn = $sheet->urn;
        $m->action = 'load';
        $m->hascancelfrom = ['exists' => (string)$this->message->actor];
        $exists = $m->deliver();
        if (count($exists))
        {
            $m = new \Message();
            $m->urn = $sheet->urn;
            $m->action = 'update';
            $m->hascancelfrom = ['remove' => (string)$this->message->actor];
            $m->deliver();
        }
        // удалить старое решение 2
        $m = new \Message();
        $m->urn = $sheet->urn;
        $m->action = 'load';
        $m->hassignfrom = ['exists' => (string)$this->message->actor];
        $exists = $m->deliver();
        if (count($exists))
        {
            $m = new \Message();
            $m->urn = $sheet->urn;
            $m->action = 'update';
            $m->hassignfrom = ['remove' => (string)$this->message->actor];
            $m->deliver();
        }

        // СОХРАНИТЬ РЕШЕНИЕ В ОДИН ИЗ СПИСКОВ
        if ($visaActorDecision == 'cancel') // visa canceled
        {
            $m = new \Message();
            $m->urn = $sheet->urn;
            $m->action = 'update';
            $m->hascancelfrom = ['append' => (string)$this->message->actor];
            $m->deliver();
            // комментарий причины отказа
            if ($this->message->status == 'cancel') {
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
        }
        else // vised
        {
            $m = new \Message();
            $m->urn = $sheet->urn;
            $m->action = 'update';
            $m->hassignfrom = ['append' => (string)$this->message->actor];
            $m->deliver();

            $visedvariants = json_decode($this->message->visedvariants, true);
            foreach ($visedvariants as $visedvariant)
            {
                $m = new \Message();
                $m->action = 'add';
                $m->urn = (string) $this->message->actor;
                $m->to = $visedvariant.':visedby';
                $m->deliver();
            }
        }

        // все ли уже завизировали?
        $sheet = $sheet->urn->resolve();
        if (count($sheet->needsignfrom) < count($sheet->hassignfrom) + count($sheet->hascancelfrom)) {
            foreach ($sheet->needsignfrom as $needsignfrom) println($needsignfrom,2,TERM_VIOLET);
            //println($sheet->hassignfrom,3,TERM_GREEN);
            foreach ($sheet->hassignfrom as $hassignfrom) println($hassignfrom,3,TERM_GREEN);
            foreach ($sheet->hascancelfrom as $hascancelfrom) println($hascancelfrom,3,TERM_RED);
            throw new \Exception("needsignfrom < hassignfrom + hascancelfrom");
        }
        // все византы подписали
        if ( count($sheet->needsignfrom) == count($sheet->hassignfrom) + count($sheet->hascancelfrom) )
        {
            \Log::debug((string)$sheet->current(), 'decision');
            // есть хоть один отказавшийся визировать
            if (count($sheet->hascancelfrom)) {
                $globalDecision = 'cancel';
            }
            else
                $globalDecision = 'visa';
            \Log::debug("globalDecision $globalDecision", 'decision');

            // закрываем Sheet
            $m = new \Message();
            $m->action = 'update';
            $m->urn = $sheet->urn;
            $m->closed = true;
            $m->deliver();

            // создаем новый Sheet с теми же византами
            if ($globalDecision == 'cancel')
            {
                /*
                $m = new Message();
                $m->gate = 'Process/RemapDecisionSheet';
                $m->subjectURN = $mpe->subject;
                $m->mpeId = $this->message->mpeid;
                $m->send();
                */
                /**
                $m = new \Message();
                $m->action = 'create';
                $m->urn = 'urn:DMS:DecisionSheet:Signed';
                $m->document = $mpe->subject;
                $m->needsignfrom = $sheet->needsignfrom;
                //$m->hascancelfrom = [];
                //$m->hassignfrom = [];
                $newsheet = $m->deliver();
                 * */
            }

            // load metadata
            //\Log::info($mpe->metadata, 'decision');
            $metadata = $mpe->metadata;
            // update metadata
            $metadata->decision = $globalDecision;
            \Log::debug($metadata, 'decision');
            //
            $m = new \Message();
            $m->action = 'update';
            $m->urn = $mpe->urn;
            $m->metadata = json_encode($metadata);
            $m->deliver();

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

        }
        else
        {
            \Log::debug((string)$sheet, 'decision');
            \Log::debug('NOT ALL DECIDED', 'decision');
        }

        \Log::debug($this->message->ticketurn, 'rznasa');

        //закрываем тикет
        if($this->message->ticketurn) {
            $m = new \Message();
            $m->action = 'update';
            $m->urn = $this->message->ticketurn;
            $m->allowopen = false;
            $ticket = $m->deliver();
        }


        // return
		return ['mpeid' => $this->message->mpeid, 'globaldecision' => $globalDecision];
	}

}

?>