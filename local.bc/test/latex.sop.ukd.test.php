<?php
require dirname(__FILE__).'/../goldcut/boot.php';
define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB', true);
define('PRODUCTION_DB_IN_TEST_ENV', true);

class LatexSOPTest implements TestCase
{

    public function sopUKDPDF()
    {
        try {
            $urn = new URN('urn:Document:Regulations:SOP:46420535');
            $pdfLatex = LatexUtils::buildLatexPDF($urn, ['copyid'=>'1234567', 'barcode'=>'1bar128.png']);
            $latex = $pdfLatex[0];
            $pdfURI = $pdfLatex[1];
            println(BASE_DIR);
            print "<a style='color: white;' href='$pdfURI'>DOWNLOAD RESULT PDF {$m->urn} {$proto->title}</a>";
            if ($_GET['latex']) {
                print '<pre>'.$latex.'</pre>';
            }
        } catch (Exception $e) {
            println($e->getMessage(), 1, TERM_RED);
        }
    }

    //
    public function contractPDF()
    {
        pendingTest();
        try {
            $urn = new URN('urn:Document:Contract:LC:583553747');
            $pdfLatex = LatexUtils::buildLatexPDF($urn);
            $latex = $pdfLatex[0];
            $pdfURI = $pdfLatex[1];
            println(BASE_DIR);
            print "<a style='color: white;' href='$pdfURI'>DOWNLOAD RESULT PDF {$m->urn} {$proto->title}</a>";
            if ($_GET['latex']) {
                print '<pre>'.$latex.'</pre>';
            }
        } catch (Exception $e) {
            println($e->getMessage(), 1, TERM_RED);
        }
    }
}
