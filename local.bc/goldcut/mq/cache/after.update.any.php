<?php
function after_update_any($m)
{
	$old = $m[1]; // DataRow
	$new = $m[0]; // Message
	
	
	//printlnd($old,1,TERM_GRAY);
	//printlnd($old->color[0]);
	
	//printlnd($new,1,TERM_GRAY);
	//printlnd($new->urn->resolve()->color[0]);
	
	$key = $new->urn->entity->name.':'.$new->urn->uuid.':'.$new->lang;
	Cache::clear($key);

	/**
	Нужны все и только измененные поля - важно не потерять старую категорию, когда нельзя определить старый урл полностью при ее смене
	*/
	
	/**
	
	CacheInvalidate::all($new, $old);
	
	if ($new->active && $new->uri != $old->uri && ( $new->urn->E()->name == 'news' ) ) //  or $new->urn->E()->name == 'hub'
	{
		dprinln('Create redirect');
		if (!strlen($new->uri)) return false;
		$mm = new Message();
		$mm->action = 'create';
		$mm->urn = 'urn-redirect';
		$mm->uri = $old->url;
		$mm->target = $new->urn->resolve()->url;
		$mm->deliver();
	}
	*/
}

if (ENABLE_QUERY_CACHE === true && Cache::is_enabled())
{
	$broker = Broker::instance();
	$broker->queue_declare("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
	$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "after.update");
	$broker->bind_rpc ("ENITYUPDATECONSUMER", "after_update_any");
}
?>