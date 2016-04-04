<?php

function before_delete_file($d)
{
	//dprintln(__FUNCTION__);
	
	if (!$d) return;
	
	//printlnd($d,2);
	//dprintln($d->original,1,TERM_YELLOW);
	$file = $d->original;
	if (file_exists($file))
	{
		unlink($file);
		Log::info("- delete {$file}", 'image');
	}
	
	/**
	$files = Photo::filePathes($d);
	foreach ($files as $file)
	{
		dprintln($file,1,TERM_VIOLET);
		if(file_exists($file))
			unlink($file);
	}
	*/
}

$broker = Broker::instance();

$broker->queue_declare ("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "before.delete.file");
$broker->bind_rpc ("ENITYUPDATECONSUMER", "before_delete_file");

?>