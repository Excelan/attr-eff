<?php 

class General_Clone
{
	
	public function copy($message)
	{
		$reference = $message->urn->resolve()->current();
		$data = $reference->toGeneralData();		
		$m = new Message($data);
		$m->urn = $message->urn->generalize();
		$m->action = 'create';
		$copied = $m->deliver();
		return $copied;
	}
	
}
?>