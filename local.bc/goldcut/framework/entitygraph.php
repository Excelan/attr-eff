<?php 
class EntityGraph
{
	/**
	CREATE
	*/
	public static function edgeCreate($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->with) throw new Exception('Provide with for edge create');
		$subject = $data->with;
		$subjectEntity = $subject->entity;
		$subjectID = $subject->uuid->toInt();
		$type = $data->type ? $data->type : 0;
		$metadata = $data->metadata ? "'".json_encode($data->metadata->toArray())."'" : 'NULL';
		$rdb = DB::link();
		$r = $rdb->nquery("INSERT INTO mappings (entity1, entity2, id1, id2, relation, ns) VALUES ( {$objectEntity->uid}, {$subjectEntity->uid}, {$objectID}, {$subjectID}, {$metadata}, {$type} )");
		return $data;
	}
	
	/**
	DELETE
	*/
	public static function edgeRemove($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->with) throw new Exception('Provide with for edge remove');
		$subject = $data->with;
		$subjectEntity = $subject->entity;
		$subjectID = $subject->uuid->toInt();
		$andtype = ($data->type && $data->type >= 0) ? "AND ns = {$data->type}" : '';
		$rdb = DB::link();
		$rdb->nquery("DELETE FROM mappings WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} AND id2 = {$subjectID} {$andtype}");
		return $data;
	}
	
	/**
	EXISTS
	*/
	public static function edgeExists($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->with) throw new Exception('Provide with for edge exists');
		$subject = $data->with;
		$subjectEntity = $subject->entity;
		$subjectID = $subject->uuid->toInt();
		$type = $data->type ? $data->type : 0; 
		$rdb = DB::link();
		$q = "SELECT count(entity1) AS edgecount FROM mappings WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} AND id2 = $subjectID AND ns = {$type}";
		$r = $rdb->count_query($q);
		$m = new Message();
		$m->exists = $r['edgecount'];
		return $m; 
	}

    /**
    LOAD
     */
    public static function edgeLoad($data)
    {
        $objectID = $data->urn->uuid;
        $objectEntity = $data->urn->entity;
//        $objectEntityName = $objectEntity->name;
        if (!$data->with) throw new Exception('Provide with for edge exists');
        $subject = $data->with;
        $subjectEntity = $subject->entity;
        $subjectID = $subject->uuid->toInt();
        $type = $data->type ? $data->type : 0;
        $andNS = $data->type ? "AND ns = {$type}" : '';
        $rdb = DB::link();
        $q = "SELECT * FROM mappings WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} AND id2 = $subjectID {$andNS}";
        $r = $rdb->tohashquery($q);
        foreach ($r as $edgeraw)
        {
            $fromEntity = Entity::ref((int) $edgeraw['entity1']);
            $nodeFrom = URN::build($fromEntity->name, $edgeraw['id1']);
            $toEntity = Entity::ref((int) $edgeraw['entity2']);
            $nodeTo = URN::build($toEntity->name, $edgeraw['id2']);
            $etype = $edgeraw['ns'] ? $edgeraw['ns'] : null;
            $metadata = json_decode($edgeraw['relation'], true);
            // $weight = ?
            $edge = new Edge($nodeFrom, $nodeTo, $etype, $weight, $metadata);
        }
        return $edge;
    }
	
	public static function edgesRemoveAll($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->to) throw new Exception('Provide to for edges From-To');
		$subject = $data->to;
		$subjectEntity = $subject->entity;
		//$subjectID = $subject->uuid->toInt();
		$type = $data->type ? $data->type : 0; 
		$andNS = $data->type ? "AND ns = {$type}" : '';
		$rdb = DB::link();
		$q = "DELETE FROM mappings WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} {$andNS}";
		$rdb->nquery($q);
	}
	
	/**
	EDGES FROM
	*/
	public static function edgesFrom($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->to) throw new Exception('Provide to for edges From-To');
		$subject = $data->to;
		$subjectEntity = $subject->entity;
		//$subjectID = $subject->uuid->toInt();
		$type = $data->type ? $data->type : 0; 
		$andNS = $data->type ? "AND ns = {$type}" : '';
		$rdb = DB::link();
		$q = "SELECT * FROM mappings WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} {$andNS}";
		$r = $rdb->tohashquery($q);
		foreach ($r as $edgeraw)
		{
			$fromEntity = Entity::ref((int) $edgeraw['entity1']);
			$nodeFrom = URN::build($fromEntity->name, $edgeraw['id1']);
			$toEntity = Entity::ref((int) $edgeraw['entity2']);
			$nodeTo = URN::build($toEntity->name, $edgeraw['id2']);
			$etype = $edgeraw['ns'] ? $edgeraw['ns'] : null;
			$metadata = json_decode($edgeraw['relation'], true);
			$edges[] = new Edge($nodeFrom, $nodeTo, $etype, $weight, $metadata);
		}
		return $edges; 
	}
	
	/**
	EDGES TO
	*/
	public static function edgesTo($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->from) throw new Exception('Provide from for edges To-From');
		$subject = $data->from;
		$subjectEntity = $subject->entity;
		//$subjectID = $subject->uuid->toInt();
		$type = $data->type ? $data->type : 0;
		$andNS = $data->type ? "AND ns = {$type}" : '';
		$rdb = DB::link();
		$q = "SELECT * FROM mappings WHERE entity2 = {$objectEntity->uid} AND entity1 = {$subjectEntity->uid} AND id2 = {$objectID} {$andNS}";
		$r = $rdb->tohashquery($q);
		foreach ($r as $edgeraw)
		{
			$fromEntity = Entity::ref((int) $edgeraw['entity1']);
			$nodeFrom = URN::build($fromEntity->name, $edgeraw['id1']);
			$toEntity = Entity::ref((int) $edgeraw['entity2']);
			$nodeTo = URN::build($toEntity->name, $edgeraw['id2']);
			$etype = $edgeraw['ns'] ? $edgeraw['ns'] : null;
			$metadata = json_decode($edgeraw['relation'], true);
			$edges[] = new Edge($nodeFrom, $nodeTo, $etype, $weight, $metadata);
		}
		return $edges; 
	}
	
	/**
	SET DATA
	*/
	public static function edgeSetData($data)
	{
		$objectID = $data->urn->uuid;
		$objectEntity = $data->urn->entity;
		$objectEntityName = $objectEntity->name;
		if (!$data->with) throw new Exception('Provide with for edge data');
		$subject = $data->with;
		$subjectEntity = $subject->entity;
		$subjectID = $subject->uuid->toInt();
		$type = ($data->type && $data->type >= 0) ? "AND ns = {$data->type}" : ''; 
		$rdb = DB::link();
		$q = "SELECT * FROM mappings WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} AND id2 = $subjectID $type";
		$r = $rdb->tohashquery($q);
		foreach ($r as $edgeraw)
		{
			$fromEntity = Entity::ref((int) $edgeraw['entity1']);
			$nodeFrom = URN::build($fromEntity->name, $edgeraw['id1']);
			$toEntity = Entity::ref((int) $edgeraw['entity2']);
			$nodeTo = URN::build($toEntity->name, $edgeraw['id2']);
			$etype = $edgeraw['ns'] ? $edgeraw['ns'] : null;
			$metadata = json_decode($edgeraw['relation'],true);
			$edge = new Edge($nodeFrom, $nodeTo, $etype, $weight, $metadata);
			if ($edgeraw['relation'] == '0') throw new Exception("OLD relation table with INT metadata column!");
			$metadata = $data->metadata->toArray();
			foreach ($metadata as $mk => $mv)
			{
				if (!is_array($mv))
				{
					$edge->metadata[$mk] = $mv;
				}
				else
				{
					switch ($cc = key($mv))
					{
						case 'increment':
							$edge->metadata[$mk] += $mv[$cc];
							break;
						case 'decrement':
							$edge->metadata[$mk] -= $mv[$cc];
							break;
						case 'clear':
							unset($edge->metadata[$mk]);
							break;
						default:
							throw new Exception('Unknown Edge Data command');
							break;
					}
				}
				//$edge->metadata = array_merge($edge->metadata, $metadata);
			}
			$rdb->nquery("UPDATE mappings SET relation = '".json_encode($edge->metadata)."' WHERE entity1 = {$objectEntity->uid} AND entity2 = {$subjectEntity->uid} AND id1 = {$objectID} AND id2 = $subjectID $type");
			$edges[] = $edge;
		}
		return $edges;
	}
}
?>