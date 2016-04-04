<?php

/**
remove trailing endlines
find . -not \( -name .svn -prune -o -name .git -prune \) -type f -print0 | xargs -0 sed -i '' -E "s/[[:space:]]*$//"

http://igorsviridov.narod.ru/webdes/unicode.html
Таблица символов unicode
Знак дюйма	"	&#34;	&quot; и тд
*/

class Utils
{
	private static $start;

	public static function getClientIP()
	{
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function array_to_colored_json($array, $re=false) {
		$es = json_encode($array);
		if ($re===false) {
			$es = UnicodeOp::decodeUnicodeString($es);
			if (is_web_request()) {
				$es = preg_replace("\"([a-z0-9_-])*\":", "<span class='arraykey'>\\0</span>", $es);
				$es = preg_replace("\"([0-9])*\":", "<span class='arrayvalue'>\\0</span>", $es);
			}
			else
			{
				$es = preg_replace("\"([a-z0-9_-])*\":", "\\0", $es);
				$es = preg_replace("\"([0-9])*\":", "\\0", $es);
			}
			$es = str_replace('\/', '/',$es);
		}
		return $es;
	}

	public static function get_remote_json($url)
	{
		$fp = fopen( $url, 'r' );
		$content = "";
		while( !feof( $fp ) )
		{
			$buffer = trim( fgets( $fp, 4096 ) );
			$content .= $buffer;
		}
		fclose($fp);
		return json_decode($content, true);
	}

	public static function getTime() {
		$a = explode (' ',microtime()); return(double) $a[0] + $a[1];
	}

	public static function startTimer($task='task') {
		self::$start[$task] =  self::getTime();
	}

	public static function parse_array_or_json($data)
	{
		if (!$data) throw new Exception("DATA IS BLANK (NOT ARRAY NOT JSON)");
		if (!is_array($data))
		{
			$d = json_decode($data, true);
			if (!$d) throw new Exception("DATA IS JSON WITH ERRORS - {$data}");
		}
		else
			$d = $data;
		return $d;
	}

	public static function check_path($path)
	{
		if (!file_exists ( $path ))
		{
			if (!(mkdir($path, octdec(FS_MODE_DIR), true)))
				throw new Exception("Failed to deep create dirs ($path) TODO FIND REASON HERE\n");
		}
	}

	public static function check_file($file)
	{
		if (file_exists($file))
			return true;
		return false;
	}

	public static function check_image($path)
	{
		if (!file_exists($path))
			throw new Exception("IMAGE AS FILE [$path] NOT EXISTS");
		$refsize = getimagesize($path);
		if (!$refsize[0])
			throw new Exception("IMAGE FILE [$path] EXISTS BUT ITS NOT A IMAGE HAVE NOT SIZE");
		return true;
	}


	public static function file_in_path($filepath)
	{
		$pos = strrpos($filepath, '/');
		if($pos===false)
			return $filepath;
		else
			return substr($filepath, $pos+1);
	}

	public static function filename_extension($filename, $opt=null)
	{
		$filename = self::file_in_path($filename);
		$pos = strrpos($filename, '.');
		if ($pos === false)
		{
			return false;
		}
		else
		{
			$ext = substr($filename, $pos+1);
            if ($opt === 'extasis') {}
            else
                $ext = strtolower($ext);
			$name = substr($filename, 0, $pos);
			return array('name'=>$name,'extension'=>$ext);
		}
	}









	public static function reportTimer($task='task')
	{
		$end = self::getTime();
		$taketime = $end - self::$start[$task];
		if ($taketime <= 0.01) $color = 'green';
		elseif ($taketime > 0.01 && $taketime <= 0.1) $color = 'blue';
		elseif ($taketime > 0.1 && $taketime <= 0.5) $color = 'red';
		elseif ($taketime > 0.5 && $taketime <= 1) $color = 'FF0099';
		elseif ($taketime > 1 && $taketime <= 3) $color = 'FF00FF';
		else $color = 'black';
		if ($taketime <= 0.0001) $nform = number_format($taketime,6);
		elseif ($taketime <= 0.01) $nform = number_format($taketime,4);
		else $nform = number_format($taketime,2);

		if ( $taketime < 3600)
			return array("time"=>$nform,"color"=>$color);
		else
			throw new Exception("Timer report withot startup\n");
		//flush();
	}

	public static function SimpleText($txt) {
		$txt = nl2br($txt);
		return $txt;
	}

	public static function formatBytes($bytes, $precision = 1) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		$formatter = array('','');
		if (is_web_request()) $formatter = array('<sub>','</sub>');
		return round($bytes, $precision) . $formatter[0] . $units[$pow] . $formatter[1];
	}

    public static function unzip_file($zip, $path, $opts='')
    {
        $command = "rm -rf $path";
        $er = system($command, $retval);
        $command = "unzip -qq -u $opts '$zip' -d '$path'";
        $er = system($command, $retval);
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
        {
            if ($retval != 0) throw new Exception("failed to UNZIP $zip to $path ($command)\n");
        }
    }

	public static function zip_folder($path, $zip)
	{
		$command = "rm -rf $zip";
		$er = system($command, $retval);
		$command = "zip -r -m -q -0 $zip $path";
		$er = system($command, $retval);
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			if ($retval != 0) throw new Exception("failed to ZIP $path to $zip ($command)\n");
		}
	}

    public static function pack_any($zipfile, $patharray, $level=3)
    {
        $allfiles = join(' ', $patharray);
        // osx /opt/local/bin/
        $prog = '7za';
        if (OS::getOS() == 'SMARTOS' || OS::getOS() == 'DARWIN')
        {
            $prog = '/opt/local/bin/7za';
        }
        $archcmd = "$prog a -tzip -bd -r -mx{$level}"; // can make ISO -tiso no compress -mx0
        $cmd = "cd ".TMP_DIR."; rm -rf {$zipfile}; {$archcmd} {$zipfile} {$allfiles}";
        Log::info($cmd, 'zip');
        //$cmd = "cd {$root}; zip -r -j {$root}/{$userroot}/{$zipfile} {$allfiles}";
        //$archcmd = "zip -UN=UTF8 -r";
        // -UN=UTF8 - native names
        // -UN=e - escape names
        $retval = null;
        ob_start(); // catch stdout/err
        system($cmd, $retval); // fpassthru
        $outbuffer = ob_get_contents();//ob_get_clean();
        //ob_end_flush();
        ob_end_clean();
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
        {
            if ($retval == 127) throw new Exception("NO 7ZIP IN SYSTEM\n");
            else if ($retval != 0) throw new Exception("Failed to $cmd - $retval\n");
        }
        return $outbuffer;
    }

    public static function list_rec_dir_images($dir)
    {
        try
        {
            $directory = $dir;
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
            $objects->setMaxDepth(10);
            foreach ($objects as $fileinfo)
            {
                $file = $fileinfo;
                if ($fileinfo->isFile())
                {
                    $fname = $fileinfo->getFilename();
                    //$fpath = $fileinfo->getPath();
                    if ( substr($fname,0,1) != '.' && !$file->isDir() && ( strstr($fname,'JPG') || strstr($fname,'jpg') || strstr($fname,'jpeg') || strstr($fname,'JPEG') || strstr($fname,'png') || strstr($fname,'PNG') ))
                    {
                        $photos[] = (string) $file;
                        //if ($makefullpath===false) $photos[] = (string) $file;
                        //else $photos[] = $dir."/".$fname;
                    }
                }
            }
            return $photos;
        }
        catch (Exception $e) {
            echo "No files Found in $dir!<br />";
            return false;
        }
    }

	public static function list_dir_images($dir, $makefullpath=false)
	{
		try
		{
			foreach ( new DirectoryIterator($dir) as $file )
			{
				if ( substr($file,0,1)!='.' && !$file->isDir() && ( strstr($file,'JPG') || strstr($file,'jpg') || strstr($file,'jpeg') || strstr($file,'JPEG') || strstr($file,'png') || strstr($file,'PNG') ))
				{
					if ($makefullpath===false) $photos[] = (string) $file;
					else $photos[] = $dir."/".$file;
				}
			}
			return $photos;
		}
		catch (Exception $e) {
			echo "No files Found in $dir!<br />";
			return false;
		}
	}

    public static function list_batch_images($dir, $bydir=false)
    {
        //println($dir,1,TERM_BLUE);
        $batch = array();
        try
        {
            $dirs = array();
            //foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file )
            foreach ( new DirectoryIterator($dir) as $file )
            {
                //println($file);
                if ($file->isDir()) $dirs[] = (string) $file;
                if ( substr($file,0,1)!='.' && !$file->isDir() && ( strstr($file,'JPG') || strstr($file,'jpg') || strstr($file,'jpeg') || strstr($file,'JPEG') || strstr($file,'png') || strstr($file,'PNG') ))
                {
                    $id = null;
                    if ($bydir)
                        $id = $bydir;
                    else
                    {
                        $ar = explode("_", $file);
                        if (((int)$ar[0]) > 0) $id = $ar[0];
                        else if (((int)$ar[1]) > 1) $id = $ar[1];
                        else println("NO ID IN $file", 1, TERM_RED);
                    }
                    if ($id) $batch[$id][] = $dir."/".$file;
                }
            }
            foreach ($dirs as $edir) {
                $id = (int) (string) $edir;
                if ($id)
                {
                    //printlnd($id);
                    //println($dir.'/'.$edir);
                    $x = self::list_batch_images($dir.'/'.$edir, $id);
                    //printlnd($x,2);
                    $batch[$id] = $x[$id];
                }
            }
            //println($batch,1,TERM_VIOLET);
            return $batch;
        }
        catch (Exception $e) {
            echo "No files Found in $dir!<br />";
            return false;
        }
    }


	public static function receiveUploadedFILES($suffix)
	{
		$files = array();
		$fileCount = count($_FILES[$suffix]['name']);
		for($i = 0; $i < $fileCount; $i++)
		{
			$error = (int) $_FILES[$suffix]['error'][$i];
			$type = $_FILES[$suffix]['type'][$i];
			$size = $_FILES[$suffix]['size'][$i];
			$name = basename($_FILES[$suffix]['name'][$i]); // some browsers will give you C:\fullpath\img.jpg as name. You cant just Ignore Exceptions.
			$tmpfile = $_FILES[$suffix]['tmp_name'][$i];
			/**
			4 == $error если в форме был input file, а файл не выбран. те это "soft error"
			*/
			if (4 == $error) continue;
			if ($error > 0)
			{
				$errorText = "Error {$error} in upload";
				Log::error($errorText, 'myerrornamespace');
				throw new Exception($errorText);
			}
			if (is_uploaded_file($tmpfile))
			{
				$files[$name] = $tmpfile;
			}
			else
			{
				$errorText = "Forget upload. (Security)";
				Log::error($errorText, 'myerrornamespace');
				throw new Exception($errorText);
			}
		}
		return $files;
	}

	public static function createEntitiesFromUploadedFiles($files, $destination=null, $params=null)
	{
		$es = array();
		foreach ($files as $name => $tmpfile)
		{
			$m = new Message();
			$m->action = 'create';
			$m->urn = 'urn:file';
			$m->file = $tmpfile;
			$m->uri = $name;
			if ($destination) $m->destination = $destination;
			foreach ($params as $name => $value)
			{
				$m->$name = $value;
			}
			$e = $m->deliver();
			$urn = (string) $e->urn;
			$es[$urn] = $e;
		}
		return $es;
	}

	static function cleanFilesEndWhites($dirs)
	{
		if (!$dirs) $dirs = array('goldcut','mq_rpc','config','data-plugins','managers','apps');
		foreach ($dirs as $D)
		{
			$directory = BASE_DIR.DIRECTORY_SEPARATOR.$D;
			if (!file_exists($directory)) continue;
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
			$objects->setMaxDepth(10);
			foreach ($objects as $fileinfo)
			{
				if ($fileinfo->isFile())
				{
					$fname = $fileinfo->getFilename();
					$fpath = $fileinfo->getPath();
					if (substr($fname,-4,4) == '.php')
					{
						echo($fpath.DIRECTORY_SEPARATOR.$fname." clean<br>\n");
						$f = file_get_contents($fileinfo);
						save_data_as_file($fpath.DIRECTORY_SEPARATOR.$fname, trim($f));
					}
				}
			}
		}
	}

	// from http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
	public static function auto_link_text($text)
	{
	   $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
	   $callback = function($matches) {
	       $url       = array_shift($matches);
	       $url_parts = parse_url($url);

	       $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
	       $text = preg_replace("/^www./", "", $text);

	       $last = -(strlen(strrchr($text, "/"))) + 1;
	       if ($last < 0) {
	           $text = substr($text, 0, $last) . "&hellip;";
	       }
	       return sprintf('<a rel="nofollow" href="%s">%s</a>', $url, $text);
	   };
	   return preg_replace_callback($pattern, $callback, $text);
	}

	/**
	different list views protect from se indexing with metatags
	*/
	public static function hrefLinkParamsMix($url, $mixParams, $originalParams, $defaultValues)
	{
		// printH($url);
		$filtered = array('uri');
		$params = array();
		foreach ($originalParams as $k => $v)
		{
			if (!in_array($k, $filtered)) $params[$k] = $v;
		}
		foreach ($mixParams as $k => $v)
		{
			$params[$k] = $v;
		}
		// printlnd($params);
		foreach ($defaultValues as $k => $v)
		{
			if ($params[$k] == $v) unset($params[$k]);
		}
		if (count($params))
		{
			foreach($params as $k => $v)
			$paramsStr[] = "{$k}={$v}";
			$url = $url.'?'.join('&',$paramsStr);
		}
		return $url;
	}

	public static function hrefLinkPathAdd($url, $pathadd)
	{
		$s2 = explode('?',$url);
		$onlylink = $s2[0];
		$linkparams = $s2[1];
		foreach ($pathadd as $p) $onlylink .= '/'.$p;
		if ($linkparams)
			return $onlylink.'?'.$linkparams;
		else
			return $onlylink;
	}

	public static function uri2title($uri)
	{
		// mb_ucfirst = preg_replace( '/^(\S)(.*)$/eu', "mb_strtoupper('\\1', 'UTF-8').mb_strtoupper('\\2', 'UTF-8')", $string );
		$uri = ucfirst($uri);
		$uri = str_replace('-',' ',$uri);
		$uri = str_replace('_',' ',$uri);
		$uri = htmlspecialchars_decode($uri);
		$uri = str_replace('&apos;','',$uri);
		return $uri;
	}

	public static function textHasSense($s)
	{
		// TODO digits to chars?
		// TODO words or hash?
		if (strlen($s) > 10 || count(explode(' ',$s))>1) return true;
		return false;
	}
}

class Date_Difference
{
    public static function getStringResolved($date, $compareTo = NULL)
    {
        if(!is_null($compareTo)) {
            $compareTo = new DateTime($compareTo);
        }
        return self::getString(new DateTime($date), $compareTo);
    }

    public static function getString(DateTime $date, DateTime $compareTo = NULL)
    {
        if(is_null($compareTo)) {
            $compareTo = new DateTime('now');
        }
        $diff = $compareTo->format('U') - $date->format('U');
        $dayDiff = floor($diff / 86400);

        if(is_nan($dayDiff) || $dayDiff < 0) {
            return '';
        }

        if($dayDiff == 0) {
            if($diff < 60) {
                return 'Just now';
            } elseif($diff < 120) {
                return '1 minute ago';
            } elseif($diff < 3600) {
                return floor($diff/60) . ' minutes ago';
            } elseif($diff < 7200) {
                return '1 hour ago';
            } elseif($diff < 86400) {
                return floor($diff/3600) . ' hours ago';
            }
        } elseif($dayDiff == 1) {
            return 'Yesterday';
        } elseif($dayDiff < 7) {
            return $dayDiff . ' days ago';
        } elseif($dayDiff == 7) {
            return '1 week ago';
        } elseif($dayDiff < (7*6)) { // Modifications Start Here
            // 6 weeks at most
            return ceil($dayDiff/7) . ' weeks ago';
        } elseif($dayDiff < 365) {
            return ceil($dayDiff/(365/12)) . ' months ago';
        } else {
            $years = round($dayDiff/365);
            return $years . ' year' . ($years != 1 ? 's' : '') . ' ago';
        }
    }
}

?>
