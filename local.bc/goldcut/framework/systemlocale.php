<?php

define('en','en');
define('ru','ru');
define('es','es');
define('it','it');
define('cn','cn');
define('de','de');
define('pt','pt');

class SystemLocale
{
	public static $DEFAULT_LANG = DEFAULT_LANG;
    private static $PUBLISHING_LANGS;
	public static $REQUEST_LANG;
    public static $ALL_LANGS;
    public static $URILANG;
	private static $instance;

	static function set($lang)
	{
		self::$DEFAULT_LANG = $lang;
	}

    static function setall(array $langs)
    {
        self::$ALL_LANGS = $langs;
    }

    static function setpublishinglangs(array $langs)
    {
        self::$PUBLISHING_LANGS = $langs;
    }

	static function default_lang()
	{
		return self::$DEFAULT_LANG;
	}

	static function request_lang()
	{
		if (strlen(self::$REQUEST_LANG))
			return self::$REQUEST_LANG;
		else
			return self::$DEFAULT_LANG;
	}

    static function translate_langs()
    {
        $langs = array();
        foreach (self::$ALL_LANGS as $lang)
        {
            if ($lang == self::$DEFAULT_LANG) continue;
            array_push($langs, $lang);
        }
        return $langs;
    }

    static function publishing_langs()
    {
        return self::$PUBLISHING_LANGS;
    }

    // TODO move out
	static function localMonthName($m, $p=0)
	{
		$month = array();
		if (!$p)
		{
			$month[1]='января';
			$month[2]='февраля';
			$month[3]='марта';
			$month[4]='апреля';
			$month[5]='мая';
			$month[6]='июня';
			$month[7]='июля';
			$month[8]='августа';
			$month[9]='сентября';
			$month[10]='октября';
			$month[11]='ноября';
			$month[12]='декабря';
		}
		else
		{
			$month[1]='январь';
			$month[2]='февраль';
			$month[3]='март';
			$month[4]='апрель';
			$month[5]='май';
			$month[6]='июнь';
			$month[7]='июль';
			$month[8]='август';
			$month[9]='сентябрь';
			$month[10]='октябрь';
			$month[11]='ноябрь';
			$month[12]='декабрь';
		}
		return $month[$m];
	}
	
}

if (!class_exists('Locale',false)) 
{
	class Locale extends SystemLocale {}
}

?>