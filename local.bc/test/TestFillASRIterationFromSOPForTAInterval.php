<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB',TRUE);
define('PRODUCTION_DB_IN_TEST_ENV',TRUE);

class testFillASRIterationFromSOPForTAInterval implements TestCase
{
  private $mpeurn;

  function gateTest()
  {
    $m = new \Message();
    $m->gate = 'Process/Study/fillASRIterationFromSOPForTAInterval';
    $m->mpeId = 484922; // Attestation mpe id
    $r = $m->send();
    println($r);
  }

}
