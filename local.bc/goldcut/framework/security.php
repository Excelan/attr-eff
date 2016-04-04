<?php

/**
TODO ! HTTPOnly cookie flag
*/

/**
web crawlers in white list by ip, user agent (when in red only by known ips)
sensitive regions - register, any email related, cpu heavy (text search, extended filter)
per ip activity counter for RO/WRITE/HEAVYRO/EMAIL/REGISTER ops
block with 503 on hour rate exceed
store - php xcache, node.js,   redis, mongo
every hour clear all counters
red zone - when total ip > peak hour maximum for site class (small, medium, large)
red zone for RO/W different. W can be in red, R in green
in red zone
	check country & block non primary countries for New ips
KEY - ip/RO++
keys++ if new ip
*/

// TODO https://www.owasp.org/index.php/DOM_based_XSS_Prevention_Cheat_Sheet

class Security
{

	static $htmlPurifier;

	// TODO if righturl*(%*# != filtered return - throw Redirect to /page/righturl
	public static function filterString09AZaz_dahed($uri)
	{
		$uri = filter_var($uri, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HI);
		///$uri = preg_replace('/[^(\x20-\x7F)]*/','', $uri); // by a-z codes
		if (function_exists('iconv'))
		{
		$uri = iconv("UTF-8","UTF-8//IGNORE", $uri); // filter non utf8
		$uri = iconv("UTF-8","ISO-8859-1//IGNORE", $uri); // filter non ISO-8859-1
		$uri = iconv("ISO-8859-1", "UTF-8", $uri); // str back to utf8
		}
		$uri = preg_replace('/[^0-9A-Za-z-_]*/','', $uri);
		return $uri;
	}

	public static function filterStringURI($uri)
	{
		$uri = filter_var($uri, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HI);
		$uri = iconv("UTF-8","UTF-8//IGNORE", $uri);
		$uri = iconv("UTF-8","ISO-8859-1//IGNORE", $uri);
		$uri = iconv("ISO-8859-1", "UTF-8", $uri);
		$uri = preg_replace('/[^0-9A-Za-z-%~_\/]*/','', $uri); // no dots(.) in uri, ?no=params, no #hash
		return $uri;
	}


	// relative pathes, without /
	/**
	проеряется допустимость на уровне fs. но при выводе имени в браузер нужно еще экранировать (' <> ")
	могут быть / - тк путь полныйне должно бы
	*/
	static function checkfolder($f, $maxcomponents = 20) // bindfolder - разрешенное имя первой папки в пути f
	{
		/**
		compare path with realpath(name) if base differ - forged
		*/

		//if ($f == null or $f == '')
		//	throw new Exception('Пацаны уже выехали из [null]');
		if (strlen($f) > 255)
			throw new Exception('Пацаны уже выехали и очень злы '.strlen($f));
		if ($f == '/')
			return true;
		else
			$comp = explode('/',$f);

		if (count($comp) > $maxcomponents)
			throw new Exception("Пацаны уже выехали и очень злы. Попытка дать путь с глубиной > $maxcomponents. ".count($comp));

		if (!is_array($comp))
			throw new Exception('Пацаны уже выехали [not array]');
		if (count($comp) > 10)
			throw new Exception('Пацаны уже выехали [>10]');

		// ДЛЯ КАЖДОГО КОМПОНЕНТА ПУТИ
		for($i=0; $i < count($comp); $i++)
		{
			if (!$comp[$i]) continue;
			if ($comp[$i] == '..') throw new Exception('Пацаны уже выехали [..]'); // /path/../../home
			if ($comp[$i] == '.') throw new Exception('Пацаны уже выехали на [.]'); // path/./path
			if (substr($comp[$i], -1) == '.') throw new Exception('Пацаны уже выехали [.$]'); // точка в конце
			if (substr($comp[$i], -1) == ' ') throw new Exception('Пацаны уже выехали [ $]'); // пробел в конце (ntfs problem)
			if (substr($comp[$i], 0,1) == '.') throw new Exception('Пацаны уже выехали [^.]'); // точка в начале
			$unallowed = array('/', '?', '<', '>', '\\', ':', '*', '|', '"', '\x0A', '\xFF'); // ntfs запрещенные символы
			foreach($unallowed as $c)
			{
				$pos = strpos($comp[$i], $c);
				if ($pos !== false) throw new Exception('Пацаны не рекоменуют символ '.$c);
			}
		}
		return true;
	}

	/**
	проеряется допустимость на уровне fs. но при выводе имени в браузер нужно еще экранировать (' <> ")
	в имени не могут быть / и .. - имя файла не должно быть разделено на директорию и файл (а так же с выходом уровнем выше)
	*/
	static function checkuserfile($f)
	{
		if (strlen($f) > 255) throw new Exception('Пацаны уже выехали и очень злы '.strlen($f));
		if ($f===null) throw new Exception('Пацаны уже выехали NULL');
		if ($f==='') throw new Exception('Пацаны уже выехали ""');
		if ($f===' ') throw new Exception('Пацаны уже выехали " "');
		if ($f=='\\') throw new Exception('Пацаны уже выехали \\');
		if ($f=='/') throw new Exception('Пацаны уже выехали /');
		if ($f=='.') throw new Exception('Пацаны уже выехали .');
		if ($f=='..') throw new Exception('Пацаны уже выехали ..');
		if (substr($f, -1) == '.') throw new Exception('Пацаны уже выехали [.$]');
		if (substr($f, 0,1) == '.') throw new Exception('Пацаны уже выехали [^.]');
		if (substr($f, -1) == ' ') throw new Exception('Пацаны уже выехали [ $]');
		if (strpos($f, '../') !== false) throw new Exception('Пацаны уже поднимаются [../]');
		if (strstr($f, '/')) throw new Exception('Пацаны /уже выехали');
		$unallowed = array('/', '?', '<', '>', '\\', ':', '*', '|', '"', '\x0A', '\xFF');
		foreach($unallowed as $c)
		{
			$pos = strpos($f, $c);
			if ($pos !== false) throw new Exception('Пацаны уже выехали '.$c);
		}
		return true;
	}

	/**
	Illegal Characters on Various Operating Systems
	Windows using NTFS:
	/ ? < > \ : * | " and any character you can type with the Ctrl key, FAT: caret ^
	max len 256/32767 chars
	Placing a space at the end of the name. Placing a period at the end of the name
	Mac OS X
	colon :
	File and folder names are not permitted to begin with a dot “.”
	max len 255 chars
	*/

	/**
	escapeshellcmd экранируются #&;|*?~<>^()[]{}$\, \x0A и \xFF. Символы ' и " экранируются только в том случае, если они встречаются не попарно. В Windows все эти символы и % заменяются пробелом
	*/

	/**
	$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U");
	$onlyconsonants = str_replace($vowels, "", "Hello World of PHP");
	*/

	static function safeFileName($name)
	{
		$name = escapeshellcmd($name);
	}


	/**
	1. Use htmlspecialchars() to filter text input values for html input tags.  i.e.,
	echo '<input name=userdata type=text value="'.htmlspecialchars($data).'" />';
	2. Use htmlentities() to filter the same data values for most other kinds of html tags, i.e.,
	echo '<p>'.htmlentities($data).'</p>';
	3. Use your database escape string function to filter the data for database updates & insertions, for instance, using postgresql,
	pg_query($connection,"UPDATE datatable SET datavalue='".pg_escape_string($data)."'");
	*/

	/**
	TODO add 'az' only, А-Я, А-йо-Я, 1-0,
	*/
	static function mysql_escape($inp, $dblink=null)
	{
		if (!empty($inp) && is_string($inp))
		{
            if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
                $x = pg_escape_string($inp);
            else
			    $x = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
			//$x = str_replace(array('\\', "\0", "'", '"', "\x1a"), array('\\\\', '\\0', "\\'", '\\"', '\\Z'), $inp);
			return $x;
		}
		else
		{
			return $inp;
		}
	}

    static function generateSimplePassword()
    {
        return mt_rand(10000,99999);
    }

	static function generatePassword($len=7)
	{
		//return shortcode();
		return self::easy_pass();
	}

	static function easy_pass()
	{
		$pw = '';
		$c  = 'bcdfghjklmnprstvwz';
		$v  = 'aeiou';
		$a  = $c.$v;
		for($i = 0; $i < 2; $i++)
		{
			$pw .= $c[mt_rand(0, strlen($c)-1)];
			$pw .= $v[mt_rand(0, strlen($v)-1)];
			$pw .= $a[mt_rand(0, strlen($a)-1)];
		}
		$pw .= mt_rand(10,99);
		return $pw;
	}

	static function genLoginHashString()
	{
		return sha1(mt_rand(1000, 2147483647));
	}
	static function genLoginHashNumber()
	{
		$uuid = new UUID();
		return $uuid->toInt();
	}

	static function html_purify($html, $options)
	{
		$optionskey = md5(json_encode($options));
		if (self::$htmlPurifier[$optionskey])
		{
			$pure = self::$htmlPurifier[$optionskey];
		}
		else
		{
			include_once(BASE_DIR.'/lib/htmlpurifier/library/HTMLPurifier.auto.php');
			$config = HTMLPurifier_Config::createDefault();

			$config->set('HTML.TidyLevel', 'heavy');
			$config->set('Core.Encoding', 'utf-8');
			$config->set('HTML.Doctype', "XHTML 1.0 Strict");

			//$config->set('Cache.DefinitionImpl', null);
			$cachePath = BASE_DIR.'/tmp/cache/htmlpurifier';
			if (!is_dir($cachePath)) mkdir($cachePath, octdec(FS_MODE_DIR), true);
			$cachePath = realpath($cachePath);
			$config->set('Cache.SerializerPath', $cachePath);

			// Specify elements and attributes that are allowed using: element1[attr1|attr2],element2.... For example, if you would like to only allow paragraphs and links, specify a[href],p. You can specify attributes that apply to all elements using an asterisk, e.g. *[lang
			// The syntax is a subset of TinyMCE's valid_elements whitelist: directly copy-pasting it here will probably result in broken whitelists
			// 'HTML.Allowed' => 'p,br,b,i,b,strong,em,img[alt|src],a[href],table[summary],tbody,th[abbr],tr,td[abbr]',dl, dt, dd
			if ($options['HTML.Allowed'])
				$HTMLAllowed = $options['HTML.Allowed'];
			else
				$HTMLAllowed = 'p,br,i,b,strong,em';
			$config->set('HTML.Allowed', $HTMLAllowed);

			// This is the logical inverse of %HTML.AllowedElements, and it will override that directive, or any other directive
			if ($options['HTML.ForbiddenElements'])
			{
				$HTMLForbiddenElements = $options['HTML.ForbiddenElements'];
				$config->set('HTML.ForbiddenElements', $HTMLForbiddenElements);
			}

			if ($options['HTML.Allowed'] && $options['HTML.ForbiddenElements']) throw new Exception("Use HTML.Allowed OR HTML.ForbiddenElements");

			$config->set('URI.AllowedSchemes', array('http' => true,'https' => true));

			if ($options['HTML.Nofollow'])
			{
				$config->set('HTML.Nofollow', true);
				//var_dump($options['HTML.Nofollow']);
			}
			if ($options['Filter.YouTube']) $config->set('Filter.YouTube', true);
			if ($options['AutoFormat.AutoParagraph']) $config->set('AutoFormat.AutoParagraph', true);
			if ($options['AutoFormat.RemoveEmpty']) $config->set('AutoFormat.RemoveEmpty', true);
			if ($options['AutoFormat.RemoveEmpty.RemoveNbsp']) $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
			$pure = new HTMLPurifier($config);
			self::$htmlPurifier[$optionskey] = $pure; // instance
		}

		$clean = $pure->purify($html);
		if ($options['postclean']) $clean = self::postClean($clean);
		return $clean;
	}

	static function htmlEncode($s) {
		$s = htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
		$s = preg_replace('/&#0*39;/', '&apos;', $s); // &#039;
		return $s;
	}

	static function safeStr($s) {
		return self::htmlEncode(strip_tags($s));
	}

	static function safeHtml($s, $options) {
		return self::html_purify($s, $options);
	}

	static function postClean($clean)
	{
		//$clean = str_replace("\r\n\r\n","\r\n",$clean);
		$clean = str_replace("\r\n\r\n\r\n\r\n\r\n\r\n","\r\n\r\n\r\n", $clean); // 4 > 2
		$clean = str_replace("\r\n\r\n\r\n","\r\n\r\n", $clean); // 3> 2
		$clean = str_replace("\n\n\n\n","\n\n", $clean); // 4 > 2
		$clean = str_replace("\n\n\n","\n\n", $clean); // 3 > 2
		$clean = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n\n", $clean);

		$clean = str_replace("<i>","<em>", $clean);
		$clean = str_replace("</i>","<em>", $clean);
		$clean = str_replace("<b>","<strong>", $clean);
		$clean = str_replace("</b>","</strong>", $clean);

		$clean = str_replace("«</strong>","</strong>«", $clean);
		$clean = str_replace("</strong><strong>","", $clean);
		$clean = str_replace("<strong><br />","<br /><strong>", $clean);
		$clean = str_replace("<strong>\r\n","<strong>", $clean);
		$clean = str_replace("<br /><br />","</p>\n\n<p>", $clean);
		$clean = str_replace(" </strong>","</strong> ", $clean);

		$clean = str_replace("<p><br />","<p>", $clean);

		$clean = str_replace("<em>-","<em>—", $clean);
		$clean = str_replace("<p><strong>-","<p><strong>—", $clean);
		$clean = str_replace("<p>-","<p>—", $clean);

		$clean = str_replace("<p>&nbsp;</p>","", $clean);
		$clean = str_replace("<p>  </p>","", $clean);

		$clean = str_replace("\r",'', $clean);
		$clean = str_replace("\n",'', $clean);
		$clean = str_replace("<br />","\n", $clean);
		$clean = str_replace("</p>","\n\n", $clean);
		// TODO * Дом > UL LI
		// TODO <p> (\n..) </p>
		//$pattern = "/<p[^>]*><\\/p[^>]*>/";
		$pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";
		$clean = preg_replace($pattern, '', $clean);
		// TODO незакрытые кавычки в конце предложения
		$clean = str_replace("<p></p>","", $clean);
		$clean = str_replace("<p>\n\n","<p>", $clean);
		return $clean;
	}

}

?>