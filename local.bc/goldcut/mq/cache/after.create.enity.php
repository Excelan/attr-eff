<?php

function after_any_created($new)
{
	$key = $new->urn->entity->name.':'.$new->urn->uuid.':'.$new->lang;
	//Cache::clear($key);
	Cache::put($key, $new->urn->resolve(false, true));
}

if (ENABLE_QUERY_CACHE === true && Cache::is_enabled())
{
	$broker = Broker::instance();
	$broker->queue_declare ("ENITYCONSUMERANY", DURABLE, NO_ACK);
	$broker->bind("ENTITY", "ENITYCONSUMERANY", "after.create");
	$broker->bind_rpc ("ENITYCONSUMERANY", "after_any_created");
}

?>