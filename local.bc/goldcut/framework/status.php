<?php

class Status
{

	public $all = array();
	public $allt = array();
	
	private static $instance;
	private function __construct() {}

	private function init()
	{
		$this->all = $GLOBALS['CONFIG']['STATUS'];
		foreach ($this->all as $uid => $f)
			$this->allt[$f->name] = $f;
	}

	public function reinit()
	{
		$this->init();
	}

	public static function manager() 
	{
		if (!self::$instance) { self::$instance = new Status(); self::$instance->init(); } return self::$instance;
	}
	
	public static function exists($uid)
	{
		if (is_int($uid)) 
			$f = Status::manager()->all[$uid];
		else 
			$f = Status::manager()->allt[$uid];
		if (!$f) 
			return false;
		if ($f)
			return true;
	}
	
	public static function ref($uid)
	{
		if (is_int($uid)) 
			$f = Status::manager()->all[$uid];
		else 
			$f = Status::manager()->allt[$uid];
		if (!$f) 
			return false;
		if ($f)
			return $f;
		else 
			throw new Exception("Status $uid not exists");
	}
	
	

}

?>