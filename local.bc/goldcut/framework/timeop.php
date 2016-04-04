<?php 
/**
// Google "bitbucket timezone detect" and use it to set a "local_timezone" cookie that you can read from PHP and set via date_default_timezone_set()
// Начиная с версии PHP 5.1 метка начала запроса доступна в поле $_SERVER['REQUEST_TIME'].
var_dump(time(), tzdelta(), date('O Z e'));
print_r(localtime());
$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
$nextyear  = mktime(0, 0, 0, date("m"),   date("d"),   date("Y")+1);
*/
class TimeOp
{
	/**
	today, comparewith
	if comparewith in future - result is negative
	if comparewith in past - result is positive
	*/
	private static $real = true;
	private static $now = null;
	private static $frozen = false;

	public static function now()
	{
		if (self::$now == 0)	
			return time();
		else
			return self::emit();
	}
		
	private static function emit()
	{
		if (self::$frozen == false)
		{
			$delta = self::$now - time();
			// printlnd("emit ".$delta);
			self::$now = time() + abs($delta);
		}
		return self::$now;
	}
	
	public static function takeControl()
	{
		self::$real = false;
		self::base(time());
	}
	
	public static function base($change) // time now is $changed now
	{
		self::$now = $change;
	}
		
	public static function skip($s)
	{
		self::isControlTaken();
		self::$now = self::now() + $s;
		return self::$now;
	}
	
	public static function skipDay()
	{
		self::isControlTaken();
		self::$now = strtotime('+1 day', self::$now);
		return self::$now;
	}
	
	public static function daysBefore($targetdate, $days=1)
	{
		self::isControlTaken();
		$timemove = strtotime("-{$days} day", date_parse($targetdate));
		if (self::$now > $timemove) throw new Exception('Wonderland detected. Move time only forward');
		self::$now = $timemove;
		return self::$now;
	}
	
	public static function todayNowIs($todaydate)
	{
		self::isControlTaken();
		self::$now = strtotime('+1 second', date_parse($targetdate));
		return self::$now;
	}
	
	public static function skipWeek()
	{
		self::isControlTaken();
		self::$now = strtotime('+1 week', self::$now);
		return self::$now;
	}
	
	public static function skipMonth()
	{
		self::isControlTaken();
		self::$now = strtotime('+1 month', self::$now);
		return self::$now;
	}
	
	public static function nextMonday()
	{
		self::isControlTaken();
		self::$now = strtotime('next Monday', self::$now);
		return self::$now;
	}
	
	public static function nextWeekend()
	{
		self::isControlTaken();
		self::$now = strtotime('next Saturday', self::$now);
		return self::$now;
	}
	
	private static function isControlTaken()
	{
		if (self::$real == true) throw new Exception('takeControl() on time before play');
	}
			
	/** Back is broken login. We can move start point of time with base(startpoint) and play time forward but not back
	public static function back($s)
	*/
	
	public static function freeze()
	{
		self::$frozen = true;
	}
	
	public static function liquify()
	{
		self::$frozen = false;
	}
	
	public static function date()
	{
		return date('Y-m-d', self::now());
	}
	
	public static function time()
	{
		return date('H:i:s', self::now());
	}
	
	public static function datetime()
	{
		return date('Y-m-d H:i:s', self::now());
	}
	
	public static function daysInterval($date1, $date2) // Y-m-d format
	{
		$date1 = date_parse($date1);
		$date2 = date_parse($date2);
		$diff = ((gmmktime(0, 0, 0, $date1['month'], $date1['day'], $date1['year']) - gmmktime(0, 0, 0, $date2['month'], $date2['day'], $date2['year']))/3600/24);
		return $diff;
	}
	
	/**
	timezone delta in seconds with UTC 
	*/
	static function tzdelta ( $iTime = 0 )
	{
		if ( 0 == $iTime ) { $iTime = time(); }
		$ar = localtime ( $iTime );
		$ar[5] += 1900; $ar[4]++;
		$iTztime = gmmktime ( $ar[2], $ar[1], $ar[0], $ar[4], $ar[3], $ar[5], $ar[8] );
		return ( $iTztime - $iTime );
	}
}

?>