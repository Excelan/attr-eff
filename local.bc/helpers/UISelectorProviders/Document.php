<?php

$GLOBALS['UISELECTOR_METADATA_Document'] = function ()
{
    $metadata = [
        "fields"=> [
          ['name'=> "title", 'title'=> "Название", 'type'=> "string"],
            ['name'=> "class", 'title'=> "Класс", 'type'=> "unit"],
            ['name'=> "type", 'title'=> "Тип", 'type'=> "unit"],
            ['name'=> "code", 'title'=> "Код", 'type'=> "string"],
            ['name'=> "dateapproved", 'title'=> "Дата утверждения", 'type'=> "date"]
            //['name'=> "responsible", 'title'=> "Ответственный", 'type'=> "string"]
        ]
    ];
    return $metadata;
};

$GLOBALS['UISELECTOR_DATA_Document'] = function ()
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:DMS:Document:Universal';
    $m->order = 'created ASC';
    $ds = $m->deliver();

    $data = [];
    foreach ($ds as $document)
    {
        //$documentUrn = new URN($dr->subject);
        //$document = $documentUrn->resolve();

        // DefinitionPrototypeSystem
        $classUnit = [$document->urn->getPrototype()->getOfClass() => $document->urn->getPrototype()->getOfClass()];
        $typeUnit = [$document->urn->getPrototype()->getOfType() => $document->urn->getPrototype()->getOfType()];
        //

        array_push($data, ['urn'=> (string)$document->urn,
            'title'=> $document->title,
            'class'=> $classUnit,
            'type'=> $typeUnit,
            'code'=> $document->code,
            //'resp'=> $document->initiator,
            'dateapproved'=> '-',
        ]);
    }
    return $data;
};

?>
