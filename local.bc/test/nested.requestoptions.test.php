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

class NestedQueryOptionTest implements TestCase
{
    public function doitFilterNamed()
    {
        $m = new Message();
        $m->action = "load";
        $m->urn = 'urn:Document:Tender:Extended:445726024';
        $loaded = $m->deliver();

        if (count($loaded)) {
            //$d = $loaded->current()->toArray(['DirectoryTenderBidderSimple%passedToSecondTour'=>['DocumentTenderTable', 'DocumentTenderTableAdditional']]);
          // $d = $loaded->current()->toArray(['DirectoryTenderBidderSimple%passedToSecondTour']);
          $d = $loaded->current()->toArray(['DirectoryTenderBidderSimple%passedToSecondTour'=>['DocumentTenderTable']]);
          //$d = $loaded->current()->toArray(['DirectoryTenderBidderSimple'=>['DocumentTenderTable']]);
            println($d);
            println($d['DirectoryTenderBidderSimple']);
            foreach ($d['DirectoryTenderBidderSimple'] as $bidder) {
                println($bidder, 2);
                println($bidder['DocumentTenderTable'], 2);
                println($bidder['biddersolution'], 3);
            }
        } else {
            Log::error("No data loaded for $urn", 'uniload');
        }
    }

    public function doitSimple()
    {
        $m = new Message();
        $m->action = "load";
        $m->urn = 'urn:Document:Capa:Deviation:525629863';
        $loaded = $m->deliver();

        if (count($loaded)) {
            $d = $loaded->current()->toArray(['RiskManagementRiskApproved_unit', 'RiskManagementRiskNotApproved', 'DocumentCorrectionCapa']);
          //$d = $loaded->current()->toArray(['DirectoryTenderBidderSimple'=>['DocumentTenderTable']]);
            println($d);

            println($d['RiskManagementRiskNotApproved'], 1, TERM_YELLOW);
            foreach ($d['RiskManagementRiskNotApproved'] as $risk) {
                println($risk, 2);
            }

            println($d['DocumentCorrectionCapa'], 1, TERM_VIOLET);
            foreach ($d['DocumentCorrectionCapa'] as $mer) {
                println($mer, 2);
            }
        } else {
            Log::error("No data loaded for $urn", 'uniload');
        }
    }
}
