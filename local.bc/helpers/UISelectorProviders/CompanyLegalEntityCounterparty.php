<?php

$GLOBALS['UISELECTOR_METADATA_CompanyLegalEntityCounterparty'] = function ()
{
    $metadata = [
        "fields"=> [
            ['name'=> "title", 'title'=> "Название контрагента", 'type'=> "string"],
            ['name'=> "isactive", 'title'=> "Действующий/проектный", 'type'=> "unit"],
            ['name'=> "warehouse", 'title'=> "Номер склада", 'type'=> "string"],
            ['name'=> "isclient", 'title'=> "Клиент", 'type'=> "unit"],
            ['name'=> "iscontractor", 'title'=> "Подрядчик", 'type'=> "unit"],
            ['name'=> "BusinessArea", 'title'=> "Направление деятельности", 'type'=> "unit"]
        ]
    ];
    return $metadata;

};

$GLOBALS['UISELECTOR_DATA_CompanyLegalEntityCounterparty'] = function ()
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Company:LegalEntity:Counterparty';
    $m->order = 'title ASC';
    $ds = $m->deliver();

    $data = [];
    foreach ($ds as $dr)
    {
        $BusinessArea = $dr->BusinessArea;
        if (count($BusinessArea)) {
            $baUnit = [(string)$BusinessArea->urn => $BusinessArea->title];
        }

        $warehouseTitle = '--';
        $warehouse = $dr->BusinessObjectRecordPolymorph;
        if (count($warehouse)) $warehouseTitle = $warehouse->title;

        $isactive = $dr->isactive ? 'Действующий' : 'Проектный';
        $isclient = ($dr->isclient ? 'Клиент' : '-');
        $iscontractor = ($dr->iscontractor ? 'Подрядчик' : '-');

        array_push($data, ['urn'=>(string)$dr->urn,
            'title'=>$dr->title,
            'isactive'=> [$isactive => $isactive],
            'isclient'=> [$isclient => $isclient],
            'iscontractor'=> [$iscontractor => $iscontractor],
//            'isactive' => $dr->isactive ? 'Действующий' : 'Проектный',
//            'isclient' => $dr->isclient ? 'Клиент' : '',
//            'iscontractor'=> $dr->iscontractor ? 'Подрядчик' : '',
            'warehouse' => $warehouseTitle,
            'BusinessArea' => $baUnit
        ]);
    }
    return $data;
};
?>