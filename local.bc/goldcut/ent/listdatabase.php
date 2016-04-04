<?php
/**
sadd
sinter, sunion, sdiff + store results
SCARD - count members or 0
SRANDMEMBER
SMOVE
spop - get last
smembers

cascade struct?
// variators
list-variator-varid-variatedentity => list-color-1red-products
// related
list-tag-2-ad
// lists
list-user-1-following
list-user-1-friends [(2),3,4]  DO+ UPDATE ON OTHER SIDE UPDATE (MUTUAL LIST)
list-user-1-messagesinbox
*/

class ListDatabase
{
	
	private $connection;
	private $nsprefix;
	private static $instance;
	private function __construct() {}
	private static $enabled = ENABLE_LISTDB;
		
	private function init()
	{
		Utils::startTimer('redisconnect');
		
		require BASE_DIR.'/lib/predis/lib/Predis/Autoloader.php';
		Predis\Autoloader::register();
		$this->connection = new Predis\Client();
		if (TEST_ENV === true) $ENV = 'TEST';
		else $ENV = ENV;
		if ($DBID = $GLOBALS['CONFIG'][$ENV]['DB']['REDISDBID'])
		{
			$this->connection->select($DBID);
		}
		else
		{
			die('Please set redisdb database number for ENV '.$ENV);
		}
		//$this->nsprefix = HOST.'-'.substr(ENV,0,3).'-';
		$this->nsprefix = '';
		
		$ctime = Utils::reportTimer('redisconnect');
		Log::info("@ Connected DBID:[$DBID] CTIME: [{$ctime['time']}]",'list');
	}

	public static function manager() 
	{
		if (!self::$instance) 
		{ 
			self::$instance = new ListDatabase();
			try {
				self::$instance->init();	
			}
			catch (Exception $e)
			{
				throw new Exception('Cant connect to Redis server');
			}
		}
		return self::$instance;
	}
		
	public static function is_enabled()
	{
		if (self::$enabled === true)
			return true;
		else
			return false;
	}
	
	public static function is_disabled()
	{
		if (self::$enabled !== true)
			return true;
		else
			return false;
	}
	
	private function namespace_key($key, $multiple = false)
	{
		if ($multiple) return $key; // without prefix 
		if (is_array($key)) $key = 'list-'.join('-',$key);
		return $this->nsprefix . $key;
	}
	
	
	public static function vectorAddLeft($key, $value) 
	{
		if (self::is_disabled()) return null;
		if (DEBUG_LISTDB === true) dprintln(json_encode($key)." +v $value",2,TERM_GREEN);		
		if (isUUID($value)) $value = $value->toInt();
		$key = self::manager()->namespace_key($key);
		Log::info("+v $key / $value", 'listdb');	
		$c = self::manager()->connection;
		$res = $c->lpush($key, $value);
		return $res;
	}
	public static function vectorAddRight($key, $value) 
	{
		if (self::is_disabled()) return null;
		if (DEBUG_LISTDB === true) dprintln(json_encode($key)." v+ $value",2,TERM_GREEN);		
		if (isUUID($value)) $value = $value->toInt();
		$key = self::manager()->namespace_key($key);
		Log::info("v+ $key / $value", 'listdb');	
		$c = self::manager()->connection;
		$res = $c->rpush($key, $value);
		return $res;
	}
	
	public static function vectorRange($key, $from, $to) 
	{
		if (self::is_disabled()) return null;
		if (DEBUG_LISTDB === true) dprintln(json_encode($key)." [v..] $value",2,TERM_GREEN);		
		if (isUUID($value)) $value = $value->toInt();
		$key = self::manager()->namespace_key($key);
		Log::info("[v..V] $key / $value", 'listdb');	
		$c = self::manager()->connection;
		$res = $c->lrange($key, $from, $to);
		return $res;
	}

	
	
	// its not a SetExists!
	static function keyExists($key)
	{
		if (self::is_disabled()) return null;
		$key = self::manager()->namespace_key($key);
		$c = self::manager()->connection;
		return $c->exists($key);		
	}
	
	// key is string or array(). value is int or int[]
	public static function setAdd($key, $value) 
	{
		if (self::is_disabled()) return null;
		if (DEBUG_LISTDB === true) dprintln(json_encode($key)." >> $value",2,TERM_GREEN);		
		if (isUUID($value)) $value = $value->toInt();
		$key = self::manager()->namespace_key($key);
		Log::info(">> $key / $value", 'listdb');	
		$c = self::manager()->connection;
		$res = $c->sadd($key, $value);
		return $res;
	}

	public static function setRemove($key, $value) 
	{
		if (self::is_disabled()) return null;
		if (DEBUG_LISTDB === true) dprintln(json_encode($key)." -- $value",2,TERM_RED);
		if (isUUID($value)) $value = $value->toInt();
		$key = self::manager()->namespace_key($key);
		Log::info("-- $key / $value", 'listdb');		
		$c = self::manager()->connection;
		$c->srem($key, $value);
		return true;
	}

	public static function setExists($key, $value) 
	{
		if (DEBUG_LISTDB === true) dprintln(json_encode($key)." ? $value",2,TERM_GRAY);
		if (self::is_disabled()) return null;
		$key = self::manager()->namespace_key($key);
		Log::debug("?? $key / $value", 'listdb');
		$c = self::manager()->connection;
		//Log::error(json_encode($c->smembers($key)),'listdb');
		// TODO was incostintent after sql dup fails - db has in list, redis - not
		$ismember = $c->sismember($key, $value);
		if ($ismember) return 1;
		return 0;
	}
	
	public static function setAll($key)
	{
		if (self::is_disabled()) return null;
		$key = self::manager()->namespace_key($key);
		//dprintln('<< '.json_encode($key)." *",2,TERM_YELLOW);		
		$c = self::manager()->connection;
		return $c->smembers($key);
	}
	
	public static function keysSearch($mask)
	{
		if (self::is_disabled()) return null;
		$mask = self::manager()->namespace_key($mask);
		$c = self::manager()->connection;
		return $c->keys($mask);
	}
	
	// TODO $storeAsNewSet=false
	public static function setsIntersection(array $sets, $storeAsNewSet=false)
	{
		if (self::is_disabled()) return null;
		foreach($sets as &$cn) $cn = self::manager()->namespace_key($cn);
		$c = self::manager()->connection;
		$inter = $c->sinter($sets);
		Log::debug("INTER ".json_encode($sets)." / < ".json_encode($inter), 'listdb');
		return $inter;
	}
	
	public static function setsUnion(array $sets, $storeAsNewSet=false)
	{
		if (self::is_disabled()) return null;
		foreach($sets as &$cn) $cn = self::manager()->namespace_key($cn);
		Log::debug("UNION ".json_encode($sets)." / <", 'listdb');				
		$c = self::manager()->connection;
		return $c->sunion($sets);
	}	

	public static function setsDifference(array $sets, $storeAsNewSet=false)
	{
		if (self::is_disabled()) return null;
		foreach($sets as &$cn) $cn = self::manager()->namespace_key($cn);
		$c = self::manager()->connection;
		return $c->sdiff($sets);
	}
		
	public static function keyDel($key, $multiple = false)
	{
		if (self::is_disabled()) return null;
		if (!$key) return false;
		//dprintln(json_encode($key)." --",2,TERM_RED);		
		$key = self::manager()->namespace_key($key, $multiple);
		//dprintln(json_encode($key)." --",2,TERM_GREEN);		
		Log::info("-- ".json_encode($key)." / **", 'listdb');		
		$c = self::manager()->connection;
		return $c->del($key);
	}
	
	public static function allKeysDel()
	{
		if (self::is_disabled()) return null;
		Log::info("-- ** / **", 'listdb');		
		ListDatabase::keyDel(ListDatabase::keysSearch('*'), true);
		// or flushdb
	}

}

?>