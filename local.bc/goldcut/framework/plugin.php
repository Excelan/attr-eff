<?php

/**
TODO !!! rename to Data Decorator
*/

class Plugin
{

	private static $instance;
	private $classes = array();
	private function __construct() {}

	public static function manager()
	{
		if (!self::$instance) { self::$instance = new Plugin(); self::$instance->init(); } return self::$instance;
	}

	public function get($dr, $function_name)
	{

		$entity = $dr->entitymeta;
		$AppClass = $entity->getAlias().'Plugin';
		
		$plugin_file = PLUGINS_DIR . $entity->getAlias() . '.php';
		$plugin_local_file = PLUGINS_LOCAL_DIR . $entity->getAlias() . '.php';

		if (file_exists($plugin_file))
		{
			$plugin_path = $plugin_file;
			if (!$this->classes[$AppClass])
			{
				require $plugin_path;
			}
			$this->classes[$AppClass] = true;
			$loaded = true;
		}
		if (file_exists($plugin_local_file))			
		{
			$plugin_path = $plugin_local_file;
			if (!$this->classes[$AppClass])
			{
				require $plugin_path;
			}
			$this->classes[$AppClass] = true;
			$loaded = true;
		}
		/*
		$plugin_file = PLUGINS_DIR . $entity->name . '_' . $function_name . '.php';
		if (file_exists($plugin_file))
		{
			$AppClass = ucfirst($entity->name).'Plugin';
			if (!$this->classes[$AppClass])
			{
				require $plugin_file;
			}
			$this->classes[$AppClass] = true;
			$loaded = true;
		}
		*/

		if ($loaded == true)
		{
			try
			{
				$App = new $AppClass($entity, $dr);
				if (method_exists($App, $function_name))
				{
					return $App;
					//return $App->$function_name();
				}
				else 
					return null;
			}
			catch (Exception $e)
			{
				return "Plugin exec error - ".$e->getMessage();
			}
		}
		else return null;
		//throw new Exception("Plugin for {$entity->name}::{$function_name}() not found");
	}


	public function dataset_plugin($ds, $function_name)
	{
		$entity = $ds->entitymeta;
		$plugin_file = PLUGINS_DIR . 'dataset/' . $entity->name . '.php';
		if (file_exists($plugin_file))
		{
			$AppClass = ucfirst($entity->name).'SetPlugin';
			if (!$this->classes[$AppClass])
			{
				require $plugin_file;
			}
			$this->classes[$AppClass] = true;
			$loaded = true;
		}
		if ($loaded == true)
		{
			try
			{
				$App = new $AppClass($entity, $ds);
				return $App->$function_name();
			}
			catch (Exception $e)
			{
				return "Exec error";
			}
		}
		else throw new Exception("Plugin for {$entity->name}::{$function_name}() not found");
	}

	private function init() {}

}
?>