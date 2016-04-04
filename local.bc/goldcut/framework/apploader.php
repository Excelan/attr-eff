<?php
/*
if non legacy

*/
class AppLoader
{
	private function __construct() {}

	public static function get($R, $uriar)
	{
		$name = $R['app'];
		$legacy = $R['legacy'];
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$key = 'Control';
		}
		else if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$key = 'App';
		}
		else
		{
			Log::error('Unallowed http method used - '.$_SERVER['REQUEST_METHOD'], 'http');
		}
		if ($legacy == 'no')
			return self::appLoad($name, $key, $R['type'], $uriar);
		else
			return self::appLoadLegacy($name, $key, $R, $uriar);
	}
	
	private static function appLoadLegacy($name, $key, $R, $uriar)
	{
		$AppClass = $name.$key;
		$sysapp = SYSTEM_APPS_DIR . $name . '/' . $name . $key .'.php';
		$app = APPS_DIR . $name . '/' . $name . $key .'.php';
		if (file_exists($app)) // local app first
		{
			require $app;
		}
		elseif (file_exists($sysapp)) // system app
		{
			require $sysapp;
			define('IS_SYSTEM_APP',true);
		}
		else // app not found 
		{
			throw new Exception("$AppClass application not found in system or local apps");
		}
		$App = new $AppClass($R, $uriar);
		return $App;
	}
	
	private static function appLoad($name, $key, $type, $uriar)
	{
		if ($key == 'App') $key = '';
		// println($type);
		if ($type == 'modules')
		{
			$method = 'Request';
			$ClassR = ucfirst($uriar[1]).$method;
			$AppClass = $name.$key;
			require APPS_DIR . $name . '/' . $uriar[1] . '/' . $name . $ClassR . $key .'.php';
			// $App = new $AppClass($R, $uriar);
			// return $App;
		}
		else
			throw new Exception("NIY");
	}
}
?>