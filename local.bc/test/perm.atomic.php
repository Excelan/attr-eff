<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB', true);
define('PRODUCTION_DB_IN_TEST_ENV', true);

set_time_limit(10);

class PermTest implements TestCase
{
    private $urn;

    public function t1()
    {
    }
}
