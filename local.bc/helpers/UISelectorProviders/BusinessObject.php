<?php

$GLOBALS['UISELECTOR_METADATA_BusinessObject'] = function ()
{
    $metadata = [
        "fields"=> [
            ['name'=> "title", 'title'=> "Название объекта", 'type'=> "string"],
            ['name'=> "boc", 'title'=> "Класс объекта", 'type'=> "unit"],
            ['name'=> "bot", 'title'=> "Тип объекта", 'type'=> "unit"],
            ['name'=> "place", 'title'=> "Местонахождение объекта", 'type'=> "string"],
            ['name'=> "resp", 'title'=> "Материально-ответственный сотрудник", 'type'=> "string"],
            ['name'=> "invid", 'title'=> "Инвентарный номер", 'type'=> "string"],
            ['name'=> "manuf", 'title'=> "Производитель объекта", 'type'=> "string"]
        ]
    ];
    return $metadata;
};

$GLOBALS['UISELECTOR_DATA_BusinessObject'] = function ($mpe=null)
{
    Log::info((string)$mpe, 'ui');

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:BusinessObject:Record:Polymorph';

    if ($mpe)
    {
      $mpeURN = new \URN($mpe);
  		$mpe = $mpeURN->resolve();
      $subjectURN = new \URN($mpe->subject);
  		$subject = $subjectURN->resolve();
      $client = $subject->CompanyLegalEntityCounterparty;
      $m->boofclient = $client->urn;
      Log::info("BY CLIENT BO!!! ".'$m->boofclient = $client->urn;', 'richselect');
    }
    Log::info((string)$m, 'ui');
    $m->order = 'title ASC';
    $ds = $m->deliver();

    $data = [];
    foreach ($ds as $dr)
    {
        //
        $locationTitle = '-';
        $location = $dr->location;
        if (count($location)) $locationTitle = $location->title;
        //
        $boc = $dr->DefinitionClassBusinessObject;
        if (count($boc))
        {
            Log::debug("dr->DefinitionClassBusinessObject {$boc}", 'richselect');
            $bocUnit = [(string)$boc->urn => $boc->title];
        }
        else
        {
            Log::error("No dr->DefinitionClassBusinessObject", 'richselect');
            continue;
        }
        //
        $bot = $dr->DefinitionTypeBusinessObject;
        if (count($bot))
        {
            Log::debug("dr->DefinitionTypeBusinessObject {$bot}", 'richselect');
            $botUnit = [(string)$bot->urn => $bot->title];
        }
        else
        {
            Log::error("No dr->DefinitionTypeBusinessObject", 'richselect');
            continue;
        }

        array_push($data, ['urn'=> (string)$dr->urn,
            'title'=> $dr->title,
            'boc'=> $bocUnit,
            'bot'=> $botUnit,
            'place'=> $locationTitle,
            'resp'=> $dr->MateriallyResponsible->title,
            'invid'=> $dr->inventorynumber,
            'manuf'=> $dr->maker
        ]);
    }
    return $data;
};


// DATA FALLBACK (No special RBAC)
$GLOBALS['UISELECTOR_DATA_INVARIANT_BusinessObject'] = function ()
{
    $data = [
        [ 'urn'=> 'urn:a:a:a:1', 'title'=> "BO 1", 'boc'=> ["urn:class:b:c:1"=> "Class BO 1"], 'bot'=> ["urn-category-1"=> "Type BO A"], 'place'=>'x', 'resp'=>'x2', 'invid'=>'c', 'manuf'=>'v' ]
    ];
    return $data;
};

?>
