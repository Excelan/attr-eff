<?php

// TODO return cache invalidate!!!

function after_any_created_ent($new)
{
	
	//println('after_any_created_ent');
	//println($new);
	if ($new->__extended)
	{	
		//println('new extended vars - new');
		$newExtended = $new->__extended->toArray();
		//println($newExtended);
		foreach ($newExtended as $variatorName => $variationsIds)
		{
			//println($variatorName);
			//println($variationsIds);
			if (!is_array($variationsIds)) $variationsIds = array($variationsIds);
			foreach ($variationsIds as $nv)
			{
				ListDatabase::setAdd(array('variator', $nv, $new->urn->entity->name), $new->urn->uuid);
			}
		}
	}
}

if (EXTENDEDSTRUCTURE === true)
{
	$broker = Broker::instance();
	$broker->queue_declare ("ENITYCONSUMER_ENT", DURABLE, NO_ACK);
	$broker->bind("ENTITY", "ENITYCONSUMER_ENT", "after.create");
	$broker->bind_rpc ("ENITYCONSUMER_ENT", "after_any_created_ent");
}
?>