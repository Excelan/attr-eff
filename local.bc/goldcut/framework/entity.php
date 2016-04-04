<?php
/**
Helper static methods:
	childIds($urn, $cid)
	extenderPropertiesVariatorsHelper($extender)
*/
class Entity
{
	private $list; // by uid
	public $listt; // by name
	private static $instance;
	private function __construct() {}


	private function init()
	{
		$this->list = $GLOBALS['CONFIG']['ENTITY'];
		foreach ($this->list as $uid => $e)
		{
			if (!$this->listt[$e->name]) $this->listt[$e->name] = $e;
			else throw new Exception("Entity {$e->name} eid {$e->uid} already exists in ".$this->listt[$e->name]->uid.' '.join(' ',$this->listt[$e->name]->title));
		}
		ksort($this->list);
	}

	public static function manager() {
		if (!self::$instance) { self::$instance = new Entity(); self::$instance->init(); } return self::$instance;
	}

	public static function ref($uid, $nothrow = false) 
	{
        if (!$uid) throw new Exception("Entity:ref(NULL)");
		if (is_int($uid))
		{
			$r = Entity::manager()->list[$uid];
		}
		else if (is_array($uid))
		{
			$usedAs = key($uid);
			$baseName = $uid[$usedAs];
			//println("$baseName as $usedAs",2,TERM_GREEN);
			$r = Entity::manager()->listt[$baseName];
		}
		else 
		{
			$r = Entity::manager()->listt[$uid];
		}
		if ($r)
		{
			return $r;
		}
		else
		{
			if ($nothrow)
				return false;
			else
                throw new Exception("Entity " . json_encode($uid) . " not exists");
		}
	}
	
	public static function exists($uid) {
		$r = Entity::ref($uid, true);
		if (!$r) return false;
		else return true;
	}

	static function each_entity() {
		return Entity::manager()->list;
	}

	static function each_toplevel_entity() {
		$r=array();
		foreach ( Entity::each_entity() as $entity ) {
			if (!$entity->belongs_to())
				$r[]=$entity;
		}
		return $r;
	}

	static function each_managed_entity($onlymanagers=null, $onlyentities=null) 
	{
		if ($onlymanagers && !is_array($onlymanagers)) $onlymanagers = array($onlymanagers);
		if ($onlyentities && !is_array($onlyentities)) $onlyentities = array($onlyentities);
		$r=array();
		foreach (Entity::each_entity() as $entity) 
		{
			$manager_name = $entity->class;
			if (!$onlymanagers)
			{
				if ($onlyentities)
					{
						if (in_array($entity->name, $onlyentities))
							$r[$manager_name][] = $entity;
					}
					else
						$r[$manager_name][] = $entity;
			}
			else
			{
				if (in_array($manager_name, $onlymanagers))
				{
					if ($onlyentities)
					{
						if (in_array($entity->name, $onlyentities))
							$r[$manager_name][] = $entity;
					}
					else
						$r[$manager_name][] = $entity;
				}
			}
		}
		return $r;
	}

	public static function new_uuid($e)
	{
		$id = new UUID();
		return $id;
	}

	public static function translate($data)
	{
		$E = $data->urn->E();
		if ( !($data->urn) ) throw new Exception("Provide URN of entity to translate!");
		if (!$data->urn->is_concrete()) throw new Exception("Provide ID of entity to translate!");
		if ( !$lang = $data->lang ) throw new Exception("Provide LANG to translate entity!");
		$estore = new EntityDB_translate($E);
		$estore->provide_translate($data->urn->uuid()->toInt(), $lang, $data);
		$translated = new Message();
		$translated->urn = $data->urn; //"urn-".$E->name.":".$uuid;
		return $translated;
	}

	public static function store($data)
	{
		// BOTH CREATE & UPDATE
		return EntityStore::store($data);
	}

	public static function query($query)
	{
		if (is_string($query)) $query = new URN($query);
		$class = get_class($query);
		if ($class == 'Message' || $class == 'ManagedMessage' || $class == 'URN')
		{
			if ($class == 'URN') $query = new Message(array( "urn" => (string) $query ));
			if (DEBUG_QUERY === true)	println($query,1,TERM_YELLOW);
			return EntityQuery::query($query);
		}
		else
			throw new Exception("Entity::query() params have to be Message or URN");
	}

	public static function delete($data)
	{
		$E = $data->urn->E();
		$dbm = new EntityDB_delete($E);
		$deleted = $dbm->delete($data);

		// TODO check has relations, then clean
		$db = DB::link();
		// TODO HasMany delete CASCADE?
		foreach ($E->related() as $rel)
		{
			if ($E->uid <= $rel->uid)
			{
				$db->raw_query("DELETE FROM mappings WHERE entity1 = {$E->uid} AND entity2 = {$rel->uid} AND id1 = {$data->urn->uuid}");
			}
			else
			{
				$db->raw_query("DELETE FROM mappings WHERE entity1 = {$rel->uid} AND entity2 = {$E->uid} AND id2 = {$data->urn->uuid}");
			}
		}
		$m = new Message();
		$m->urn = $data->urn;
		$m->status = 0;
		$m->info = 'deleted';
		return $m;
	}
	
	public static function extenderAttributesHelper($extender, $d=null)
    {
        $attrs = array();
        foreach ($extender as $ext)
        {
//            dprintln($ext,1,TERM_GREEN);
            $attributes = $ext->attribute;
            foreach ($attributes as $attribute) {
//                dprintln($attribute->uri, 2, TERM_YELLOW);
//                dprintln($attribute->metainfo, 2, TERM_YELLOW);
//                dprintln($attribute->struct, 3, TERM_YELLOW);
                array_push($attrs, $attribute);
            }
        }
        if ($d) {
            $self = $d->urn->resolve();
            $attributesds = $self->attribute;
            foreach ($attributesds as $attr) {
                $attrs[] = $attr;
            }
        }
        return $attrs;
    }

	public static function extenderPropertiesVariatorsHelper($extender)
	{
		if (count($extender) > 1)
		{
			foreach ($extender as $ext)
			{
				$propertiesInParent = $ext->property;
				$variatorsInParent = $ext->variator;
				if (!$properties || !$variators)
				{
					$properties = $propertiesInParent;
					$variators = $variatorsInParent;  
				}
				else
				{
					$properties->merge($propertiesInParent);
					if (count($variators))
						$variators->merge($variatorsInParent); // TODO fix bug: если $ext->variator пустой, то тип dataset в ответе category, а не variator
					else 	
						$variators = $variatorsInParent;
				}
			}
		}
		else
		{
			$properties = $extender->property;
			$variators = $extender->variator;
		}
		return array('properties'=>$properties,'variators'=>$variators);
	}
	
	public static function childIds($urn, $cid)
	{
		$m = new Message();
		$m->action = 'load';
		$m->urn = $urn;
		$ds = $m->deliver();
		$ds->treesort();
		$childs = array();
		foreach ($ds as $d)
		{
			if ($d->id == $cid) 
			{
				$start = true;
				$clevel = $d->_level;
			}
			if ($start)
			{
				$i++;
				if ($i==1)
				{
					$childs[] = $d->id;
				}
				else
				{
					if ($d->_level > $clevel)
						$childs[] = $d->id;
					else
						$start = false;
				}
				
			}
			
		}
		return $childs;
	}

}

?>