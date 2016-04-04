<?php

class Listing 
{
	public $entity;
	public $ids = array();
	
	public function __construct($entity, $ids=array())
	{
		$this->entity = $entity;
		$this->ids = $ids;
	}
	
	public function add($id)
	{
		array_push($this->ids, (int) $id);
	}
	
	public function __toString()
	{
		return "{$this->entity} ".json_encode($this->ids);
	}
	
	public function count()
	{
		return count($this->ids);
	}
		
	public function resolve()
	{
		if (count($this->ids))
		{
			$m = new Message();
			$m->urn = $this->entity->urn;
			$m->action = 'load';
			$m->id = $this->ids;
			//$m->in = $this; // WORKING
			return $m->deliver();
		}
		else return array();
	}
}

?>