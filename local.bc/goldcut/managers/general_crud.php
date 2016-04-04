<?php
/**
General_Crud
*/
class General_Crud
{

	public function extend($message)
	{
		throw new Exception("EXTEND EMANAGER NOT REALIZED $message");
	}

	public function create($message)
	{
		//println($message,2,TERM_VIOLET);
		$created = Entity::store($message);
		$entityname = $created->urn->E()->name;
		//Broker::instance()->send($created, "ENTITY", "after.create.{$entityname}");
		return $created;
	}

	public function update($message)
	{
		$response = Entity::store($message);
		$updated = $response[0];
		$entityname = $updated->urn->E()->name;
		if (!$message->_skipmq)
			Broker::instance()->send($response, "ENTITY", "after.update.{$entityname}");
		return $updated;
	}

	public function delete($message)
	{
		//Log::error($message, 'delete');
		//throw new Exception($message);
		$entityname = $message->urn->E()->name;
        $ob = $message->urn->resolve();
        if (!count($ob)) return new Message(array('error'=>'Object not exists'));
		$object = $ob->current();
		Broker::instance()->send($object, "ENTITY", "before.delete.{$entityname}");
		// !!! Send this BEFORE real data deletion
		$response = Entity::delete($message);
		return $response;
	}

	public function load($message)
	{
		//if ($message->urn->entitymeta->cacheMemoryEnabled && $message->urn->is_concrete())
		if (ENABLE_QUERY_CACHE === true && $message->urn->is_concrete())
		{
			//dprintln('Cache enabled');
			if ($cached = Cache::get($message->urn))
			{
				//dprintln('From cache');
				return unserialize($cached);
			}
			else
			{
				//dprintln('Query & cache');
				$response = Entity::query($message);
				Cache::put($message->urn, serialize($response));
				return $response;
			}
		}
		else
		{
			//dprintln('No cache');
			$response = Entity::query($message);
			return $response;
		}
	}

	public function increment($m)
	{
		$field = $m->field;
		$F = Field::ref($field);
		$u = new Message();
		$u->action = "update";
		$u->urn = $m->urn;
		if ($F->type == 'integer') $newval = (integer) $m->value;
		if ($F->type == 'float') $newval = (float) $m->value;
		if ($F->type == 'money') $newval = (float) $m->value;
		$u->$field = array('increment' => $newval);
        $u->deliver();
        $x = $m->urn->resolve();
        if (count($x))
            return $x->$field;
        else
            return null;
	}

	public function decrement($m)
	{
		$field = $m->field;
		$F = Field::ref($field);
		$u = new Message();
		$u->action = "update";
		$u->urn = $m->urn;
		if ($F->type == 'integer') $newval = (integer) $m->value;
		else if ($F->type == 'float') $newval = (float) $m->value;
		else if ($F->type == 'money') $newval = (float) $m->value;
		else throw new Exception("No decrement for {$m->value}");
		$u->$field = array('decrement' => $newval);
        $u->deliver();
        $x = $m->urn->resolve();
        if (count($x))
		    return $x->$field;
        else
            return null;
	}

	public function grow($m)
	{
		throw new Exception('GROW is deprecated. Use INCREMENT instead');
	}
}
?>
