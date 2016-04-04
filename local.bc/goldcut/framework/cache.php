<?php

/**
USAGE

if ($res = Cache::get($key))
{
	$res = ($res); // unserialize auto
}
else
{
	$res = rand(10);
	$cachedOk = Cache::put($key, ($res)); // serialize auto
}

TODO !!! NEED ->SYSTEM UPDATE FLAG TO NOT CHANGE F:UPDATED AND DONT TOUCH CACHE ON COUNT_ INC
TODO cache by _parent, uri (or another KEY)
TODO cache check in (SQL IN requests) if (1,2,cached,4,cached,cached,7)
TODO cached views for paged listings cache 

TODO !! use json to serialize
ob + readfile is fastest for fileread
secure /tmp cache folder in webserver (db passwords are in cache!)
*/

class Cache 
{

	static $enabled = ENABLE_CACHE;
	static $link = null;

	private static function init()
	{
		if (extension_loaded('memcache') && SKIP_MEMCACHE !== true)
		{
			//Log::debug("-- MEMCACHED CONNECT", 'cache');
			self::$link = memcache_connect('localhost', 11211);
			//memcache_close(self::$link);
		}
		else
			self::$link = true;
	}

	public static function enable()
	{
		self::$enabled = true;
	}
	
	public static function disable()
	{
		self::$enabled = false;
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
	
	private static function namespace_key($key)
	{
		return HOST.'/'.ENV.'/'.$key;
	}

	static function exists($key)
	{
		/**
		TODO
		*/
		self::backend();
	}
	
	public static function put($key, $value, $ttl=null)
	{
		if (ENABLE_CACHE !== true) return null;
        if (!$key) throw new Exception("BLANK CACHE KEY");
		self::backend();
		if (ENABLE_CACHE_LOG === true) Log::info(">> $key $ttl (".self::backend().")", 'cache');
		$value = serialize($value);
		$key = self::namespace_key($key);
		
		if (extension_loaded('xcache') && SKIP_XCACHE !== true)
		{
			if ($ttl)
				$ok = xcache_set($key, $value, $ttl);
			else
				$ok = xcache_set($key, $value);
			if (!$ok) throw new Exception('Set xcache.var_size > 0M in php.ini');
			return $ok;
		}	
		else if (extension_loaded('apc') && SKIP_APC !== true)
		{
			return apc_store($key, $value, $ttl);
		}
		else if (extension_loaded('memcache') && SKIP_MEMCACHE !== true)
		{
			if ($memcache_obj = self::$link)
			{
				$ok = memcache_set($memcache_obj, $key, $value, 0, $ttl); // MEMCACHE_COMPRESSED, TTL seconds
			}
			return $ok;
		}
		else // filecache
		{
			// if no mem caches
			$filename = BASE_DIR.'/tmp/'.md5($key).'.cache';
			$file = fopen($filename, 'w');
			if (flock($file, LOCK_EX)) 
			{
				fwrite($file, $value);
				flock($file, LOCK_UN); // release the lock
				$ok = true;
			} 
			else
			{
			   throw new Exception("Couldn't get the WRITE EX lock for $filename file!");
			}
			fclose($file);
			return $ok;
		}
	}
	
	public static function get($key, $ttl=false) // TTL in seconds
	{
		if (ENABLE_CACHE !== true) return null;

		self::backend();

		$data = null;
		$origkey = $key;
		$key = self::namespace_key($key);
		
		if (extension_loaded('xcache') && SKIP_XCACHE !== true)
		{
			if (xcache_isset($key))
			{
				$data = xcache_get($key);
				$data = unserialize($data);
				//if (DEBUG_CACHE === true) printlnd($data);
				//return $data;
			}
//			else
//			  return null;
		}
		else if (extension_loaded('apc') && SKIP_APV !== true)
		{
			return unserialize(apc_fetch($key));
		}
		else if (extension_loaded('memcache') && SKIP_MEMCACHE !== true)
		{
			if ($memcache_obj = self::$link)
			{
				$data = unserialize(memcache_get($memcache_obj, $key));
				//return $data;
			}
			//else return null;
		}
		else
		{
            $filename = BASE_DIR.'/tmp/'.md5($key).'.cache';
			if (file_exists($filename))
			{
				if ($ttl) $df = time() - filemtime($filename);
                if ($ttl && $df > $ttl) {
                    unlink($filename);
                    //return null;
                }
				else
				{
					$file = fopen($filename, 'r');
					if (flock($file, LOCK_SH))
					{
						$fs = filesize($filename);
						$data = fread($file, $fs);
						//$data = file_get_contents($file); // slow!
						flock($file, LOCK_UN);
					}
					else
					{
						throw new Exception("Couldn't get the READ SHARED lock for $filename file!");
					}
					fclose($file);
					$data =  unserialize($data);
				}
			}
			else
			{
				//Log::info(" <NOFILE $key (".self::backend().")", 'cache');
				//return null;
			}
		}
		$get = '<';
		if ($data) $get .= '<';
		if (ENABLE_CACHE_LOG === true) Log::info(" $get $origkey $ttl (".self::backend().")", 'cache');
		return $data;
	}
	
	
	public static function flush()
	{
		self::backend();

		if (extension_loaded('xcache') && SKIP_XCACHE !== true)
		{
			if (NOCLEARCACHE !== true)
			{
				Log::debug('XCache cleared', 'cache');
				xcache_clear_cache();
			}
		}
		else if (extension_loaded('memcache') && SKIP_MEMCACHE !== true)
		{
			Log::debug('-- MEMCACHE FLUSHED', 'cache');
			memcache_flush(self::$link);
		}
		// TODO rm files in tmp/*.cache
	}

	public static function clear($okey)
	{
		if (ENABLE_CACHE !== true) return null;

		self::backend();

		if (ENABLE_CACHE_LOG === true) Log::info("0 $okey", 'cache');
		
		$key = self::namespace_key($okey);
		
		if (extension_loaded('xcache') && SKIP_XCACHE !== true)
		{
			return xcache_unset($key);
		}
		
		else if (extension_loaded('apc') && SKIP_APC !== true)
		{
			return apc_delete($key);
		}
		
		else if (extension_loaded('memcache') && SKIP_MEMCACHE !== true)
		{
			if ($memcache_obj = self::$link)
			{
				memcache_delete($memcache_obj, $key);
				return true;
			}
			else return null;
		}
		// else
		// if no mem caches
		$filename = BASE_DIR.'/tmp/'.md5($key).'.cache';
		if (file_exists($filename))
		{
			if (ENABLE_CACHE_LOG === true) Log::debug("0 F $okey", 'cache');
			unlink($filename);
		}
		else
		{
			if (ENABLE_CACHE_LOG === true) Log::debug("0 F ?404 $okey", 'cache');
			return false;
		}
	
	}
	
	public static function backend()
	{
		if (ENABLE_CACHE !== true) return 'CACHE DISABLED';

		if (!self::$link) self::init();
		
		if (extension_loaded('xcache') && SKIP_XCACHE !== true)
		{
			return 'XCACHE';
		}
		else if (extension_loaded('apc') && SKIP_APC !== true)
		{
			return 'APC';
		}
		else if (extension_loaded('memcache') && SKIP_MEMCACHE !== true)
		{
			return 'MEMCACHE';
		}
        else
		    return 'FILECACHE';
	}

}

?>