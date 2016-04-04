<?php 
class General_Multylang
{
	
	public function translate($message)
	{
		if (!$message->urn->entity->is_multy_lang()) throw new Exception("{$message->urn->entity} is not multylang");
		$response = Entity::translate($message);
		$entityname = $response->urn->E()->name;
        $tr = "translator{$message->lang}";
		Broker::instance()->send(new Message(array('lang'=>$message->lang, 'translator' => $message->$tr, 'urn' => $message->urn)), "ENTITY", "after.translate.{$entityname}");
		return $response;
	}
}
?>