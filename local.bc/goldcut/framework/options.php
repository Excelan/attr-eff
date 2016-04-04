<?php
/**
DEFAULTS for option if user not set
*/
// TODO options as kernel dots

class Options
{

	private static $instance;
	private $options;

	private function __construct()
	{

	}

	private function init()
	{

	}

	public static function get($option)
	{
		if (!self::$instance) { self::$instance = new OPTIONS(); self::$instance->init(); }
			return self::$instance->options[$option];
	}

	public static function set($option, $value)
	{
		if (!self::$instance) { self::$instance = new OPTIONS(); self::$instance->init(); }
			self::$instance->options[$option] = $value;
		return true;
	}

}
?>