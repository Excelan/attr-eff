<?php

/*
$GLOBALS['DIRECTOR_DMS_Decisions_Approvement_Approve'] = function ($mpe, $subject)
{
    Log::info('? DIRECTOR_DMS_Decisions_Approvement_Approve', 'director');
    return "urn:Actor:User:System:777";
};
*/

// DIRECTOR_ClaimsManagement_Claims_Claim_Editing
// DIRECTOR_ClaimsManagement_Claims_Detective_ProtocolEditing
// DIRECTOR_ClaimsManagement_Claims_Detective_ProtocolExtendRisk
// DIRECTOR_DMS_Decisions_Visa_Decision

/**
$GLOBALS['DIRECTOR_ClaimsManagement_Claims_Claim_Editing'] = function ($mpe, $subject)
{
    Log::info('? DIRECTOR_ClaimsManagement_Claims_Claim_Editing', 'director');

    return "urn:Actor:User:System:1962316964";

};

$GLOBALS['DIRECTOR_ClaimsManagement_Claims_Detective_ProtocolEditing'] = function ($mpe, $subject)
{
    Log::info('? DIRECTOR_ClaimsManagement_Claims_Detective_ProtocolEditing', 'director');

    return "urn:Actor:User:System:1962316964";

};
*/

$GLOBALS['DIRECTOR_FALLBACK'] = function ($mpe, $subjectPrototype) {
    Log::info('? DIRECTOR_FALLBACK', 'director');
    Log::info((string) $mpe->current(), 'director');
    Log::info((string) $subjectPrototype, 'director');

    if (!$mpe) {
        throw new Exception("No mpe");
    }
    if (!$subjectPrototype) {
        throw new Exception("No subject prototype");
    }

    $subjectURN = new \URN($mpe->subject);
    $subjectProto = $subjectURN->getPrototype();
    $m = new \Message();
    $m->action = 'load';
    $m->urn = 'urn:Definition:Prototype:Document';
    $m->indomain = $subjectProto->getInDomain();
    $m->ofclass = $subjectProto->getOfClass();
    $m->oftype = $subjectProto->getOfType();
    $subjDefinitionProto = $m->deliver();
    Log::info((string) $subjDefinitionProto->current(), 'director');

    $processProto = new \Prototype($mpe->prototype);
    $m = new \Message();
    $m->action = 'load';
    $m->urn = 'urn:Definition:ProcessModel:System';
    $m->indomain = $processProto->getInDomain();
    $m->target = $processProto->getOfClass();
    $m->way = $processProto->getOfType();
    $processDefinitionProto = $m->deliver();
    Log::info((string) $processDefinitionProto->current(), 'director');


    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RBAC:DocumentPrototypeResponsible:System';
    $m->processmodelprototype = $processDefinitionProto->urn;
    $m->documentprototype = $subjDefinitionProto->urn;
    $m->stage = $mpe->currentstage;
    Log::info((string) $m, 'director');
    $responsiblePost = $m->deliver();
    if (count($responsiblePost) > 1) {
        foreach ($responsiblePost as $rp) {
            Log::error((string) $rp, 'director');
        }
        throw new Exception("Logic error");
    } elseif (count($responsiblePost) == 1) {
        foreach ($responsiblePost->managementrole as $managementrole) {
            Log::info((string) $managementrole, 'director');
        }
        Log::info('RETURN @ '.$managementrole->urn, 'director');
        return $managementrole->urn;
    } else {
        return "urn:Actor:User:System:0";
    }
};
