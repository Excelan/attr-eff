<?php

class StatusMeta
{
	public $uid;
	public $default = 0; // default value
	public $name;
	public $title;

	function __construct($config)
	{
		foreach ($config as $option => $value)
			$this->$option = $value;
	}
}

?>