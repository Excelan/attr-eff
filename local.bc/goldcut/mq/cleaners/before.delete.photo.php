<?php

function before_delete_photo($d)
{
	if (!$d) return;
	
	//dprintln(__FUNCTION__);
	
	$file = $d->file;
	if (count($file))
	{
		$m = new Message();
		$m->action = 'delete';
		$m->urn = $file->urn;
		$m->deliver();
	}
	else
	{
		$oext = ($d->ext) ? $d->ext : 'jpg';
		$original = realpath(BASE_DIR.'/original/'.$d->id.'.'. $oext);
		$files[] = $original;
	}
	
	$file = $d->original;
	$files[] = $file;
	
	foreach ($files as $file)
	{
		//dprintln($file,1,TERM_RED);
		if (file_exists($file))
		{
			unlink($file);
			Log::info("- delete {$file}", 'image');
		}
	}
	
	/**
	$file = $d->file;
	printlnd($file->current(),2);
	println($file->original,1,TERM_YELLOW);
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
$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "before.delete.photo");
$broker->bind_rpc ("ENITYUPDATECONSUMER", "before_delete_photo");

?>