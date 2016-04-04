<?php

$GLOBALS['DIRECTOR_DMS_Tenders_Tender_Tour1_Step1'] = function ($mpe, $subjectPrototype) {
    Log::info('DIRECTOR_DMS_Tenders_Tender_Tour1_Step1', 'director');
    Log::info('mpe '.$mpe, 'director');
    $subjectURN = new URN($mpe->subject);
    $subject = $subjectURN->resolve();
    Log::info('subject '.$subject, 'director');
    //$post = $subject->initiator; // <<
    Log::info('$mpe->metadata '.json_encode($mpe->metadata), 'director');
    //$parentMPE_UPN = new \URN(str_replace("UPN","urn",$mpe->returntopme)); // metadata->parent



    $urn = new URN((string)$subjectURN);
    $prototype = $urn->getPrototype();

    Log::info('subjectPrototype '.$subjectPrototype, 'director');
    Log::info('prototype '.$prototype, 'director');

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RBAC:DocumentPrototypeResponsible:System';
    // $m->subjectprototype = $prototype;
    $m->documentprototype = $prototype;
    $m->stage = 'Tour1_Step1';
    Log::info('subject '.$m, 'director');
    $role = $m->deliver();



    Log::info('parentMPE '.$role, 'director');
    Log::info('RESULT +++ $parentMPE->initiator '.$role->managementrole->urn, 'director');
    return (string) $role->managementrole->urn;
};
