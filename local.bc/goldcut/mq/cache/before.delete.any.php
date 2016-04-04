<?php
/**
before - ДО, тк ПОСЛЕ данных уже нет и связи отследить невозможно
*/

function before_delete_any($del)
{
	foreach (SystemLocale::$ALL_LANGS as $lang)
	{
		$key = $del->urn->entity->name.':'.$del->urn->uuid.':'.$lang;
		Cache::clear($key);
	}
}

if (ENABLE_QUERY_CACHE === true && Cache::is_enabled())
{
	$broker = Broker::instance();
	$broker->queue_declare ("ENITY_USERFOLDER_CONSUMER", DURABLE, NO_ACK);
	$broker->bind("ENTITY", "ENITY_USERFOLDER_CONSUMER", "before.delete");
	$broker->bind_rpc ("ENITY_USERFOLDER_CONSUMER", "before_delete_any");
}
?>