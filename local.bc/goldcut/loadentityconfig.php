<?php
require "boot.php";
//Log::info(json_encode($_REQUEST), 'GF');

exit(1);
// DEPRECATED

$E = Entity::ref($_REQUEST['entity']);

$file = BASE_DIR.'/config/entity/'.strtolower($E->class).'/'.$E->name.'.xml';

$d = new DOMDocument;
$d->loadXML(file_get_contents($file));

function processConfig($d)
{
	$xpath = new DOMXPath($d);
	$q = "//field";
	$nodes = $xpath->query($q);
	foreach ($nodes as $v)
	{
		$fname = $v->getAttribute('name');
		$F = Field::ref($fname);
		$new = $d->createElement("field");
		$new->setAttribute('name', $fname);
		$new->setAttribute('title', $F->title);
		$new->setAttribute('type', $F->type);

        if ($v->getAttribute('usereditable') == 'yes')
		    $v->parentNode->replaceChild($new, $v);
        else
            $v->parentNode->removeChild($v);
	}

	foreach (array('hasone','useone','hasmany','usemany') as $reltype)
	{
		$xpath = new DOMXPath($d);
		$q = "//".$reltype;
		$nodes = $xpath->query($q);
		foreach ($nodes as $v)
		{
			$ename = $v->getAttribute('entity');

            if ($v->getAttribute('usereditable') != 'yes') $v->parentNode->removeChild($v);

			$E = Entity::ref($ename);
			$din = new DOMDocument;
			$fin = BASE_DIR.'/config/entity/'.strtolower($E->class).'/'.$E->name.'.xml';
			if (file_exists($fin))
			{
				$xmlin = file_get_contents($fin);
				//echo $fin;
				//$f = $v->ownerDocument;
				//$fr = $d->createDocumentFragment();
				//$fr->appendXML($xmlin);
				//$fr->appendXML('<test name="xxx" />');
				//var_dump($fr);
				//$v->appendChild($fr);
				$din->loadXML($xmlin);
				processConfig($din);
				//echo $din->saveXML(); // $d->documentElement
				$inode = $d->importNode($din->documentElement, true);

                $v->appendChild($inode);

				//innerHTML($v, '<test name="xxx" />');
				//innerHTML($v, $xmlin);
			}
		}
	}	
}

processConfig($d);

header ("content-type: text/xml"); 
echo $d->saveXML(); // $d->documentElement

/*
echo '<fields>
<field name="title" title="TITLE" />
<field name="uri" title="Uri" />
</fields>';
*/
?>