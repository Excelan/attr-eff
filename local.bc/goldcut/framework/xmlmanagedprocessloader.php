<?php

class XMLManagedProcessLoader
{
	private static $filepath;

	public static function load($filepath)
	{
		self::$filepath = $filepath;
		$doc = new DOMDocument();
		$doc->load($filepath);
		self::loadEntity($doc);
	}

	private static function loadEntity($doc)
	{
		if (!$doc->documentElement) throw new Exception("Error in config file ".self::$filepath);

        $prototype = $doc->documentElement->getAttribute('prototype');

		$domx = new DOMXPath($doc);
		$entries = $domx->evaluate("//responsibility/stage");
		foreach ($entries as $n)
		{
			$stagename = $n->getAttribute('name');
			$title = $n->getAttribute('title');
			$GLOBALS['MPE'][$prototype][$stagename] = $title;
		}

    }
}

?>
