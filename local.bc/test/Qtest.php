<?php
require dirname(__FILE__).'/../goldcut/boot.php';
define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB',TRUE);
define('PRODUCTION_DB_IN_TEST_ENV',TRUE);

class qtest implements TestCase
{

    function readlData()
    {


        $m = new Message();
        $m->action = 'update';
        $m->urn = 'urn:Document:Correction:Capa:1964498246';

        $m->descriptioncorrection = '--------------';
        $m->realizationtype = '0';
        //$m->realizationtype = 0;
        //$m->realizationtype = null;
        $m->comment = '';

        Log::info($m,'rznasa');

        $done = $m->deliver();

        Log::info($done,'rznasa');


    }

}
?>
