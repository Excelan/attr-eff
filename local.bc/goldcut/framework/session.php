<?php
/**
bool setcookie ( string $name [, string $value [, int $expire = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httponly = false ]]]]]] )
*/

define('COOKIEEXPIRE', 3600*24*30*3);

class Session
{

	private static $instance;
	private static $SID;
	public $vars = array();

	public static function manager()
	{
		if (!self::$instance) { self::$instance = new Session(); self::$instance->start(); } return self::$instance;
	}

	private function start()
	{
	}

	public static function ID()
	{
		return false;
	}

	public static function put($k,$v, $dontserialize=false)
	{
        Log::info("$k, $v", 'register');
		self::manager();
        if ($dontserialize !== true)
		    $value = serialize($v);
        else
            $value = (string) $v;
		setcookie($k, $value, time() + COOKIEEXPIRE, '/');
		self::$instance->vars[$k] = $v;
	}

	public static function get($k, $dontserialize=false)
	{
		self::manager();
		if ($v = self::$instance->vars[$k]) return $v;
		if ($_COOKIE[$k])
        {
            if ($dontserialize !== true)
                return unserialize($_COOKIE[$k]);
            else
                return $_COOKIE[$k];

        }
		return false;
	}

	public static function pop($k,$dontserialize=false)
	{
		self::manager();
        $v = self::get($k,$dontserialize);
		if ($v) setcookie($k, null, time() - 1, '/' );
		return $v;
	}

	public static function destroy()
	{
		self::manager();
        self::$instance->vars = array();
		foreach($_COOKIE as $k => $v)
		{
			setcookie($k, '', time() - 1, '/' );
		}
	}

	public static function debug()
	{
		print "<pre>";
		print_r($_COOKIE);
		print "</pre>";
	}

}

?>