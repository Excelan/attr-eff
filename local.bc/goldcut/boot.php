<?php

/*
 * php
 * max_execution_time = 60 #this is in seconds
 * nginx
 * proxy_read_timeout 60
 * curl
 * curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
 * curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
 */

if (version_compare(PHP_VERSION, '5.3.0') >= 0)
{
	set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/framework');
	require "goldcut.php";
	try {
		$framework = new Goldcut(__DIR__);
	}
	catch (Exception $e)
	{
		//$error = GCException::ghostBuster($e, $URI);
		print "<pre>";
		print $e;
		print "</pre>";
		exit(503);
	}
}
else
{
	print "PHP 5.3 OR HIGHER REQUIRED";
}

?>