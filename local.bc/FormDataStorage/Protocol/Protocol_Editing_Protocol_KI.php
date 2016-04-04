<?php

$GLOBALS['LOAD_Protocol_Editing_Protocol_KI'] =  $GLOBALS['LOAD_Protocol_Vising_Protocol_KI']= $GLOBALS['LOAD_Protocol_Approving_Protocol_KI']=function ($urn)
{
    Log::info("LOAD_Protocol_Editing_Protocol_KI", 'uniload');
    Log::debug('LOADED PROMISE FOR '.$urn, 'uniload');

    $m = new Message();
    $m->action = "load";
    $m->urn = $urn;
    $loaded = $m->deliver();

    if (count($loaded))
    {
        $d = $loaded->current()->toArray(['DocumentCapaDeviation'=>['DocumentCorrectionCapa'=>['selectedsolution','DirectorySolutionvariantsSimple']]]);   // 'responsible'=>['CompanyStructureDepartment'] ,'deviations'=>['approvedrisks_unit','notapprovedrisks']

        $json = json_encode($d);
        Log::debug($json, 'uniload');
        return $json;
    }
    else
        Log::error("No data loaded for $urn", 'uniload');
};


$GLOBALS['SAVE_Protocol_Editing_Protocol_KI']=
$GLOBALS['SAVE_Protocol_Vising_Protocol_KI']=
$GLOBALS['SAVE_Protocol_Approving_Protocol_KI'] =
    function ($d)
{
    Log::info("XX SAVE_Protocol_Editing_Protocol_KI", 'rznasa');

    /*
    $m = new Message((array)$d);
    $m->action = "update";
    $m->deliver();
*/

    $protocolUrn = $d->urn;


    foreach($d->DocumentCapaDeviation->DocumentCorrectionCapa as $correnction) {

        //создание, или апдейт результата
        $m = new Message((array)$correnction->DirectorySolutionvariantsSimple);
        if ($correnction->DirectorySolutionvariantsSimple->urn)
            $m->action = "update";
        else {
            $m->urn = 'urn:Directory:Solutionvariants:Simple';
            $m->action = "create";
        }
        $m->DocumentProtocolKI = $protocolUrn;
        $done = $m->deliver();

        //привязка результата к мероприятию
        $m = new Message();
        $m->action = "update";
        $m->urn = (string)$correnction->urn;
        $m->DirectorySolutionvariantsSimple =  (string)$done->urn;
        $m->deliver();

    }


    $state = new stdClass();
    $state->state = $saved->urn;
    return $state;
}
?>
