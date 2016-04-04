<?php

function after_update_any_ent($m)
{
	$old = $m[1]; // DataRow
	$new = $m[0]; // Message
	
	if ($old->extended)
	{
		//printlnd($old); // ->__variators
		//printlnd($new); // ->__variators
		if ($new->__extended)		
			$newExtended = $new->__extended->toArray();
		else
			return;
		//printlnd($newExtended);		
		foreach ($old->__variators as $variatorName => $variationsIds)
		{
			//dprintln($variatorName);
			//dprintln($variationsIds);
			$newVals = $newExtended[$variatorName];
			if ($newVals) // variator key present in updates
			{
				if (!is_array($newVals)) $newVals = array($newVals);
				if (!is_array($variationsIds)) $variationsIds = array($variationsIds);
				foreach ($newVals as $nv)
				{
					if (!in_array($nv, $variationsIds))  // $nv NOT exists in old. Add {$old->id} to list-variator-vid-entity OR it exists in old & dont changed'
						ListDatabase::setAdd(array('variator', $nv, $new->urn->entity->name), $old->id);
				}
				foreach ($variationsIds as $nv)
				{
					if (!in_array($nv, $newVals))  // exists in new vals.  not changed OR NOT exists in new vals. Remove'
						ListDatabase::setRemove(array('variator', $nv, $new->urn->entity->name), $old->id);
				}
			}
			else // variator key not present in updates
			{
				// remove all old variations
				foreach ($variationsIds as $nv)
					ListDatabase::setRemove(array('variator', $nv, $new->urn->entity->name), $old->id);			
			}
		}
	}
}

if (EXTENDEDSTRUCTURE === true)
{
	$broker = Broker::instance();
	$broker->queue_declare("ENITYUPDATECONSUMER_ENT", DURABLE, NO_ACK);
	$broker->bind("ENTITY", "ENITYUPDATECONSUMER_ENT", "after.update.ad");
	$broker->bind_rpc ("ENITYUPDATECONSUMER_ENT", "after_update_any_ent");
}
?>