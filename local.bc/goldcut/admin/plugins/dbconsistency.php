<?php 

function deleteByURN($urn)
{
	$m = new Message();
	$m->action = 'delete';
	$m->urn = $urn;
	$m->deliver();
}

$m = new Message();
$m->action = 'load';
$m->urn = 'urn:photoitem';
//$m->last = 10;
$ds = $m->deliver();

printH("Photoitems without parent Photoalbum");
foreach ($ds as $dr)
{
	if (!count($dr->photoalbum))
	{
		print "<img src='{$dr->thumbnail->uri}'>";
		deleteByURN($dr->urn);
		$deletedCount++;
	}
}
if (!$deletedCount) println('None');




$m = new Message();
$m->action = 'load';
$m->urn = 'urn:comment';
//$m->last = 10;
$ds = $m->deliver();

printH("Comment without parent Photoitem or Post");
foreach ($ds as $dr)
{
	if (!count($dr->on))
	{
		println($dr);
		deleteByURN($dr->urn);
		$deletedCount++;
	}
}
if (!$deletedCount) println('None');




$m = new Message();
$m->action = 'load';
$m->urn = 'urn:feed';
//$m->last = 10;
$ds = $m->deliver();

printH("Feed on deleted target");
foreach ($ds as $dr)
{
	$t = new URN($dr->targeturn);
	$to = $t->resolve();
	$tg = (string) $t->generalize();
	switch ($tg) 
	{
		case 'urn:comment':
			if (count($to)) $tot = $to->on;
			break;
		case 'urn:post':
			if (count($to)) $tot = $to;
			break;
		case 'urn:photoitem':
			if (count($to)) $tot = $to->photoalbum;
			break;
	}
	if (!count($to))
	{
		println($dr);
		deleteByURN($dr->urn);
		$deletedCount++;
	}
}
if (!$deletedCount) println('None');

?>