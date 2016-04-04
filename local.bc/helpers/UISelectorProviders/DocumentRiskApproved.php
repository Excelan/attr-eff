<?php

$GLOBALS['UISELECTOR_METADATA_RiskManagementRiskApproved'] = function ()
{
    $metadata = [
        "fields"=> [
            ['name'=> "title", 'title'=> "Название/Описание риска", 'type'=> "string"],
            ['name'=> "objproc", 'title'=> "Объект / Процесс", 'type'=> "string"],
            ['name'=> "resp", 'title'=> "Ответственный за контроль риска", 'type'=> "string"]
        ]
    ];
    return $metadata;
};

$GLOBALS['UISELECTOR_DATA_RiskManagementRiskApproved'] = function ()
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RiskManagement:Risk:Approved';
    $m->order = 'title ASC';
    $ds = $m->deliver();

    $data = [];
    foreach ($ds as $dr)
    {
        $bo = $dr->BusinessObjectRecordPolymorph;
        $process = $dr->DirectoryBusinessProcessItem;
        if (count($bo)) $objproc = $bo->title;
        elseif (count($process)) $objproc = $process->title;
        else $objproc = '-';
        array_push($data, ['urn'=>(string)$dr->urn,
            'title'=>$dr->title,
            'objproc'=>$objproc,
            'resp'=>$dr->ManagementPostIndividual->title
        ]);
    }
    return $data;
};

?>
