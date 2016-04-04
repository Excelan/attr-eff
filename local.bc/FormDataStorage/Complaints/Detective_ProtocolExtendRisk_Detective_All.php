<?php

$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_IS'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_IS'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_IV'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_IW'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_LB'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_LC'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_LP'] =
$GLOBALS['SAVE_Detective_ProtocolExtendRisk_Detective_C_LT'] =
function ($d)
{
    $urn = new URN($d->urn);
    $code = $urn->getPrototype()->getOfType();
    Log::info("SAVE_Detective_ProtocolExtendRisk_Detective_{$code}", 'unisave');

    $savedSubject = new stdClass();
    $savedSubject->urn = $d->urn;

    $subjectURN = $d->urn;

    foreach ($d->deviations as $deviation)
    {
        // вложенные Отклонения
        //Log::debug($deviation, 'unisave');
        $newDeviation = false;
        $m = new Message((array)$deviation);
        if ($deviation->urn)
            $m->action = "update";
        else {
            $m->urn = "urn:Directory:Deviation:PreCapa";
            $m->action = "create";
            $newDeviation = true;
        }
        Log::info($m, 'unisave');
        $savedDeviation = $m->deliver();
        Log::debug($savedDeviation, 'unisave');

        // если это новое Отклонение, его нужно добавить в список Протокола
        if ($newDeviation == true) {
            $m = new Message();
            $m->action = 'add';
            $m->urn = $savedDeviation->urn;
            $m->to = $savedSubject->urn.':deviations';
            $added = $m->deliver();
            Log::debug('created devaiation added to list '.$added, 'unisave');
        }
        else
        {
            Log::debug('Old devaiation, not need to add to list', 'unisave');
        }

        // вложенные в Отклонения Риски
        foreach ($deviation->approvedrisks as $approvedrisk)
        {
            //if (!$approvedrisk->risk) continue;
            //Log::debug($approvedrisk, 'unisave');
            $listURN = $savedDeviation->urn.':approvedrisks';
            $m = new Message();
            $m->action = 'exists';
            $m->urn = $approvedrisk; //->risk;
            $m->in = $listURN;
            Log::info($m, 'unisave');
            $e = $m->deliver();
            if (!$e->exists)
            {
                $m = new Message();
                $m->action = 'add';
                $m->urn = $approvedrisk; //->risk;
                $m->to = $listURN;
                Log::info($m, 'unisave');
                $added = $m->deliver();
                Log::info($added, 'unisave');
            }
            else
                Log::info($e, 'unisave');
        }

        // TODO
        // вложенные в Отклонения Новые Риски
        foreach ($deviation->notapprovedrisks as $notapprovedrisk)
        {
            $listURN = $savedDeviation->urn.':notapprovedrisks';
            //Log::debug($notapprovedrisk, 'unisave');
            $m = new Message((array)$notapprovedrisk);
            $m->documentoforigin = (string)$savedSubject->urn; // Новый Риск появился в Протоколе (верхний уровень), а не в Отлонении
            if ($notapprovedrisk->urn)
                $m->action = "update";
            else {
                $m->urn = 'urn:RiskManagement:Risk:NotApproved';
                $m->action = "create";
            }
            $narisk = $m->deliver();
            // TODO add if created
            Log::info($m, 'unisave');
            //if (!$e->exists)
            {
                $m = new Message();
                $m->action = 'add';
                $m->urn = $narisk->urn;
                $m->to = $listURN;
                Log::info($m, 'unisave');
                $added = $m->deliver();
                //Log::info($added, 'unisave');
            }
        }
    }

    $s = new stdClass();
    $s->state = (string)$savedSubject->urn;

    return $s;

};




$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_IS'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_IS'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_IV'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_IW'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_LB'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_LC'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_LP'] =
$GLOBALS['LOAD_Detective_ProtocolExtendRisk_Detective_C_LT'] =
function ($urn)
{
    Log::info((string)$urn, 'uniload');
    $code = $urn->getPrototype()->getOfType();
    Log::info((string)$code, 'uniload');
    Log::info("LOAD_Detective_ProtocolExtendRisk_Detective_{$code}", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']]);

        /*
        $DocumentComplaint_Par_URN = new URN(key($d['DocumentComplaint'.$code]));
        $DocumentComplaintPar = $DocumentComplaint_Par_URN->resolve()->current();
        $d['CONTEXT_DocumentComplaint'.$code] = $DocumentComplaintPar->toArray();
        */

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

?>
