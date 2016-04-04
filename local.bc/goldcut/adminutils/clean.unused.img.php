<?php


$m = new Message();
$m->action = 'load';
$m->urn = 'urn:news';
//$m->last = 3;
$feed = $m->deliver();
foreach ($feed as $p)
{
	//print('<h4>'.$p->title.'</h4>');
	$doc = new DOMDocument('1.0', 'UTF-8');
	$doc->loadHTML('<?xml encoding="UTF-8">' . $p->text);
	$images = $doc->getElementsByTagName("img");
	for ($i = 0; $i < $images->length; $i++) 
	{
		$imgURI = $images->item($i)->attributes->getNamedItem('src')->nodeValue;
		$uric = explode('/',$imgURI);
		//printlnd($uric);
		$uuid = $uric[3];
		if (!$uuid) continue;
		$imgURN = new URN('urn:img-'.$uuid);
		$img = $imgURN->resolve();
		//println($img->current());
		$m = new Message();
		$m->action = 'update';
		$m->urn = $img->urn;
		$m->isused = true;
		$m->deliver();
		print '+';
	}
}



$m = new Message();
$m->action = 'load';
$m->urn = 'urn:img';
$m->isused = false;
$feed = $m->deliver();

printH(count($feed)." unused in urn-img");

foreach ($feed as $img)
{
	println($img);
	$m = new Message();
	$m->action = 'delete';
	$m->urn = $img->urn;
	$m->deliver();
}

?>