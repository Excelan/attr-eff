<?php 

class General_Report
{
	
	public function report($message)
	{
		$m = new Message();
		$m->urn = $message->urn;
		$m->action = 'increment';
		$m->field = 'complain';
		$m->value = 1;
		$m->deliver();
		
		$m = new Message();
		$m->urn = 'urn:complain';
		$m->action = 'create';
		$m->report_reason = $message->reason;
		$m->feedback_text = $message->reporttext;
		$m->ad = $message->urn; // ! non universal
		if ($this->user) $m->user = $this->user;
		
		$m->deliver();
		
		$entityname = $message->urn->E()->name;
		Broker::instance()->send($message, "ENTITY", "after.report.{$entityname}"); // .Subject
		return new Message('{"urn": "'.$message->urn.'", "complain": "done"}');
	}
	
}
?>