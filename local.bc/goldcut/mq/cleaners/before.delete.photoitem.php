<?php

function before_delete_photoitem($d)
{
	if (!$d) return;
	$files = Photo::filePathes($d);
	foreach ($files as $file)
	{
		dprintln($file,1,TERM_VIOLET);
		if(file_exists($file))
			unlink($file);
	}
}

$broker = Broker::instance();

$broker->queue_declare ("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "before.delete.photoitem");
$broker->bind_rpc ("ENITYUPDATECONSUMER", "before_delete_photoitem");

?>