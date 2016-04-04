<?php 

$m = new Message();
$m->action = 'load';
$m->urn = 'urn:news';
//$m->last = 3;
$feed = $m->deliver();

foreach ($feed as $p)
{
	print('<h4>'.$p->title.'</h4>');
	//print htmlentities($p->text);
	//print ($p->text);
	
	/* Omit libxml warnings */
	libxml_use_internal_errors(true);
	$doc = new DOMDocument('1.0', 'UTF-8');
	$doc->formatOutput = true;
	$doc->preserveWhiteSpace = false;
	$doc->loadHTML('<?xml encoding="UTF-8">' . $p->text);
	foreach ($doc->childNodes as $item)
		if ($item->nodeType == XML_PI_NODE)
			$doc->removeChild($item); // remove hack
	$doc->encoding = 'UTF-8'; 
	
	$images = $doc->getElementsByTagName("img");
	for ($i = 0; $i < $images->length; $i++) 
	{
		$imgURI = $images->item($i)->attributes->getNamedItem('src')->nodeValue;
		$oldSymbPos = strpos($imgURI,'_');
		if (!$oldSymbPos) continue;
		$cut = $oldSymbPos - 11; 
		$uuid = substr($imgURI,11, $cut);
		$imgURN = new URN('urn:img-'.$uuid);
		$img = $imgURN->resolve();
		//println($img->folder,1,TERM_GRAY);
		$newImgUri = $img->image->uri;
		println($newImgUri,2,TERM_VIOLET);
		$images->item($i)->setAttribute('src', $newImgUri);
		$images->item($i)->setAttribute('alt', '');
		
		$m = new Message();
		$m->action = 'update';
		$m->urn = $img->urn;
		$m->isused = true;
		$m->deliver();
	}
	print '<hr>';
	$xhtml = $doc->saveXML($doc->documentElement->firstChild);
	$html = substr($xhtml,6,-7); // remove <body>HTML</body>
	$html = str_replace("&#13;", '', $html);
	//print htmlentities($html);
	//$htmlTextOnly = $doc->getElementsByTagName("body")->item(0)->textContent;
	//print $html;
	$m = new Message();
	$m->action = 'update';
	$m->urn = $p->urn;
	$m->text = $html;
	$m->deliver();
}
?>