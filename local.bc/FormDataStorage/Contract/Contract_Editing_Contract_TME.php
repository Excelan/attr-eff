<?php

$GLOBALS['LOAD_Contract_Editing_Contract_TME']=$GLOBALS['LOAD_Contract_Considering_Contract_TME']=$GLOBALS['LOAD_Contract_Decision_Contract_TME']=$GLOBALS['LOAD_Contract_Approving_Contract_TME'] = function ($urn)
{
  Log::info("LOAD_Contract_Editing_Contract_TME", 'uniload');
  Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

  $m = new Message();
  $m->action = "load";
  $m->urn = $urn;
  $loaded = $m->deliver();

  if (count($loaded))
  {
    $d = $loaded->current()->toArray(['notifyusercompany_unit', 'notifyusercounterparty', 'contractapplication' => ['MediaAttributed']]);   // 'responsible'=>['CompanyStructureDepartment'] ,'deviations'=>['approvedrisks_unit','notapprovedrisks']
    $json = json_encode($d);
    Log::debug($json, 'uniload');
    return $json;
  }
  else
  Log::error("No data loaded for $urn", 'uniload');
};



$GLOBALS['SAVE_Contract_Editing_Contract_TME'] =
$GLOBALS['SAVE_Contract_Considering_Contract_TME']=
$GLOBALS['SAVE_Contract_Decision_Contract_TME']=
$GLOBALS['SAVE_Contract_Approving_Contract_TME'] =
    function ($d)
    {
        $m = new Message();
        $m->urn = $d->urn;
        $m->place = $d->place;
        $m->date = $d->date;
        $m->timecontract = $d->timecontract;
        $m->prolongation = $d->prolongation;
        $m->timenotifyfor = $d->timenotifyfor;
        $m->summ = $d->summ;

        if($d->DirectoryBusinessProjectsItem)$m->DirectoryBusinessProjectsItem = $d->DirectoryBusinessProjectsItem;
        if($d->CompanyLegalEntityCounterparty)$m->CompanyLegalEntityCounterparty = $d->CompanyLegalEntityCounterparty;
        if($d->BusinessObjectRecordPolymorph)$m->BusinessObjectRecordPolymorph = $d->BusinessObjectRecordPolymorph;
        if($d->CompanyStructureCompanygroup)$m->CompanyStructureCompanygroup = $d->CompanyStructureCompanygroup;

        if($d->tenderdoc)$m->tenderdoc = $d->tenderdoc;

        $m->justification = $d->justification;
        $m->attachments = $d->attachments;


        $m->introduction = $d->introduction;
        $m->contractsubject = $d->contractsubject;
        $m->orderofworksexecution = $d->orderofworksexecution;
        $m->costofworks = $d->costofworks;
        $m->partyliabilities = $d->partyliabilities;
        $m->responsibilityofpartie = $d->responsibilityofpartie;
        $m->timeofcontracts = $d->timeofcontracts;
        $m->final = $d->final;


        $m->action = 'update';


        $contact_saved = $m->deliver();



        $subjectURN = $d->urn;
        formDataManageListOfItemsIn($d, ['notifyusercompany'], $subjectURN);





        if($d->notifyusercounterparty != 'NULL') {


            formDataManageListOfItemsIn($d, ['notifyusercounterparty'], $subjectURN);
            /*
            foreach ((array)$d->notifyusercounterparty as $onepost) {

                $listURN2 = (string)$contact_saved->urn . ':notifyusercounterparty';

                $m = new Message();
                $m->action = 'exists';
                $m->urn = $onepost;
                $m->in = $listURN2;
                $e2 = $m->deliver();

                Log::info($onepost, 'rznasa');

                if (!$e2->exists) {
                    $m = new Message();
                    $m->action = 'add';
                    $m->urn = $onepost;
                    $m->to = $listURN2;
                    $added = $m->deliver();
                } else
                    Log::info($e2, 'unisave');

            }
            */
        }






        foreach ($d->contractapplication as $contractapp){

            $m = new Message((array)$contractapp);
            if ($contractapp->urn)
                $m->action = "update";
            else {
                $m->urn = 'urn:Document:ContractApplication:Universal';
                $m->action = "create";
            }
            $conapp_saved = $m->deliver();

            $listURN3 = $contact_saved->urn.':contractapplication';

            $m = new Message();
            $m->action = 'exists';
            $m->urn = $conapp_saved->urn;
            $m->in = $listURN3;
            $e3 = $m->deliver();

            if (!$e3->exists){

                $m = new Message();
                $m->action = 'add';
                $m->urn = $conapp_saved->urn;
                $m->to = $listURN3;
                $m->deliver();
            }else Log::info($e3, 'unisave');

            foreach ($contractapp->MediaAttributed as $MediaAttributed){

                Log::info($MediaAttributed, 'rznasa');
                $m = new Message((array)$MediaAttributed);
                if ($MediaAttributed->urn)
                    $m->action = "update";
                else {
                    $m->urn = 'urn:Directory:Media:Attributed';
                    $m->action = "create";
                }
                $saved_mediaattr = $m->deliver();

                $listURN4 = $conapp_saved->urn.':MediaAttributed';

                $m = new Message();
                $m->action = 'exists';
                $m->urn = $saved_mediaattr->urn;
                $m->in = $listURN4;
                $e4 = $m->deliver();

                if (!$e4->exists){
                    $m = new Message();
                    $m->action = 'add';
                    $m->urn = $saved_mediaattr->urn;
                    $m->to = $listURN4;
                    $m->deliver();
                }else Log::info($e4, 'unisave');

            }

        }


          $state = new stdClass();
          $state->state = $contact_saved->urn;
          return $state;
}
?>
