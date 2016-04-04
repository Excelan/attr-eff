<?php
require dirname(__FILE__).'/../../../goldcut/boot.php';
// Deprecated
exit(1);

//define('NOMIGRATE',TRUE);
//define('DEBUG_SQL',TRUE);
define('PRODUCTION_DB_IN_TEST_ENV',TRUE);
//define('CLEAR_IMAGE_CACHE',TRUE);

class ProductionLoad implements TestCase
{

	public $fixtures = true; // load all fixtures
	public $messages = true; // load all action messages after fixtures

}
?>