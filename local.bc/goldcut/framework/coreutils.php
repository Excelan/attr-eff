<?php

define('TERM_RED', "\033[91m");
define('TERM_BLUE', "\033[36m");
define('TERM_YELLOW', "\033[93m");
define('TERM_GREEN', "\033[92m");
define('TERM_VIOLET', "\033[95m");
define('TERM_GRAY', "\033[30m");

define('TERM_COLOR_CLOSE', "\033[0m");

function is_urn($purn){
    if (is_object($purn)) $purn = (string) $purn;
    $urna = explode('-', $purn);
    if (count($urna) < 2 || count($urna) > 4 || $urna[0] != 'urn') return false;
    return true;
}

function _option($nskeypath)
{
    $v = Config::value($nskeypath);
    if (get_class($v) == 'String')
        return $v->string;
    else
        return $v;
}

function _str($keypath, $lang=null)
{
    //if (!$lang) throw new Exception("_str({$keypath}, lang) lang not provided");
    if (!$lang) $lang = SystemLocale::$REQUEST_LANG;
    return Config::get('strings/'.$lang, $keypath);
}

function moneyFormatSimple($p)
{
    $p = (float) $p;
    $ip = (int) $p;
    if ($p == $ip)
        return $ip;
    else
        return money_format('%!n', $p);
}

function callerFirstParam($callers = null)
{
	$stack = 0;
	if (!$callers)
	{
		$callers = debug_backtrace();
		$stack++;
	}
	$lines = file($callers[$stack]['file']);
	$lineum = $callers[$stack]['line'] - 1;
	$bs = explode('(', $lines[$lineum]);
	$ps = explode(',', $bs[1]);
	if (count($ps) == 2)
	{
		return $ps[0];
	}
	else
	{
		$ps1 = explode(')', $ps[0]);
		return $ps1[0];
	}
}

function txt2boolean($txt)
{
	if ($txt == 'yes') return true;
	elseif ($txt == 'y') return true;
    elseif ($txt == 'Y') return true;
    elseif ($txt == 'true') return true;
    elseif ($txt == '1') return true;
    elseif ($txt == 1) return true;
    else return false;
}
function boolean2yesnotxt($bool)
{
    if (!is_bool($bool)) $bool = txt2boolean($bool);
    if ($bool === true || $bool == 1)
        return 'yes';
    else
        return 'no';
}

function isURN($urn)
{
	if ($urn instanceof URN) return true;
	if (substr($urn,0,3) == 'urn') return 0;
	return false;
}
function isUUID($uuid)
{
	if ($uuid instanceof UUID) return true;
	return false;
}

function wrapon($d, $w1, $w2)
{
	if ($d) return $w1.$d.$w2;
}

function ajaxRequest($url, $post=null)
{
    // TODO add json decode, check json error
    $d = httpRequest($url, $post);
    return json_decode($d['data'], false); // return Object
}

function anyToString($any)
{
    if (is_string($any)) $string = $any;
    elseif (is_null($any)) $string = '(NULL)';
    elseif (is_array($any)) $string = json_encode($any);
    elseif ($any instanceof Message) $string = (string) $any;
    else $string = print_r($any, true);
    return $string;
}

function renderObjectOverHTMLTemplate(stdClass $ds, $HTML)
{
    $d = new DOMDocument;
    $d->loadHTML($HTML);
    if (!$d) throw new Exception("Load HTML error on $HTML");
    recursiveKeyDataFragmentRenderer($d, $ds);
    return $d->saveHTML($d->documentElement->firstChild->firstChild); //, LIBXML_NOEMPTYTAG
}

function renderContextObjectsOverPlainTemplate(array $context, $templateString)
{
    $T = new Template($templateString);
    foreach ($context as $contextKey => $contextValue)
    {
        $T->context->add($contextKey, $contextValue);
    }
    return (string) $T;
}

function renderCallback($funcName, $context)
{
    if (function_exists($funcName)) {
        $html = call_user_func($funcName, $context);
        return $html;
    }
    else
        throw new Exception("No render function $funcName");
}

function emitSystemEvent(array $ev) {


    // external nodejs system feed archive (file per context)
    if (EXTERNAL_SYSTEMFEED_ENABLED === true) {
        $url = 'http://0.0.0.0:8888/writeFile';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($ev));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        $ch = $curl;
        if (curl_errno($ch)) {
            $errno = curl_errno($ch);
            $error = curl_error($ch) . ' (' . $errno . ')';
            curl_close($ch);
            Log::error($error, 'systemfeed');
        }
    }
}

/**
 TODO conf timeout, Cookie per session not global!
 */
function httpRequest($url, $post=null, $headers=null, $method='GET', $timeout=1800)
{
    Log::debug("$url ".anyToString($post)." $method",'exthttp');
    $httpMethod = 'GET';
    //$cookie_jar = tempnam('/tmp','cookie');
    $url = (string) $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 1); // To get only the headers use CURLOPT_NOBODY
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); //TODO get seconds from ini files
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //TODO get seconds from ini files
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.php.dat'); // TODO add hostname to filename
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.php.dat');

    if ($headers)
    {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // !array
    }

    // TODO clear cookie curl_setopt($ch, CURLOPT_COOKIELIST, "ALL");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if (count($post) && $method != 'PUT')
    {
        $method = 'POST';
        $httpMethod = 'POST';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    elseif ($post && $method == 'PUT')
    {
        $httpMethod = 'PUT';
        $fp = fopen('php://temp/maxmemory:256000', 'w');
        if (!$fp)
            die('could not open temp memory data');
        fwrite($fp, $post);
        fseek($fp, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, strlen($post));
    }
    $output = curl_exec($ch);
    if (curl_errno($ch))
    {
        $errno = curl_errno($ch);
        $error = curl_error($ch).' ('.$errno.')';
        curl_close($ch);
        throw new Exception($error, (int)$errno);
    }
    // {httpcode: 200, url: '/login', effectiveurl: '/account', 'totaltime': 2, data: '<html>', 'headers': [k:v,..], redirectcount: 1, receivedbytes: 1000, 'method': post, 'contenttype': 'html'}
    $meta = array();
    $meta['effectiveurl'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $meta['httpcode'] = (integer) curl_getinfo($ch, CURLINFO_HTTP_CODE); // last
    $meta['totaltime'] = (float) curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $meta['dnstime'] = (float) curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);
    $meta['connecttime'] = (float) curl_getinfo($ch, CURLINFO_CONNECT_TIME);
    $meta['starttransfertime'] = (float) curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);
    $meta['redirectcount'] = (integer) curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
    $meta['receivedbytes'] = (integer) curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    $meta['contenttype'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $headersBytes = (integer) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $meta['url'] = $url;
    $meta['method'] = $httpMethod;
    $header = substr($output, 0, $headersBytes);
    $body = substr($output, $headersBytes);
    $headersarray = explode("\r\n", $header);
    $headersclean = array();
    foreach ($headersarray as $headervalue)
    {
        $hstruct = explode(':', $headervalue); //$headerkey
        if ($hstruct[0] && $hstruct[1])
            $headersclean[$hstruct[0]] = $hstruct[1];
    }
    $meta['headers'] = $headersclean;
    // cookies
    $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
    preg_match_all($pattern, $header, $matches);
    $cookiesOut = implode("; ", $matches['cookie']);
    foreach (explode(';',$cookiesOut) as $kv)
    {
        list($k,$v) = explode('=',$kv);
        $k = trim($k);
        $v = trim($v);
        $meta['newcookies'][$k] = urldecode($v);
    }
    unset($meta['headers']['Set-Cookie']);

    $meta['data'] = $body;
    //unset($body);
    curl_close($ch);
    if ($meta['httpcode'] == 200)
    {
        // $meta['contenttype'] == 'text/html'
        $aa = explode(';', $meta['contenttype']);
        if (count($aa) == 1)
        {
            if ($meta['contenttype'] == 'text/html') $ishtml = true;
        }
        else { // 2 or more
            if ($aa[0] == 'text/html') $ishtml = true;
            $aa[1] = trim($aa[1]);
            //println($aa[1],1,TERM_RED);
            $enc = explode('=', $aa[1]);
            //println($enc);
            if ($enc[0] == 'charset')
            {
                //println($enc[1],1,TERM_YELLOW);
                if ($enc[1] == 'windows-1251')
                {
                    $meta['data'] = mb_convert_encoding($meta['data'], "utf-8", "windows-1251");
                }
            }
        }
//        printlnd($meta['contenttype']);
        if (explode(';', $meta['contenttype'])[0] == 'application/json') $isjson = true;

        if ($ishtml) {
            $d = new DOMDocument;
            $d->loadHTML($body);
            $meta['html'] = $d;
        }
        elseif ($isjson)
        {

            $meta['json'] = json_decode($body, true);
        }
    }
    return $meta;
}


/**
http://www.pagood.ru/seo/pravila-transliteracii-urlov-yandeks-translit-i-gugl-translit/
http://www.rezonans.ru/lab/tablica-translita.html
UKR http://dictumfactum.com.ua/ru/infopoint/61-translit
Ї
I
Yi - в начале слова, і - в других позициях
Їжакевич - Yizhakevych;Кадіївка - Kadiivka

Й
Y, i
Y - в начале слова, і - в других позициях
Йосипівка - Yosypivka;Стрий - Stryi
Є
Ye, ie
Ye - в начале слова, іе - в других позициях
Єнакієве - Yenakiieve;Наєнко - Naienko
Г
H, gh
Н - в большинстве случаев,

gh - когда    встречается комбинация “зг”
Гадяч - Hadiach;Згорани - Zghorany
Ю
Yu, iu
Yu - в начале слова, iu - в других позициях
Юрій - Yurii;Крюківка - Krukivka

Я
Ya, ia
Ya - в начале слова, іа - в других позициях
Яготин - Yahotyn;Iчня - Ichnia

‘ (апостроф)
“
(см. пример)
Знам’янка - Znamianka
*/

// cyrillic
/**
TODO ukr spcific for letters in head of word
OPTION SAVE ' in ukr
*/
function translit($str, $lang='ru')
{

	/**
	$lang = Text::langDetect($str);
	*/
	if (!$lang) $lang = 'ru';

	if ($lang == 'ru')
	{
		$tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"Ch",
        "Ш"=>"Sh","Щ"=>"Sch","Ю"=>"U","Я"=>"Ya",

        "Ъ"=>"","Ы"=>"Y","Ь"=>"","Ё"=>"E","Э"=>"E", // RU SPECIFIC
        "Ґ"=>"G","І"=>"I","Ї"=>"I","Є"=>"E", // UKR in ru

        "а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"sch",
        "ю"=>"u","я"=>"ya",

        "ъ"=>"","ь"=>"","ы"=>"y","э"=>"e","ё"=>"e", // ru specific
        "є"=>"e","ї"=>"i","і"=>"i","ґ"=>"g", // ukr in ru

        " "=> "-", "."=> "", "/"=> "~", "*"=> "~", "№"=> "~"
        );
    }
    else if ($lang == 'ua')
	{
		$tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"Y",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"Ch",
        "Ш"=>"Sh","Щ"=>"Sch","Ю"=>"U","Я"=>"Ya",

        "Ґ"=>"G","І"=>"I","Ї"=>"I","Є"=>"E", // UKR SPECIFIC
        "Ъ"=>"","Ы"=>"Y","Ь"=>"","Ё"=>"E","Э"=>"E", // RU in ua

        "а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"u","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"sch",
        "ь"=>"","ю"=>"u","я"=>"ya",

        "є"=>"e","ї"=>"i","і"=>"i","ґ"=>"g", // ukr specific
        "ъ"=>"","ь"=>"","ы"=>"y","э"=>"e","ё"=>"e", // ru in ua

        " "=> "-", "."=> "", "/"=> "~", "*"=> "~", "№"=> "~"
        );
    }
	else // en, fr etc
	{
		$tr = array(
        	" "=> "-", "."=> "", "/"=> "~", "*"=> "~", "№"=> "~", "é" => 'e', "à" => 'a', "ç" => 'c', "ï" => 'i', "î" => 'i', "ô" => 'o', "ù" => 'u', "ÿ" => 'y'
        );
	}
    //$str = mb_strtoupper($str, 'UTF-8');
    $str = strtr($str, $tr);
    $str = preg_replace('/[^A-Za-z0-9_\-~]/', '', $str);
    $str = str_replace('__','_',$str);
    $str = str_replace('__','_',$str);
    $str = str_replace('--','-',$str);
    $str = str_replace('--','-',$str);
    $str = str_replace('_-_','_',$str);
    return $str;
}


function base64_encode_data_from_file($filename) {
	if (function_exists('finfo_file'))
		$filetype = finfo_file($filename);
	else	if (function_exists('mime_content_type'))
		$filetype = mime_content_type($filename);
	else
	{
		$i = getimagesize($filename);
		$filetype = $i['mime'];
	}
	$imgbinary = fread(fopen($filename, "r"), filesize($filename));
	return 'data:' . $filetype . ';base64,' . base64_encode($imgbinary);
}

function append_data_to_file($filename, $data)
{
	$file = fopen($filename, 'a+');
	if (flock($file, LOCK_EX))
	{
		fwrite($file, $data);
		flock($file, LOCK_UN);
	}
	else
	{
	   throw new Exception("Couldn't get the WRITE EX lock for $filename file!");
	}
	fclose($file);
}

function read_data_from_file($filename)
{
  if (!file_exists($filename)) throw new Exception("File $filename not exists");
    return join('',file($filename));
}

function save_data_as_file($filename, $data)
{
	$file = fopen($filename, 'w');
	if (flock($file, LOCK_EX))
	{
		fwrite($file, $data);
		flock($file, LOCK_UN);
	}
	else
	{
	   throw new Exception("Couldn't get the WRITE EX lock for $filename file!");
	}
	fclose($file);
}

function new_temp_file()
{
  $tmpfname = tempnam(sys_get_temp_dir(), rand(500000,900000));
  return $tmpfname;
}

function new_temp_resource()
{
  $tmpfname = tmpfile();
  return $tmpfname;
}

function save_data_as_temp_file($data)
{
  $filename = new_temp_file();
	$file = fopen($filename, 'w');
	fwrite($file, $data);
	fclose($file);
  return $filename;
}


function base64_decode_file($base64)
{
	$comapos = strpos($base64,',');
	return base64_decode(substr($base64,++$comapos));
}

function getLineInFile(Exception $e)
{
    $file = file($e->getFile());
    $line = $e->getLine();
    $codeline = trim($file[$line-1]);
    return $codeline;
}

function domGetImageDimSize($domel)
{
	$eachside = $domel->getAttribute('eachside');
	$horizontal = $domel->getAttribute('horizontal');
	$vertical = $domel->getAttribute('vertical');
	$largestside = $domel->getAttribute('largestside');
	if ($eachside)
		$size = array('dim'=>'eachside','size'=>$eachside);
	elseif ($horizontal)
		$size = array('dim'=>'horizontal','size'=>$horizontal);
	elseif ($vertical)
		$size = array('dim'=>'vertical','size'=>$vertical);
	elseif ($largestside)
		$size = array('dim'=>'largestside','size'=>$largestside);
	return $size;
}

function get_time_difference( $start, $end )
{
    //$uts['start']      =    strtotime( $start );
    //$uts['end']        =    strtotime( $end );
    $uts['start']      =    $start;
    $uts['end']        =    $end;
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }
        else
        {
            trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}


function mysqldate2timestamp($mysqldate)
{
	$year = (integer) substr($mysqldate, 0, 4);
	$month = (integer) substr($mysqldate, 5, 2);
	$day = (integer) substr($mysqldate, 8, 2);
	$hour = (integer) substr($mysqldate, 11, 2);
	$min = (integer) substr($mysqldate, 14, 2);
	$sec = (integer) substr($mysqldate, 17, 2);
	return mktime($hour, $min, $sec, $month, $day, $year);
}





function detect_platform()
{
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		return "WIN";
	} elseif (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN') {
		return "OSX";
	} elseif (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
		return "LINUX";
    } elseif (strtoupper(substr(PHP_OS, 0, 5)) === 'SUNOS') {
        return "SUNOS";
	} elseif (preg_match('BSD', PHP_OS)) {
		return "BSD";
	} else {
		throw new Exception("Unknow system");
	}
}

function is_web_request()
{
	/**
	if(php_sapi_name() == 'cli'){
	 */
	return ( ($_SERVER['DOCUMENT_ROOT']) ? true : false );
}

/**
run script from text editor - vim or TextMate
 */
function in_console()
{
	//var_dump(is_web_request());
	return !is_web_request();
	// return ( (getenv('TM_DIRECTORY') || getenv('MYVIMRC')) ? false : true );
}

function under_win()
{
	return ( detect_platform() == 'WIN' );
}

function printColor($s, $color)
{
	if (in_console() && !under_win()) echo $color;
	if ( is_web_request() && $color) {
		if ($color === TERM_RED)	$htmlcolor = 'red';
		if ($color === TERM_GREEN)	$htmlcolor = 'green';
		if ($color === TERM_YELLOW)	$htmlcolor = 'yellow';
		if ($color === TERM_BLUE)	$htmlcolor = 'lightblue';
		if ($color === TERM_VIOLET)	$htmlcolor = 'violet';
		if ($color === TERM_GRAY)	$htmlcolor = 'gray';
		print '<span style="color: '.$htmlcolor.'">';
	}
	echo $s;
	if (in_console() && !under_win()) echo TERM_COLOR_CLOSE;
	if ( is_web_request() && $color) echo '</span>';
}

function printml($s,$tag,$class)
{
	if ($class) $class = " class=\"$class\"";
	print "<{$tag}{$class}>$s</$tag>";
}

function printhref($href, $text, $id=null, $class=null)
{
	if ($class) $class = " class=\"$class\" ";
	if ($id) $id = " id=\"$id\" ";
	print "<a href=\"{$href}\"{$id}{$class}>$text</a>";
}

function dprintln($s, $level=0, $color=false, $stacklevel=1)
{
	if (TEST_ENV === true)
		println($s, $level, $color, $stacklevel);
}
function dprintlnd($s, $level=0, $color=false, $stacklevel=1)
{
	if (TEST_ENV === true)
		printlnd($s, $level, $color, $stacklevel);
}

function printlnd($s, $level=0, $color=false, $stacklevel=0)
{
	if (is_object($s)) print '@' . get_class($s)." ";
	if (is_string($s) && !is_numeric($s)) print '@string ';
	if (is_string($s) && is_numeric($s)) print '@numeric_string ';
	if (is_int($s)) print '@int ';
	if (is_float($s)) print '@float ';
	if (is_array($s)) print '@array ';
	println($s, $level, $color, ++$stacklevel);
}

function println($s, $level=0, $color=false, $stacklevel=0)
{
    if (SCREENLOG === true)
    {
        $trace = debug_backtrace();
        $callerClass = $trace[$stacklevel+1]['class'];
        $callerFunction = $trace[$stacklevel+1]['function'];
        $filename = substr($trace[$stacklevel]['file'], strlen(BASE_DIR));
        if ($stacklevel !== false) $origin = "\t({$callerClass}::{$callerFunction}) {$filename}:{$trace[$stacklevel]['line']}";
    }

	if ( is_web_request() ) print "\n<pre>\n";
	if ($level > 0)
		for ($i=0;$i<$level-1;$i++) print "\t";

	if ($color !== false && in_console() && !under_win()) echo $color;
	if ( is_web_request() && $color) {
		if ($color === TERM_RED)	$htmlcolor = 'red';
		if ($color === TERM_GREEN)	$htmlcolor = 'green';
		if ($color === TERM_YELLOW)	$htmlcolor = 'yellow';
		if ($color === TERM_BLUE)	$htmlcolor = 'lightblue';
		if ($color === TERM_VIOLET)	$htmlcolor = 'violet';
		if ($color === TERM_GRAY)	$htmlcolor = 'gray';
		print '<span style="color: '.$htmlcolor.'">';
	}

	if (is_int($s))
		print $s;
	elseif (is_array($s))
		print json_encode($s);
    elseif (is_object($s) && get_class($s) == 'stdClass') {
        print json_encode((array)$s);
    }
    elseif (is_object($s) && get_class($s) == 'Message') {
        if (is_web_request())
            print Utils::array_to_colored_json($s->get());
        else
            print json_encode($s->get());
    }
	elseif ($s === true)
		print '(TRUE)';
	else {
		if ($s === '') print "(EMPTY STRING)";
		else if ($s === false) print "(FALSE)";
		else if ($s === null) print "(NULL)";
		else print ltrim(rtrim($s));
	}
    if ($origin) printColor($origin,TERM_GRAY);
	if ($color !== false && in_console() && !under_win()) echo TERM_COLOR_CLOSE;
	if ( is_web_request() && $color) echo '</span>';
	if ( is_web_request() ) print "</pre>";
	print "\n";
}

function printLine()
{
	print "\n";
	if ( is_web_request() ) print "<hr>";
	else for ($i=0;$i<72;$i++) print "-";
	print "\n";
}

function printH($h)
{
	if ( is_web_request() ) print "<hr>";
	else for ($i=0;$i<72;$i++) print "-";
	print "\n";
	if ( is_web_request() ) print "<strong>";
	elseif (in_console() && !under_win()) echo "\033[93m";
	print strtoupper($h);
	if ( is_web_request() ) print "</strong>";
	elseif (in_console() && !under_win()) echo "\033[0m";
	print "\n";
	if ( is_web_request() ) print "<hr>";
	else for ($i=0;$i<72;$i++) print "-";
	print "\n";
}


function uuid($prefix = '')
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid  = substr($chars,0,8) . '-';
	$uuid .= substr($chars,8,4) . '-';
	$uuid .= substr($chars,12,4) . '-';
	$uuid .= substr($chars,16,4) . '-';
	$uuid .= substr($chars,20,12);
	return $prefix . $uuid;
}

function balanced_uuid()
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid  = substr($chars,0,1) . '/';
	$uuid .= substr($chars,1,1) . '/';
	$uuid .= substr($chars,2,1);
	return $uuid;
}

function short_uuid()
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid  = substr($chars,0,8);
	return $uuid;
}

function shortcode()
{
	//$chars = md5(uniqid(mt_rand(), true));
	//$uuid  = substr($chars,0,3) . rand(100,999);
	$uuid  = rand(100,999) . rand(100,999) . rand(100,999);
	return strtoupper($uuid);
}

function login_hash()
{
	$chars = md5(uniqid(mt_rand(), true));
	return substr($chars,0,32);
}

function is_json($s)
{
	if (is_string($s))
	{
		$s = trim($s);
		if (substr($s,0,1) == '{' || substr($s,0,1) == '[')
			return true;
	}
	return false;
}

function is_xml($s)
{
	if (is_string($s))
	{
		$s = trim($s);
		if (substr($s,0,1) == '<')
			return true;
	}
	return false;
}

function getRequestParameter($key, $default = null)
{
	if(isset($_REQUEST[$key])) return $_REQUEST[$key];
	else return $default;
}


// for array_filter
function morethenone($v)	{ return($v > 1); }
function morethenten($v)	{ return($v > 10); }

function cp1251toUT8($s)	{ return iconv('cp1251', 'UTF-8//IGNORE', $s);	}
function UTF8to1251($s)	{ return iconv('UTF-8', 'cp1251//IGNORE', $s);	}



function get_option($name, $field='name')
{
	$lang = SystemLocale::$REQUEST_LANG;
	$m = new Message();
	$m->action = 'load';
	$m->urn = "urn-options";
	$m->lang = $lang;
	$m->$field = $name;
	$o = $m->deliver();
	if ($o->count() != 1)
		return null;
	else
		return $o->current()->gtext;
}

function get_fragment($name)
{
	$lang = SystemLocale::$REQUEST_LANG;
	$m = new Message();
	$m->action = 'load';
	$m->urn = "urn-fragment";
	$m->lang = $lang;
	$m->name = $name;
	$o = $m->deliver();
	if ($o->count() != 1)
		return null;
	else
		return $o->current()->fullhtml;
}


function page_by_uri($uri)
{
	$m = new Message('{"action": "load"}');
	$m->urn = 'urn:page';
	$m->lang = SystemLocale::$REQUEST_LANG;
	$m->uri = $uri;
	return $m->deliver()->current();
}


function category_by_uri($uri)
{
	try
	{
		$m = new Message();
		$m->action = 'load';
		$m->urn = "urn-category";
		$m->uri = $uri;
		$m->last = 1;
		$m->lang = SystemLocale::$REQUEST_LANG;
		$r = $m->deliver();
		if (count($r)) return $r->current();
	}
	catch (Exception $e)
	{
		//print "123 $e";
	}
	return null;
}
function category_by_urn($urn)
{
	$m = new Message('{"action": "load"}');
	$m->urn = $urn;
	$m->lang = SystemLocale::$REQUEST_LANG;
	return $m->deliver()->current();
}

function page_by_urn($urn)
{
	$m = new Message('{"action": "load"}');
	$m->lang = SystemLocale::$REQUEST_LANG;
	$m->urn = $urn;
	return $m->deliver()->current();
}

function urn($urn_string)
{
	if (is_object($urn_string))
		return $urn_string;
	else if (is_string($urn_string))
	{
		if (substr($urn_string,0,3)=='urn')
			return new URN($urn_string);
		else
			return null;
	}
	else
		return null;
}

function tofloat($num) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

    if (!$sep) {
        return floatval(preg_replace("/[^0-9]/", "", $num));
    }

    return floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
    );
}
/*
$num = '1.999,369€';
var_dump(tofloat($num)); // float(1999.369)
$otherNum = '126,564,789.33 m²';
var_dump(tofloat($otherNum)); // float(126564789.33)
*/

function load_widget($widget_name, $options)
{
    Utils::startTimer($widget_name);
	$widget_file = BASE_DIR."/widgets/{$widget_name}.php";
	if (!file_exists($widget_file))
		return "ERROR: {$widget_name} not exists <br>\n";
	ob_start();
    try {
        include_once $widget_file;
        $widget_function_name = "widget_{$widget_name}";
        if (function_exists($widget_function_name)) {
            $html = call_user_func($widget_function_name, $options);
            echo $html;
        }
        //else Log::error("name: {$widget_name}, FN not exists: widget_{$widget_name}", 'widget');
    }
    catch (Exception $e) {
        if (ENV == 'DEVELOPMENT')
            print $e->getMessage();
        else
            Log::error($e->getMessage(), 'widgeterror');
    }
	$outbuffer = ob_get_clean();
	ob_end_clean();
	//Log::info(substr($outbuffer,0,100),'widgets');
    $timereport = Utils::reportTimer($widget_name);
    Log::info("$widget_name ~ {$timereport['time']}", 'benchwidget');
	return $outbuffer;
}

function is_64bit()
{
	$int = "9223372036854775807";
	$int = intval($int);
	if ($int == 9223372036854775807)
		return true;
	elseif ($int == 2147483647)
		return false;
	else
		return null;
}


function isCli()
{
	if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
		return true;
	else
		return false;
}

function ConditionalHttpGet($last_modified, $etag)
{
	//var_dump($last_modified);
	//var_dump($etag);
    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
    $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false;
    if (!$if_modified_since && !$if_none_match)
	{
		//echo '!$if_modified_since && !$if_none_match IN REQUEST';
        return true;
    }
    // хотя бы один из заголовков передан на проверку
    if ($if_none_match && $if_none_match != $etag) {
        //echo "// etag есть, но не совпадает";
    		return true; // etag есть, но не совпадает
    }
    if ($if_modified_since && $if_modified_since != $last_modified) {
    		//echo "if-modified-since есть, но не совпадает";
        return true; // if-modified-since есть, но не совпадает
    }
    // контент не изменился
    return false;
}

function http_modified_date($ts)
{
	return date('D, d M Y H:i:s O', $ts);
}














function stringVariateByTemplate($content)
{
    preg_match_all('#{(.*)}#Ui',$content,$matches);
    for ($i=0; $i<sizeof($matches[1]); $i++)
    {
        $ns = explode("|",$matches[1][$i]);
        $c2 = sizeof($ns);
        $rand = rand(0,($c2-1));
        $content = str_replace("{".$matches[1][$i]."}",$ns[$rand],$content);
    }
    return $content;
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function check_json_decode_result($json=null)
{
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return true;
            break;
        case JSON_ERROR_DEPTH:
            throw new Exception('JSONERROR1 Достигнута максимальная глубина стека '.$json);
            break;
        case JSON_ERROR_STATE_MISMATCH:
            throw new Exception('JSONERROR2 Некорректные разряды или не совпадение режимов '.$json);
            break;
        case JSON_ERROR_CTRL_CHAR:
            throw new Exception('JSONERROR3 Некорректный управляющий символ '.$json);
            break;
        case JSON_ERROR_SYNTAX:
            throw new Exception('JSONERROR4 Синтаксическая ошибка, не корректный JSON '.$json);
            break;
        case JSON_ERROR_UTF8:
            throw new Exception('JSONERROR5 Некорректные символы UTF-8, возможно неверная кодировка '.$json);
            break;
        default:
            throw new Exception('JSONERROR6 Неизвестная ошибка '.$json);
            break;
    }
}

function qr_detect_zbar($file)
{
    /*
    if (strtoupper(substr(PHP_OS, 0, 5)) !== 'LINUX') {
        dprintln("Not ready for ZBar not on Linux");
        return null;
    }
    */
    ob_start();
    if (OS::getOS() == 'SMARTOS' || OS::getOS() == 'SUNOS')
        passthru("/opt/local/bin/zbarimg $file 2>&1");
    else
        passthru("zbarimg $file 2>&1");
    $output = ob_get_contents();
    Log::debug($output,'qrzbar');
    ob_end_clean();

    $search = '/QR-Code:(.*)/';
    $matches = array();
    $found = preg_match($search, $output, $matches, PREG_OFFSET_CAPTURE, 0);
    if ($found > 0) {
        $qr = $matches[1][0];
        Log::debug($qr,'qrzbar');
        return $qr;
    }
    else
    {
        Log::debug("not found / zbarimg $file",'qrzbar');
        return false;
    }
}



function shell_run_command($dir, $command) {
    if (!$command) throw new Exception("No command");
    chdir($dir);
    ob_start();
    $cmd = "$command 2>&1";
    passthru($cmd);
    $stdout = ob_get_contents();
    ob_end_clean();
    return $stdout;
}

function getColumnFromArrayOfDict($array, $columnName)
{
    $col = array();
    foreach ($array as $ae)
    {
        //$col[$ae['id']] = $ae[$columnName];
        array_push($col, $ae[$columnName]);
    }
    return $col;
}

function prepareExternalData($d, $id=null, $key='id')
{
    $data = array();
    foreach ($d as $e) // $et
    {
	    //if (is_object($et)) $e = (array) $et;
	    //else $e = $et;
        $data[$e[$key]] = $e;
    }
    if (is_integer($id))
        return $data[$id];
    elseif(is_null($id))
    {
        return $data;
    }
    elseif(is_array($id))
    {
        $filtered = array();
        foreach ($data as $lid => $el)
        {
            if (in_array($lid, $id))
                $filtered[$lid] = $el;
        }
        return $filtered;
    }
    else
    {
        throw new \Exception('prepareExternalData() Unsupported id type. Non int or array');
    }

}

function externalDataStub($entity, $id)
{
    $f = BASE_DIR."/test/externaldata/{$entity}.json";
    $d = json_decode(join('',file($f)), true);
    $data = prepareExternalData($d, $id);
    return $data;
}

function externalData($entity, $id)
{
	$externalDataTTL = 60*60*24*7; // 60 sec 60 min 24 hours
	// TODO cache policy per entity
//	printlnd(ENV,1,TERM_GREEN);
//	printlnd(INTEST,1,TERM_GRAY);
//	printlnd(USE_REAL_EXTERNAL_DATA_IN_TEST,1,TERM_VIOLET);
    if (INTEST === true && USE_REAL_EXTERNAL_DATA_IN_TEST !== true)
    {
//	    printlnd('Y',1,TERM_VIOLET);
	    Log::debug('External data from stub json');
        return externalDataStub($entity, $id);
    }
    else
    {
        $fn = "loadExternal_$entity";
        $cacheKey = $fn;
        if ($id) $cacheKey .= $id;
        if (Cache::is_enabled() && $dataLoaded = Cache::get($cacheKey, $externalDataTTL)) // 4 housr cache external data http requests
        {
	        Log::info("Cached $cacheKey",'externaldata');
	        if (!$dataLoaded) Log::error("Cached NULL $cacheKey",'externaldata');
        }
        else
        {
            $dataLoaded = $fn($id);
	        //println($dataLoaded,1,TERM_YELLOW); // TODO
            if (count($dataLoaded))
            {
                Cache::put($cacheKey, $dataLoaded, $externalDataTTL);
                if (Cache::is_enabled()) Log::info("Cache $cacheKey",'externaldata');
            }
            else
            {
	            Log::error("Blank array $cacheKey", 'externaldata');
	            if (Cache::is_enabled()) Log::error("Not cached blank array $cacheKey", 'externaldata');
            }

        }
        $data = prepareExternalData($dataLoaded, $id);
	    //println($data,2,TERM_YELLOW); // TODO
        return $data;
    }
}

function time_diff($dt1, $dt2)
{
    $y1 = substr($dt1, 0, 4);
    $m1 = substr($dt1, 5, 2);
    $d1 = substr($dt1, 8, 2);
    $h1 = substr($dt1, 11, 2);
    $i1 = substr($dt1, 14, 2);
    $s1 = substr($dt1, 17, 2);

    $y2 = substr($dt2, 0, 4);
    $m2 = substr($dt2, 5, 2);
    $d2 = substr($dt2, 8, 2);
    $h2 = substr($dt2, 11, 2);
    $i2 = substr($dt2, 14, 2);
    $s2 = substr($dt2, 17, 2);

    $r1 = date('U', mktime($h1, $i1, $s1, $m1, $d1, $y1));
    $r2 = date('U', mktime($h2, $i2, $s2, $m2, $d2, $y2));
    return ($r1 - $r2);
}

function arraygroup($array, $field_name, $internalSortField = false) // group Forward, internal sort Forward
{
    foreach ($array as $d)
    {
        $kk = $d[$field_name];
        $dataG[$kk][] = $d;
    }
    krsort($dataG);
    foreach ($dataG as $g => $itemsInGroup)
    {
        usort($itemsInGroup, create_function('$a,$b', 'return $b[' . $internalSortField . '] > $a[' . $internalSortField . '];'));
    }
    return $dataG;
}

?>
