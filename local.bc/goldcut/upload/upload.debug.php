<?php

$post = print_r($_POST, true);
$files = print_r($_FILES, true);

//print $post;
//print $files;

$date = date("d/m H:i:s");

$filename = dirname(__FILE__).'/../../log/upload.debug.log';

$file = fopen($filename, 'a+');

if (flock($file, LOCK_EX))
{
	fwrite($file, "[{$date}] $post\n$files\n");
	flock($file, LOCK_UN);
}
else
{
	print("Couldn't get the WRITE EX lock for $filename file!");
}

fclose($file);

print json_encode($files);

?>