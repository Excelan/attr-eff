<?php
function before_delete_photoalbum($d)
{
	if (!$d) return;
	foreach ($d->photoitem as $photoitem)
	{
		$m = new Message();
		$m->action = 'delete';
		$m->urn = $photoitem->urn;
		$m->deliver();
	}
}

$broker = Broker::instance();

$broker->queue_declare ("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "before.delete.photoalbum");
$broker->bind_rpc ("ENITYUPDATECONSUMER", "before_delete_photoalbum");

?>