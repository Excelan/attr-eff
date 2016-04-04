<?php 
/**
HttpDownload
NGINX
location / {
rewrite ^/download/(.*) /down.php?path=$1 last;
proxy_pass         http://127.0.0.1:8080/;
proxy_redirect     off;
location /files {
   root /var/www;
   internal;
}
$path = $_GET["path"];
// Perform any required security checks, validation
// and/or stats accounting
// And redirect user to internal location
header("X-Accel-Redirect: /files/" . $path);
response.headers['X-Accel-Redirect'] = '/downloads/myfile.zip'
#Set the Content-Type header as nginx won't change it and Rails will send text/html
response.headers['Content-Type'] = 'application/octet-stream'
#If you want to force download, set the Content-Disposition header (which nginx won't change)
response.headers['Content-Disposition'] = 'attachment; filename=myfile.zip'
The application can also have some control over the process, sending the following headers prior to X-Accel-Redirect.
X-Accel-Limit-Rate: 1024
X-Accel-Buffering: yes|no
X-Accel-Charset: utf-8
*/


// TODO add Download (octet/stream mime) OR Open (original mime) file

class HttpPrivateDownload
{
	public static function optimal($realpath, $uri, $content_length = null)
	{
		if (!$content_length) $content_length = filesize($realpath);
		
		$uri = str_replace(' ','_', $uri);
		$uri = str_replace('%20','_',$uri);
		$uri = str_replace('"','',$uri);
		$uri = str_replace('\'','',$uri);
		$uri = str_replace('*','',$uri);		

		if (OS::clientOS() != 'Mac OS')
		{
			$uri = str_replace('?','',$uri);	
		}

		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false)
			$uri = rawurlencode($uri);
		else
			$uri = str_replace(',','.',$uri);	

		if (OS::isBrowserIE()) $uri = mb_convert_encoding($uri, "Windows-1251", "UTF-8");

		// use best from enabled
		if (USE_NGINX_INTERNAL_REDIRECT === true)
		{
			self::nginx_accel_redirect($realpath, $uri, $content_length);
		}
		else
		{
			self::legacy($realpath, $uri, $content_length);
		}
	}
	
	private static function legacy($realpath, $uri, $content_length)
	{
		if (!file_exists($realpath)) throw new Exception('File for download not found '. $realpath); 
		// TODO on error return real 404 page but custom for every Manager
		// TODO NGINX INTERNAL
		// HTTP::FILE::TRANSFER($realpath)
		
		Log::info("legacy $realpath, $uri, $content_length", 'download');
		
		header('Content-Type: application/octet-stream');

		//header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename='.$uri);
		//header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		//header('Pragma: public');
		header('Content-Length: ' . $content_length); // can be changed by user after share file
		ob_clean();
		flush();
		readfile($realpath);
		exit;
	}

	private static function nginx_accel_redirect($realpath, $uri, $content_length)
	{
		Log::info("nginx_accel_redirect $realpath, $uri, $content_length", 'download');
		
		//$uri = rawurlencode($uri);
		header('Content-Disposition: attachment; filename='.$uri);
		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . $content_length); // can be changed by user after share file
		/*
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		*/
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		//header('Pragma: public');

		header("X-Accel-Redirect: ".$realpath);	
		exit;
	}

	private static function lighttpd_xsendfile($realpath, $uri, $content_length)
	{
	/**
	 X-Sendfile
	*/
	}
	private static function apache2_xsendfile($realpath, $uri, $content_length)
	{
	/**
	https://tn123.org/mod_xsendfile/
	*/
	}
}
?>