<?php

class URN
{
	private $urn;
	public $entitymeta;
    public $prototype;
	public $entity;
	public $uuid = null;
	public $listname = null;

	public function __construct($urn_string)
	{
        if (!$urn_string) throw new Exception("NEW URN(NULL)");

		if (is_object($urn_string))
			$this->urn = (string) $urn_string;
		else
			$this->urn = $urn_string;

        $testLegacy = explode('-', $this->urn);
        if (count($testLegacy) > 1) throw new Exception("LEGACY URN {$urn_string}");

        $urna = explode(':', $this->urn);
		if ($urna[0] != 'urn') throw new Exception("NOT URN [{$urn_string}]");
        //printlnd($urna);
        $ps = "{$urna[1]}:{$urna[2]}:{$urna[3]}";

        //printlnd(Entity::manager()->listt[$ps]);

        $e = Entity::ref($ps);
        //printlnd($e);
		//$this->entitymeta = Entity::ref($ps);
		$this->entity = $e;
        $this->entitymeta = $e;
		$this->uuid = (int) $urna[4];
        //printlnd($urna[5]);
		if ($urna[5]) $this->listname = $urna[5];
        //printlnd($this->listname,1,TERM_VIOLET);
        if ($this->is_list()) {
            //printlnd("URN {$urn_string} {$this} IS LIST", 1, TERM_VIOLET);
            //printlnd($this->listmeta(), 1, TERM_VIOLET);
        }
				$this->prototype = $this->getPrototype();
    }

	function __toString()
	{
		$str = 'urn:'.$this->entity->prototype;
		if ($this->uuid) $str .= ':'.$this->uuid;
		if ($this->listname) $str .= ':'.$this->listname;
		return $str;
	}

	public static function build($entityname, $uuid=null, $list=null)
	{
		$urnstr = "urn:{$entityname}";
		if ($uuid) $urnstr .= ":{$uuid}";
		//if ($list) $urnstr .= ":{$list}";
		return new URN($urnstr);
	}

	public static function buildConcrete($entityname, $uuid)
	{
		if (!$uuid) return null;
		return new URN("urn:{$entityname}:{$uuid}");
	}

	function getPrototype()
	{
		return new Prototype($this->entity->prototype);
	}

    public function set_list($listname)
    {
        $this->listname = $listname;
    }

    public function resolve($lang=null, $nocache=false)
    {
        if (!$lang) $lang = DEFAULT_LANG;
        if (!$this->is_list())
        {
            return URN::object_by($this->urn, $lang, $nocache);
        }
        else
        {
            $m = new Message();
            $m->action = 'members';
            $m->urn = (string) $this;
            return $m->deliver();
        }
    }

    public static function object_by($urn, $lang=null, $nocache=false)
    {
        if (!$lang) $lang = DEFAULT_LANG;
        $m = new Message();
        $m->action = 'load';
        $m->urn = $urn;
        $m->lang = $lang;
        $m->limit = 1;
        $m->nocache = $nocache;
        return Entity::query($m);
    }

    public static function name_for($object)
    {
        $urna = array();
        $urna[] = 'urn';
        if (get_class($object) == 'DataRow')
            $class = strtolower($object->entitymeta->name);
        else
            $class = strtolower(get_class($object));
        $urna[] = $class;
        $urna[] = $object->uuid();
        return join('-',$urna);
    }

    public function listmeta()
    {
        if (!$this->is_list()) throw new Exception("URN {$this} is not list");
        if (!$this->listname) throw new Exception("Blank list name in get listname in URN");
        return $this->entity->listbyname($this->listname);
    }

    public function entitymeta()
	{
		return $this->part(1);
	}

	public function E()
	{
		return $this->entitymeta;
	}


	/*
	DEPRECATED
	*/
	public function htmlid()
	{
		return str_replace(':', "-", $this->__toString());
	}

	public function uuid()
	{
		if ($uuid = $this->part(4))
			return new UUID($uuid);
		else
			return false;
	}

	public function is_general()
	{
		if ($uuid = $this->part(4))
		{
			if ($uuid)
				return false;
		}
		else
			return true;
	}

	public function is_concrete()
	{
		if ($uuid = $this->part(4))
		{
			if ($uuid)
				return true;
		}
		else
			return false;
	}

	public function is_list()
	{
		if ($list = $this->part(5))
		{
			if ($list)
				return true;
		}
		else
			return false;
	}

	public function generalize()
	{
		return new URN($this->entitymeta->urn);
	}

	public function hasUUID()
	{
		if ($this->part(4))
			return true;
		else
			return false;
	}

	// в отличе от uuid возвращает "как есть", не cast в UUID
	public function resource()
	{
		return $this->part(4);
	}

	private function part($index=1)
	{
		$urna = explode(':', $this->urn);
		return $urna[$index];
	}

	public static function class_of($urn)
	{
		$urna = explode(':', $urn);
		if (count($urna) < 2 || $urna[0] != 'urn') throw new Exception("class_of INVALID URN [{$urn}]");
		$class = ucfirst ( $urna[1] );
		return $class;
	}

	public static function uuid_of($urn)
	{
		$urna = explode(':', $urn);
		if (count($urna) < 2 || $urna[0] != 'urn') throw new Exception("INVALID URN [{$urn}]");
		$indexlast = count($urna)-1;
		println($urna[$indexlast]);
		$uuid = new UUID($urna[$indexlast]);
		if (!($uuid->toInt() > 0)) throw new Exception("URN NOT CONTAIN UUID FOR CLASS [{$class}]. full urn: [{$urn}]");
		return $uuid;
	}

	public function keys()
	{
		return explode(':', $this->urn);
	}


}

?>