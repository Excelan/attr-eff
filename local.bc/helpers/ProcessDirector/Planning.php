<?php

$GLOBALS['DIRECTOR_DMS_Decisions_Plan_Planning'] = function ($mpe, $subjectPrototype)
{
    Log::info('DIRECTOR_DMS_Decisions_Plan_Planning', 'director');
    Log::info('mpe '.$mpe, 'director');
    $subjectURN = new URN($mpe->subject);
    $subject = $subjectURN->resolve();
    Log::info('subject '.$subject, 'director');
    //$post = $subject->initiator; // <<
    Log::info('$mpe->metadata '.json_encode($mpe->metadata), 'director');
    //$parentMPE_UPN = new \URN(str_replace("UPN","urn",$mpe->returntopme)); // metadata->parent
    $parentMPE_ID = explode(":",$mpe->returntopme)[4];
    $parentMPE_URN = new \URN("urn:ManagedProcess:Execution:Record:".$parentMPE_ID);
    $parentMPE = $parentMPE_URN->resolve();
    Log::info('parentMPE '.$parentMPE, 'director');
    Log::info('RESULT +++ $parentMPE->initiator '.$parentMPE->initiator, 'director');
    return (string) $parentMPE->initiator;
};

?>
