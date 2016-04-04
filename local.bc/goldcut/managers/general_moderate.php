<?php 

class General_Moderate
{
	
	public function approve($message)
	{
		$m = new Message();
		$m->urn = $message->urn;
		$m->action = 'update';
		$m->active = true;
		$m->deliver();
		
		$entityname = $message->urn->E()->name;
		Broker::instance()->send($message, "ENTITY", "after.approve.{$entityname}"); // .Subject
		return new Message('{"urn":"'.$message->urn.'", "moderate": "approve"}');
	}
	
	public function deny($message)
	{
		$m = new Message();
		$m->urn = $message->urn;
		$m->action = 'update';
		$m->active = false;
		$m->deliver();
		
		$entityname = $message->urn->E()->name;
		Broker::instance()->send($message, "ENTITY", "after.deny.{$entityname}"); // .Subject
		return new Message('{"urn":"'.$message->urn.'", "moderate": "deny"}');
	}
	
}
?>