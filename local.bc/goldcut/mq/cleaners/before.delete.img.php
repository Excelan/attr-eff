<?php

function before_delete_img($d)
{
	if (!$d) return;
	
	//dprintln(__FUNCTION__);
	
	$photo = $d->photo;
	
	$m = new Message();
	$m->action = 'delete';
	$m->urn = $photo->urn;
	$m->deliver();
	
	// TODO remove /media, /thumb
	
	/**
	$file = $photo->file;
	//printlnd($photo->current(),2);
	println($photo->original,2);
	//printlnd($file->current(),2);
	println($file->original,1,TERM_YELLOW);
	
	$files = Photo::filePathes($d);
	foreach ($files as $file)
	{
		dprintln($file,1,TERM_VIOLET);
		if(file_exists($file))
		{
			unlink($file);
			print '.';
		}
	}
	*/
}

$broker = Broker::instance();

$broker->queue_declare ("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "before.delete.img");
$broker->bind_rpc ("ENITYUPDATECONSUMER", "before_delete_img");

?>