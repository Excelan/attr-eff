<?php

$GLOBALS['SAVE_Tender_Tour1_Step1_Tender_Extended'] =
$GLOBALS['SAVE_Tender_Tour1_Step2_Tender_Extended'] =
$GLOBALS['SAVE_Tender_Tour1_Step3_Tender_Extended'] =
    function ($d) {
        $urn = new URN($d->urn);
        $code = $urn->getPrototype()->getOfType();
        Log::info("SAVE_TechnicalTask_Editing_TechnicalTask_{$code} {$d->urn}", 'unisave');

        $subjectURN = $d->urn;

        $m = new Message((array)$d);
        $m->action = "update";
        $savedSubject = $m->deliver();

        foreach ($d->DirectoryTenderBidderSimple as $bidder) {
            $m = new Message((array)$bidder);

            if ($bidder->urn) {
                $m->action = "update";
            } else {
                $m->urn = 'urn:Directory:TenderBidder:Simple';
                $m->action = "create";
                $m->DocumentTenderExtended = (string)$savedSubject->urn;
            }
            $create = $m->deliver();
        }

        $s = new stdClass();
        $s->state = (string)$savedSubject->urn;

        return $s;

    };


$GLOBALS['SAVE_Tender_Tour1_Step4_Tender_Extended'] =
    function ($d) {
        $urn = new URN($d->urn);
        $code = $urn->getPrototype()->getOfType();
        Log::info("SAVE_TechnicalTask_Editing_TechnicalTask_{$code} {$d->urn}", 'unisave');

        $subjectURN = $d->urn;

        $m = new Message((array)$d);
        $m->action = "update";
        $savedSubject = $m->deliver();

        foreach ($d->DirectoryTenderPositionSimple as $position) {
            $m = new Message((array)$position);

            if ($position->urn) {
                $m->action = "update";
            } else {
                $m->urn = 'urn:Directory:TenderPosition:Simple';
                $m->action = "create";
                $m->DocumentTenderExtended = (string)$savedSubject->urn;
            }
            $create = $m->deliver();
        }

        $s = new stdClass();
        $s->state = (string)$savedSubject->urn;

        return $s;

    };

$GLOBALS['SAVE_Tender_Tour2_Step5_Tender_Extended'] =
    function ($d) {

       $urn = new URN($d->urn);

        foreach ($d->DirectoryTenderBidderSimple as $bidder) {
            foreach ($bidder->DocumentTenderTable as $table) {
                $m = new Message();
                $m->action = "load";
                $m->urn = (string)$table->urn;
                $load0 = $m->deliver();

                if ($table->priceoffer != $load0->priceoffer) {
                    if (count($load0->priceofferarray) == 0) {
                        $newprice0 = [];
                    } else {
                        $newprice0 = $load0->priceofferarray;
                    }
                    array_push($newprice0, $table->priceoffer);
                }

                $m = new Message((array)$table);
                $m->action = "update";
                if ($table->priceoffer != $load0->priceoffer) {
                    $m->priceofferarray = $newprice0;
                }
                $tenderTable = $m->deliver();
            }

            foreach ($bidder->DocumentTenderTableAdditional as $table2) {
                $m = new Message();
                $m->action = "load";
                $m->urn = (string)$table2->urn;
                $load = $m->deliver();

                if ($table2->priceoffer != $load->priceoffer) {
                    if (count($load->priceofferarray) == 0) {
                        $newprice = [];
                    } else {
                        $newprice = $load->priceofferarray;
                    }
                    array_push($newprice, $table2->priceoffer);
                }

                $m = new Message((array)$table2);
                $m->action = "update";
                if ($table2->priceoffer != $load->priceoffer) {
                    $m->priceofferarray = $newprice;
                }
                $tenderTable2 = $m->deliver();
            }
        }

        $s = new stdClass();
        $s->state = (string)$savedSubject->urn;

        return $s;

    };







$GLOBALS['LOAD_Tender_Tour1_Step1_Tender_Extended'] =
$GLOBALS['LOAD_Tender_Tour1_Step2_Tender_Extended'] =
$GLOBALS['LOAD_Tender_Tour1_Step3_Tender_Extended'] =

    function ($urn) {

        $m = new Message();
        $m->action = "load";
        $m->order = 'created DESC';
        $m->urn = $urn;

        $loaded = $m->deliver();

        if (count($loaded)) {
            $d = $loaded->current()->toArray(['DirectoryTenderBidderSimple']);

            $json = json_encode($d);

            return $json;
        } else {
            Log::error("No data loaded for $urn", 'uniload');
        }
    };

$GLOBALS['LOAD_Tender_Tour1_Step4_Tender_Extended'] =

    function ($urn) {

        $m = new Message();
        $m->action = "load";
        $m->urn = $urn;
        $loaded = $m->deliver();

        if (count($loaded)) {
            $d = $loaded->current()->toArray(['DirectoryTenderPositionSimple']);

            $json = json_encode($d);
            return $json;
        } else {
            Log::error("No data loaded for $urn", 'uniload');
        }
    };

$GLOBALS['LOAD_Tender_Tour2_Step5_Tender_Extended'] =
$GLOBALS['LOAD_Tender_Decision_Tender_Extended'] =

    function ($urn) {

        $m = new Message();
        $m->action = "load";
        $m->urn = $urn;
        $loaded = $m->deliver();

        if (count($loaded)) {
            //$d = $loaded->current()->toArray(['DirectoryTenderBidderSimple'=>['DocumentTenderTable', 'DocumentTenderTableAdditional']]);
            $d = $loaded->current()->toArray(['DirectoryTenderBidderSimple%passedToSecondTour'=>['DocumentTenderTable', 'DocumentTenderTableAdditional']]);
            //Log::error($d['urn'], 'rznasa');
            $json = json_encode($d);
            return $json;
        } else {
            Log::error("No data loaded for $urn", 'uniload');
        }
    };
