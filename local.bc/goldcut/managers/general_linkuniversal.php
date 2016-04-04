<?php 
/**
General_Crud
*/
class General_Linkuniversal
{
	public function link($message)
	{
		$response = EntityLinks::link($message);
		$entityname = $message->urn->E()->name;
		Broker::instance()->send($message, "ENTITY", "after.link.{$entityname}"); // .Subject
		return $response;
	}
	
	public function unlink($message)
	{
		$response = EntityLinks::unlink($message);
		$entityname = $message->urn->E()->name;
		Broker::instance()->send($message, "ENTITY", "after.unlink.{$entityname}"); // .Subject
		return $response;
	}
	
}
?>