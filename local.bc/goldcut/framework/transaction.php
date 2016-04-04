<?php 

class Transaction 
{
	private static $opened = false;
	
	public static function open()
	{
		// SQL EntityStore::begin();
		// buffer MQ not in transaction
		self::$opened = true;
	}
	
	public static function commit()
	{
		// Aspect
		// SQL EntityStore::commit();
		// send buffered to MQ
		self::$opened = false;
	}
	
	public static function cancel()
	{
		self::$opened = false;
	}
	
	public static function is_opened()
	{
		return self::$opened;
	}
}
?>