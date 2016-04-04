<?php
require dirname(__FILE__).'/../../../goldcut/boot.php';
//define('DEBUG_SQL',TRUE);
//define('NOMIGRATE',TRUE);

class EListTest implements TestCase
{

	function list_entities()
	{
		EntityDump::report();
	}

}
?>