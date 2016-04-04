<?php 

ListDatabase::allKeysDel();

foreach (Entity::each_entity() as $entity)
{
	if (count($entity->extendstructure))
	{
		$m = new Message();
		$m->action = 'load';
		$m->urn = (string) $entity;
		$m->active = true;
		$all = $m->deliver();
		foreach ($all as $r)
		{
			foreach ($r->__variators as $variatorName => $variationsIds)
			{
				if (!is_array($variationsIds)) $variationsIds = array($variationsIds);
				foreach ($variationsIds as $nv) 
				{
					ListDatabase::setAdd(array('variator', $nv, $r->urn->entity->name), $r->id);
					//println($r->title);
					println(array('variator', $nv, $r->urn->entity->name, '<<', $r->id));
				}
			}
		}
	}
}


$dblink = DB::link();
$q = "SELECT * FROM mappings";
$r = $dblink->tohashquery($q);
foreach ($r as $mp)
{
	$relation_ns = $mp['ns'];
	$mp['entity1'] = (int) $mp['entity1'];
	$mp['entity2'] = (int) $mp['entity2'];
	if ($mp['entity1'] < $mp['entity2'])
	{
		$ek = 'entity1';
		$rk = 'entity2';
		$eidk = 'id1';
		$ridk = 'id2';
	}
	else
	{
		$ek = 'entity2';
		$rk = 'entity1';
		$eidk = 'id2';
		$ridk = 'id1';
	}
	//println($mp);
	try {
		$E = Entity::ref($mp[$ek]);
		$rel = Entity::ref($mp[$rk]);
		$obj_uuid = $mp[$eidk];
		$rel_uuid = $mp[$ridk];
		if ($relation_ns > 0)
		{
			$list = $E->listbyns($relation_ns);
			/*
			if ($mp['entity1'] < $mp['entity2'])
				$list = $E->listbyns($relation_ns);
			else 
				$list = $rel->listbyns($relation_ns);
			*/	
			$key = array($E->name, $obj_uuid, $list['name']);
			ListDatabase::setAdd($key, $rel_uuid);
			// else delete sql row
		}
		else
		{
			$key = array($E->name, $obj_uuid, $rel->name);
			ListDatabase::setAdd($key, $rel_uuid);
		}
		println($key);
		//println($rel_uuid);
	}
	catch (Exception $e)
	{
		println('.');
		//printH("$E $rel $relation_ns");
		//println($e->getMessage(), 1, TERM_RED);
	}
}
?>