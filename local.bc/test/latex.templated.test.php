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

class LatexTemplatedTest implements TestCase
{

    function readlData()
    {
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:Prototype:System';
        $m->withhardcopy = true;
        $ps = $m->deliver();

        foreach ($ps as $proto)
        {
            if (!in_array("{$proto->ofclass}:{$proto->oftype}", ['Contract:TME','Regulations:SOP'])) continue;
            $m = new Message();
            $m->action = 'load';
            $m->urn = "urn:{$proto->indomain}:{$proto->ofclass}:{$proto->oftype}";
            $m->order = ['created' => 'DESC'];
            $m->last = 1;
            $subject = $m->deliver();
            if (count($subject))
            {
                try {
                $urn = $subject->urn;
                $pdfLatex = LatexUtils::buildLatexPDF($urn);
                $latex = $pdfLatex[0];
                $pdfURI = $pdfLatex[1];
                print "<a style='color: white;' href='$pdfURI'>DOWNLOAD RESULT PDF {$m->urn} {$proto->title}</a>";
                if ($_GET['latex']) print '<pre>'.$latex.'</pre>';
                }
                catch (Exception $e)
                {
                    println($e->getMessage(),1,TERM_RED);
                }
            }
        }
    }
}
?>
