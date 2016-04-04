<?php 

/**
Navigation::add(url, title)
::path() - build html
*/
	
class Navigation
{
	private static $list = array('/'=>'Главная');
	public static $realm;
	public static $current;
	private static $instance;
	private function __construct() {}
	/**
	public static function manager() 
	{
		if (!self::$instance)
		{
			self::$instance = new Navigation(); 
		} 
		return self::$instance;
	}
	*/
	public static function push($link, $title) 
	{
		//array_push(self::$list, )
		self::$list[$link] = $title;
	}

	static function path() 
	{
		return self::$list;
	}
	
}
?>