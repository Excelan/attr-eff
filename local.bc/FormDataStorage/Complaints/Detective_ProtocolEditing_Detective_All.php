<?php

$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_IS'] =
$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_IV'] =
$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_IW'] =
$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_LB'] =
$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_LC'] =
$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_LP'] =
$GLOBALS['SAVE_Detective_ProtocolEditing_Detective_C_LT'] =
function ($d)
{


    foreach ($d->deviations as $deviation){

        if(count($deviation->notapprovedrisks) > 0) {
            foreach ($deviation->notapprovedrisks as $notapprovedrisk) {

                $nrc = 0;
                if(strlen(trim($notapprovedrisk->BusinessObjectRecordPolymorph)) == 0){
                    $nrc++;
                }

                if($notapprovedrisk->DirectoryBusinessProcessItem == 'NULL'){
                    $nrc++;
                }

                if($nrc == 2 || $nrc == 0) {
                    $state = new stdClass();
                    $state->state = 200;
                    $state->nextstage = 404;
                    $state->text = "Ошибка! Для не идентифицированного риска выберите или 'Объект' или 'Процесс'";
                    return $state;
                }
            }
        }
    }


    $urn = new URN($d->urn);
    $code = $urn->getPrototype()->getOfType();
    Log::info("SAVE_Detective_ProtocolEditing_Detective_{$code}", 'unisave');
    //Log::debug($d, 'unisave');

    $m = new Message((array)$d);
    $m->action = "update";
    //Log::info($m, 'unisave');
    $savedSubject = $m->deliver();

    $subjectURN = $d->urn;
    formDataManageListOfItemsIn($d, ['commissionmember','checkbo','internaldocuments'], $subjectURN);

    foreach (['commissionmember','checkbo','internaldocuments'] as $key)
    {


        /*
        foreach ($d->$key as $listitem){

          if (!$listitem) continue;
          $listURN = $savedSubject->urn.':'.$key;
          $m = new Message();
          $m->action = 'exists';
          $m->urn = $listitem;
          $m->in = $listURN;
          Log::info($m, 'unisave');
          $e = $m->deliver();
          if (!$e->exists)
          {
              $m = new Message();
              $m->action = 'add';
              $m->urn = $listitem;
              $m->to = $listURN;
              Log::info($m, 'unisave');
              $added = $m->deliver();
          }
          else
              Log::info($e, 'unisave');
        }
        */

    }

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
        Log::info((string)$m, 'unisave');
        $savedDeviation = $m->deliver();
        Log::debug($savedDeviation, 'unisave');

        // если это новое Отклонение, его нужно добавить в список Протокола
        if ($newDeviation == true) {
            $m = new Message();
            $m->action = 'add';
            $m->urn = $savedDeviation->urn;
            $m->to = $savedSubject->urn.':deviations';
            Log::info((string)$m, 'unisave');
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
            Log::info((string)$m, 'unisave');
            $e = $m->deliver();
            if (!$e->exists)
            {
                $m = new Message();
                $m->action = 'add';
                $m->urn = $approvedrisk; //->risk;
                $m->to = $listURN;
                Log::info((string)$m, 'unisave');
                $added = $m->deliver();
                Log::info($added, 'unisave');
            }
            else
                Log::info($e, 'unisave');
        }
        // вложенные в Отклонения Новые Риски
        foreach ($deviation->notapprovedrisks as $notapprovedrisk)
        {
            $listURN = $savedDeviation->urn.':notapprovedrisks';
            Log::debug((string)$notapprovedrisk->urn, 'unisave');
            $m = new Message((array)$notapprovedrisk);
            $m->documentoforigin = (string)$savedSubject->urn; // Новый Риск появился в Протоколе (верхний уровень), а не в Отлонении
            if ($notapprovedrisk->urn)
                $m->action = "update";
            else {
                $m->urn = 'urn:RiskManagement:Risk:NotApproved';
                $m->action = "create";
            }
            Log::info((string)$m, 'unisave');
            $narisk = $m->deliver();
            //Log::debug($narisk, 'unisave');
            if ($m->action == "create")
            {
                $m = new Message();
                $m->action = 'add';
                $m->urn = $narisk->urn;
                $m->to = $listURN;
                Log::info((string)$m, 'unisave');
                $added = $m->deliver();
                //Log::debug($added, 'unisave');
            }
        }
    }

    $s = new stdClass();
    $s->state = (string)$savedSubject->urn;

    return $s;

};


$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_IS'] =
$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_IV'] =
$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_IW'] =
$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_LB'] =
$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_LC'] =
$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_LP'] =
$GLOBALS['LOAD_Detective_ProtocolEditing_Detective_C_LT'] =
function ($urn)
{
    Log::info((string)$urn, 'uniload');
    $code = $urn->getPrototype()->getOfType();
    Log::info((string)$code, 'uniload');
    Log::info("LOAD_Detective_ProtocolEditing_Detective_{$code}", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']]);

        //$DocumentComplaint_Par_URN = new URN(key($d['DocumentComplaint'.$code]));
        $DocumentComplaint_Par_URN = new URN($d['DocumentComplaint'.$code]['urn']);
        $DocumentComplaintPar = $DocumentComplaint_Par_URN->resolve()->current();
        $d['CONTEXT_DocumentComplaint'.$code] = $DocumentComplaintPar->toArray();

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};

?>
