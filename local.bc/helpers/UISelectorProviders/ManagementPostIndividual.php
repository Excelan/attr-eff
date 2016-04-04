<?php

$GLOBALS['UISELECTOR_METADATA_ManagementPostIndividual'] = function ()
{
    $metadata = [
        "fields"=> [
            ['name'=> "title", 'title'=> "ФИО сотрудника", 'type'=> "string"],
            // ['name'=> "title", 'title'=> "ФИО, Должность", 'type'=> "string"],
            ['name'=> "posttype", 'title'=> "Тип должности", 'type'=> "unit"],
            ['name'=> "post", 'title'=> "Должность", 'type'=> "string"],
            ['name'=> "department", 'title'=> "Отдел/Департамент", 'type'=> "unit"]
        ]
    ];
    return $metadata;
};

$GLOBALS['UISELECTOR_DATA_ManagementPostIndividual'] = function ()
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Management:Post:Individual';
    $m->order = 'title ASC';
    $ds = $m->deliver();

    $data = [];
    foreach ($ds as $dr)
    {
      try {
        $posttype = $dr->ManagementPostGroup;
        if (count($posttype))
        {
            $postTypeUnit = [(string)$posttype->urn => $posttype->title];
        }
        else continue;

        $department = $dr->CompanyStructureDepartment;
        if (count($department))
        {
            $departmentUnit = [(string)$department->urn => $department->title];
        }
        else continue;

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:People:Employee:Internal';
        $m->ManagementPostIndividual = $dr->urn;
        $employee = $m->deliver();

        array_push($data, ['urn'=>(string)$dr->urn,
            'title'=>$employee->title,
            '_extended'=> $dr->title . '; ' . $employee->title,
            'posttype'=> $postTypeUnit,
            'post'=> $dr->title,
            'department'=>$departmentUnit
        ]);
      }
      catch (Exception $e) {
        Log::error((string)$e, 'ui');
      }
    }
    return $data;
};
?>
