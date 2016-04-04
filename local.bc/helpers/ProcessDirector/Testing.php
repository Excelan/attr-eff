
<?php

$GLOBALS['DIRECTOR_DMS_Attestations_Test_Testing'] = function ($mpe, $subjectPrototype) {

    Log::info('DIRECTOR_DMS_Attestations_Test_Testing', 'director');
//    Log::info('mpe '.$mpe, 'director');
//    $subjectURN = new URN($mpe->subject);
//    $subject = $subjectURN->resolve();
//    Log::info('subject '.$subject, 'director');
//    //$post = $subject->initiator; // <<
//    Log::info('$mpe->metadata '.json_encode($mpe->metadata), 'director');
//    //$parentMPE_UPN = new \URN(str_replace("UPN","urn",$mpe->returntopme)); // metadata->parent
//
//
//
//    $urn = new URN((string)$subjectURN);
//    $prototype = $urn->getPrototype();
//
//    Log::info('subjectPrototype '.$subjectPrototype, 'director');
//    Log::info('prototype '.$prototype, 'director');

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:People:Employee:Internal';
    $m->istrener = 1;
    $employee = $m->deliver();

    if(count($employee) > 1) throw new Exception("too much istrener");


    Log::info('RESULT +++ $parentMPE->initiator '.$employee->ManagementPostIndividual->urn, 'director');
    return (string) $employee->ManagementPostIndividual->urn;
};
