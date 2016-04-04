<?php 

$langs = array('en');

$m = new Message();
$m->urn = 'urn:photoitem';
$m->action = 'load';
$m->lang = 'ru';
$rus = $m->deliver();

foreach ($rus as $ru)
{
	//if (!$ru->is_translated('en')) println($ru);
	foreach ($langs as $lang)
	{
		if (!$ru->is_translated($lang))
		{
			$m = new Message();
			$m->action = 'translate';
			$m->lang = $lang;
			$m->urn = $ru->urn;
			$m->title = '  ';
			$m->deliver();
			println("{$ru->title} translated to $lang");
		}
	}
}
?>