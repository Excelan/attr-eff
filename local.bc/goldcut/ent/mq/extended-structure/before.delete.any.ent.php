<?php
/**
before - ДО, тк ПОСЛЕ данных уже нет и связи отследить невозможно
*/

function before_delete_any_ent($del)
{
	
	if ($del->extended)
	{
		//dprintln('extended vars - old, new');
		//dprintlnd($del->__variators);		
		foreach ($del->__variators as $variatorName => $variationsIds)
		{
			if (!is_array($variationsIds)) $variationsIds = array($variationsIds);	
			foreach ($variationsIds as $nv)
				ListDatabase::setRemove(array('variator', $nv, $del->urn->entity->name), $del->id);
		}	
	}
}

if (EXTENDEDSTRUCTURE === true)
{
	$broker = Broker::instance();
	$broker->queue_declare ("ENITY_USERFOLDER_CONSUMER_ENT", DURABLE, NO_ACK);
	$broker->bind("ENTITY", "ENITY_USERFOLDER_CONSUMER_ENT", "before.delete");
	$broker->bind_rpc ("ENITY_USERFOLDER_CONSUMER_ENT", "before_delete_any_ent");
}
?>