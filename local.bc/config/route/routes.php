<?php

$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "index", "app":"Index" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "inboxfeed", "app":"InboxFeed" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "complaint", "app":"Complaint" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "member", "app":"Member" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "requisition", "app":"Requisition" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "investigation", "app":"Investigation" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "capa", "app":"Capa" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "questionnaire", "app":"Questionnaire" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "account", "app":"Account" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "calendar", "app":"Calendar" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "getcomment", "app":"Getcomment" }';
//$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "dmsdecision", "app":"DMSDecision" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "processactorsdirector", "app":"ProcessActorsDirector" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "processrouter", "app":"ProcessRouter" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "capadirector", "app":"CapaDirector" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "tenderdirector", "app":"TenderDirector" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "contractdirector", "app":"ContractDirector" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "uiselectdata", "app":"UISelectData" }';
//$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "remapdecisionsheet", "app":"RemapDecisionSheet" }';
$GLOBALS['CONFIG']['SITEMAP'][] = '{"uri": "universaldocument", "app":"UniversalDocument" }';

foreach ($GLOBALS['CONFIG']['SITEMAP'] as $k=>$n) {
    $GLOBALS['CONFIG']['SITEMAP'][$k] = json_decode($n, true);
}

// Debug gates
$GLOBALS['CONFIG']['GATEROUTING']['Registration/ManagedGate'] = json_decode('{"gate": "Registration/ManagedGate", "type": "internal"}', true);
$GLOBALS['CONFIG']['GATEROUTING']['Registration/deep/DeepGate'] = json_decode('{"gate": "Registration/deep/DeepGate", "type": "internal"}', true);

// Extarnal Python gates
// $GLOBALS['CONFIG']['GATEROUTING']['WMS/CustomDeclaration'] = json_decode('{"gate": "WMS/CustomDeclaration", "type": "external", "host":"localhost", "port":9090}', true);
;
