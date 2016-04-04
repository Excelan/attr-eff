<?php

// TODO mark (uri) field & make it "set once" type

/**
FORMATTERS
DB, non DB (message->total (int bytes))

TODO add plaintext option NL2BR

TODO взаимоисключающие статусы

updated - any system or user update
edited - user updated



DONT USE _ UNDERSCORE IN FIELD NAMES!

*/

$GLOBALS['CONFIG']['STATUS'][1] = new StatusMeta(  array( "uid" => "1", "name" => "active", "title"=>"Активен", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][19121] = new StatusMeta(  array( "uid" => "19121", "name" => "virtual", "title"=>"Virtual", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][101] = new StatusMeta(  array( "uid" => "101", "name" => "isused", "title"=>"Использован", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][17] = new StatusMeta(  array( "uid" => "17", "name" => "cloned", "title"=>"ZFS Cloned", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][3] = new StatusMeta(  array( "uid" => "3", "name" => "top", "title"=>"Top", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][9] = new StatusMeta(  array( "uid" => "9", "name" => "payed", "title"=>"Оплачено", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][11] = new StatusMeta(  array( "uid" => "11", "name" => "closed", "title"=>"Закрыто", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][12] = new StatusMeta(  array( "uid" => "12", "name" => "terminated", "title"=>"Терминировано", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][140] = new StatusMeta(  array( "uid" => "140", "name" => "cancelled", "title"=>"Отменен", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][1401] = new StatusMeta(  array( "uid" => "1401", "name" => "local", "title"=>"Локальный", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][23401] = new StatusMeta(  array( "uid" => "23401", "name" => "approveden", "title"=>"Approved En", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][23402] = new StatusMeta(  array( "uid" => "23402", "name" => "approvedit", "title"=>"Approved It", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][23403] = new StatusMeta(  array( "uid" => "23403", "name" => "approvedcn", "title"=>"Approved Cn", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][1201] = new StatusMeta(  array( "uid" => "1201", "name" => "hidden", "title"=>"Скрыто", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][14] = new StatusMeta(  array( "uid" => "14", "name" => "public", "title"=>"Public", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][13] = new StatusMeta(  array( "uid" => "13", "name" => "brokenlink", "title"=>"Broken link", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][111779] = new StatusMeta(  array( "uid" => "111779", "name" => "hasfolder", "title"=>"Has Folder", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][112] = new StatusMeta(  array( "uid" => "112", "name" => "activefolder", "title"=>"Folder Active", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][115] = new StatusMeta(  array( "uid" => "115", "name" => "dependent", "title"=>"Подчиненный", "default" => 0 )  );


$GLOBALS['CONFIG']['STATUS'][117] = new StatusMeta(  array( "uid" => "117", "name" => "accented", "title"=>"Выделено", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][118] = new StatusMeta(  array( "uid" => "118", "name" => "multiple", "title"=>"Множественный выбор", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][119] = new StatusMeta(  array( "uid" => "119", "name" => "disallowblank", "title"=>"Выбор обязателен", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][120] = new StatusMeta(  array( "uid" => "120", "name" => "secondary", "title"=>"Вторично", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][1011] = new StatusMeta(  array( "uid" => "10011", "name" => "readen", "title"=>"Прочтено", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][301] = new StatusMeta(  array( "uid" => "301", "name" => "positive", "title"=>"Положительный", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][302] = new StatusMeta(  array( "uid" => "302", "name" => "opener", "title"=>"Открывающий", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][2] = new StatusMeta(  array( "uid" => "2", "name" => "draft", "title"=>"Черновик", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][501] = new StatusMeta(  array( "uid" => "501", "name" => "winner", "title"=>"Победитель конкурсов", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][502] = new StatusMeta(  array( "uid" => "502", "name" => "open", "title"=>"Открыт", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][503] = new StatusMeta(  array( "uid" => "503", "name" => "acceptnewonly", "title"=>"Только новые", "default" => 1 )  );

$GLOBALS['CONFIG']['STATUS'][1010] = new StatusMeta(  array( "uid" => "10010", "name" => "popular", "title"=>"Популярное", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][1011] = new StatusMeta(  array( "uid" => "10011", "name" => "readen", "title"=>"Прочтено", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][200] = new StatusMeta(  array( "uid" => "200", "name" => "archived", "title"=>"Архивный", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][908001] = new StatusMeta(  array( "uid" => "908001", "name" => "tokentradehttpget", "title"=>"Token http GET", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][908002] = new StatusMeta(  array( "uid" => "908002", "name" => "scopescontrolled", "title"=>"Права конф локально", "default" => 1 )  );
$GLOBALS['CONFIG']['STATUS'][908003] = new StatusMeta(  array( "uid" => "908003", "name" => "legacy", "title"=>"Legacy", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][20201] = new StatusMeta(  array( "uid" => "20201", "name" => "autocloseinvoice", "title" => "Автоматически закрывать счет", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][99201] = new StatusMeta(  array( "uid" => "99201", "name" => "isapp", "title" => "Приложение", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][9912] = new StatusMeta(  array( "uid" => "9912", "name" => "app", "title" => "Приложение", "default" => 0  )  );

$GLOBALS['CONFIG']['STATUS'][99001] = new StatusMeta(  array( "uid" => "99001", "name" => "closed", "title" => "Закрыто", "default" => 0  )  );
$GLOBALS['CONFIG']['STATUS'][99002] = new StatusMeta(  array( "uid" => "99002", "name" => "abandoned", "title" => "Брошено", "default" => 0  )  );
$GLOBALS['CONFIG']['STATUS'][99003] = new StatusMeta(  array( "uid" => "99003", "name" => "converted", "title" => "Сконвертировано", "default" => 0  )  );
$GLOBALS['CONFIG']['STATUS'][99004] = new StatusMeta(  array( "uid" => "99004", "name" => "delivered", "title" => "Доставлено", "default" => 0  )  );

$GLOBALS['CONFIG']['STATUS'][99005] = new StatusMeta(  array( "uid" => "99005", "name" => "proposed", "title" => "Предложен", "default" => 0  )  );
$GLOBALS['CONFIG']['STATUS'][99007] = new StatusMeta(  array( "uid" => "99007", "name" => "used", "title" => "Использован", "default" => 0  )  );

$GLOBALS['CONFIG']['STATUS'][99008] = new StatusMeta(  array( "uid" => "99008", "name" => "multy", "title" => "Multy", "default" => 0  )  );

$GLOBALS['CONFIG']['STATUS'][990192] = new StatusMeta(  array( "uid" => "990192", "name" => "controllable", "title" => "Управляемый", "default" => 0  )  );


$GLOBALS['CONFIG']['STATUS'][99138] = new StatusMeta(  array( "uid" => "99138", "name" => "isseller", "title" => "Поставщик", "default" => 0  )  );
$GLOBALS['CONFIG']['STATUS'][99139] = new StatusMeta(  array( "uid" => "99139", "name" => "isbuyer", "title" => "Покупатель", "default" => 0  )  );

$GLOBALS['CONFIG']['STATUS'][19290123] = new StatusMeta(  array( "uid" => "19290123", "name" => "sale", "title"=>"Sale", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][1066] = new StatusMeta(  array( "uid" => "1066", "name" => "approved", "title"=>"Подтвержден", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][107781] = new StatusMeta(  array( "uid" => "107781", "name" => "legacy", "title"=>"Legacy", "default" => 0 )  );


// wf
$GLOBALS['CONFIG']['STATUS'][121] = new StatusMeta(  array( "uid" => "121", "name" => "automated", "title"=>"Автоматизировано", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][122] = new StatusMeta(  array( "uid" => "122", "name" => "signed", "title"=>"Подписано", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][123] = new StatusMeta(  array( "uid" => "123", "name" => "done", "title"=>"Выполнено", "default" => 0 )  );
$GLOBALS['CONFIG']['STATUS'][10123] = new StatusMeta(  array( "uid" => "10123", "name" => "startpoint", "title"=>"Начальный", "default" => 0 )  );

$GLOBALS['CONFIG']['STATUS'][1100017] = new StatusMeta(  array( "uid" => "1100017", "name" => "tester", "title"=>"Тестер", "default"=> 0)  );

// wf
$GLOBALS['CONFIG']['FIELD'][9093] = new FieldMeta(  array( "uid" => "9093", "name" => "taskfrom", "title" => "Task From", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][9094] = new FieldMeta(  array( "uid" => "9094", "name" => "taskto", "title" => "Task To", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1999] = new FieldMeta(  array( "uid" => "1999", "name" => "targetuser", "title" => "Target USER", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][11999] = new FieldMeta(  array( "uid" => "11999", "name" => "mqname", "title" => "MQ name", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][280202] = new FieldMeta(  array( "uid" => "280202", "name" => "wfstranslateen", "title" => "Workflow translate en", 'units'=>'0 - не начат, 3 - сделан, 5 - утвержден, 6 - переделать', "type" => "integer", 'system'=>true )  );
$GLOBALS['CONFIG']['FIELD'][280203] = new FieldMeta(  array( "uid" => "280203", "name" => "wfstranslateit", "title" => "Workflow translate it", 'units'=>'0 - не начат, 3 - сделан, 5 - утвержден, 6 - переделать', "type" => "integer", 'system'=>true )  );
$GLOBALS['CONFIG']['FIELD'][280205] = new FieldMeta(  array( "uid" => "280205", "name" => "wfstranslatede", "title" => "Workflow translate de", 'units'=>'0 - не начат, 3 - сделан, 5 - утвержден, 6 - переделать', "type" => "integer", 'system'=>true )  );
$GLOBALS['CONFIG']['FIELD'][280204] = new FieldMeta(  array( "uid" => "280204", "name" => "wfstranslatecn", "title" => "Workflow translate cn", 'units'=>'0 - не начат, 3 - сделан, 5 - утвержден, 6 - переделать', "type" => "integer", 'system'=>true )  );
// update product set wfstranslatede = 0;

// WMS
$GLOBALS['CONFIG']['FIELD'][97321] = new FieldMeta(  array( "uid" => "97321", "name" => "direction", "type" => "set", "title"=>"Тип", "options" => array('in'=>'Приход','out'=>'Уход') )  );
$GLOBALS['CONFIG']['FIELD'][97322] = new FieldMeta(  array( "uid" => "97322", "name" => "shipmentstate", "type" => "set", "title"=>"Состояние отправки", "options" => array('parcelsent'=>'Отправлено','parceldelivered'=>'Получено') )  );

$GLOBALS['CONFIG']['FIELD'][202047] = new FieldMeta(  array( "uid" => "202047", "name" => "oauth2service", "type" => "string", "title" => "oauth name key (from config)" )  );
$GLOBALS['CONFIG']['FIELD'][202041] = new FieldMeta(  array( "uid" => "202041", "name" => "oauthtokensecret", "type" => "string", "title" => "oauth v1 token secret" )  );
$GLOBALS['CONFIG']['FIELD'][202044] = new FieldMeta(  array( "uid" => "202044", "name" => "expire", "type" => "integer", "title" => "Expire", 'units' => 'сек' )  );


$GLOBALS['CONFIG']['FIELD'][202747] = new FieldMeta(  array( "uid" => "202747", "name" => "accountno", "type" => "integer", "title" => "Acoount #" )  );
$GLOBALS['CONFIG']['FIELD'][202741] = new FieldMeta(  array( "uid" => "202741", "name" => "accountfrom", "type" => "integer", "title" => "Acoount from #" )  );
$GLOBALS['CONFIG']['FIELD'][202742] = new FieldMeta(  array( "uid" => "202742", "name" => "accountto", "type" => "integer", "title" => "Acoount to #" )  );
$GLOBALS['CONFIG']['FIELD'][202743] = new FieldMeta(  array( "uid" => "202743", "name" => "payway", "type" => "string", "title" => "Pay Way" )  );


$GLOBALS['CONFIG']['FIELD'][70901] = new FieldMeta(  array( "uid" => "70901", "name" => "paymentgateway", "title" => "", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][70902] = new FieldMeta(  array( "uid" => "70902", "name" => "phoneverified", "title" => "Телефон verified", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][70903] = new FieldMeta(  array( "uid" => "70903", "name" => "phoneprovided", "title" => "Телефон", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][70910] = new FieldMeta(  array( "uid" => "70910", "name" => "mixeddata", "title" => "mixed data", "type" => "text", "system" => true )  );

$GLOBALS['CONFIG']['FIELD'][888721] = new FieldMeta(  array( "uid" => "888721", "name" => "facecount", "title" => "Лиц на фото", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][888722] = new FieldMeta(  array( "uid" => "888722", "name" => "facelist", "title" => "Лиц на фото", "type" => "string", "raw"=>true )  );

$GLOBALS['CONFIG']['FIELD'][70904] = new FieldMeta(  array( "uid" => "70904", "name" => "openedamount", "title" => "Открыт на сумму", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][70905] = new FieldMeta(  array( "uid" => "70905", "name" => "openedcurr", "title" => "Открыт в валюте", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][70906] = new FieldMeta(  array( "uid" => "70906", "name" => "closedamount", "title" => "Закрыт на сумму", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][70907] = new FieldMeta(  array( "uid" => "70907", "name" => "closedcurr", "title" => "Закрыт в валюте", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][70908] = new FieldMeta(  array( "uid" => "70908", "name" => "remoteid", "title" => "Номер транзакции в удаленной системе", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][70909] = new FieldMeta(  array( "uid" => "70909", "name" => "maxage", "title" => "Врем жизни", "type" => "integer", 'units' => 'сек' )  );

$GLOBALS['CONFIG']['FIELD'][99] = new FieldMeta(  array( "uid" => "99", "name" => "app", "title" => "Приложение", "type" => "string"  )  );

$GLOBALS['CONFIG']['FIELD'][31012] = new FieldMeta(  array( "uid" => "31012", "name" => "complain", "title" => "Число жалоб", "type" => "integer",  "disabled" => false, "default" => 0 )  );

$GLOBALS['CONFIG']['FIELD'][821] = new FieldMeta(  array( "uid" => "821", "name" => "fromname", "title" => "Email от имени", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][822] = new FieldMeta(  array( "uid" => "822", "name" => "fromemail", "title" => "Email обратный адрес", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][25004] = new FieldMeta(  array( "uid" => "25004", "name" => "question", "title" => "Вопрос", "type" => "richtext",
"htmlallowed" => "p,br,a[href],cite,b,strong,i,em", "autoparagraph" => true, "nofollow" => true )  ); 

$GLOBALS['CONFIG']['FIELD'][25003] = new FieldMeta(  array( "uid" => "25003", "name" => "answer", "title" => "Ответ", "type" => "richtext",
"htmlallowed" => "p,br,a[href],cite,b,strong,i,em", "autoparagraph" => true, "nofollow" => false )  );

$GLOBALS['CONFIG']['FIELD'][79812] = new FieldMeta(  array( "uid" => "79812", "name" => "prefs", "type" => "string", 'title'=>'Настройки' )  );

$GLOBALS['CONFIG']['FIELD'][10002] = new FieldMeta(  array( "uid" => "10002", "name" => "acttype", "type" => "string", 'title'=>'Действие' )  );
$GLOBALS['CONFIG']['FIELD'][10003] = new FieldMeta(  array( "uid" => "10003", "name" => "actonobject", "type" => "string", 'title'=>'Объект действия' )  );
$GLOBALS['CONFIG']['FIELD'][9003] = new FieldMeta(  array( "uid" => "9003", "name" => "targetuser", "title" => "Получатель(id)", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][901] = new FieldMeta(  array( "uid" => "901", "name" => "fromuserurn", "title" => "from_user_urn", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][902] = new FieldMeta(  array( "uid" => "902", "name" => "touserurn", "title" => "to_user_urn", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][9902] = new FieldMeta(  array( "uid" => "9902", "name" => "targeturn", "title" => "Target URN", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][9903] = new FieldMeta(  array( "uid" => "9903", "name" => "feedaction", "title" => "feedaction", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][3000701] = new FieldMeta(  array( "uid" => "3000701", "name" => "countaction", "title" => "Число действий", "type" => "integer",  "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30001] = new FieldMeta(  array( "uid" => "30001", "name" => "countview", "title" => "Число просмотров", "type" => "integer",  "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30003] = new FieldMeta(  array( "uid" => "30003", "name" => "countplus", "title" => "Число плюсов", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30004] = new FieldMeta(  array( "uid" => "30004", "name" => "countminus", "title" => "Число минусов", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30005] = new FieldMeta(  array( "uid" => "30005", "name" => "avgstar", "title" => "Средний рейтинг", "type" => "float", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000101] = new FieldMeta(  array( "uid" => "10000101", "name" => "countnews", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000102] = new FieldMeta(  array( "uid" => "10000102", "name" => "countpost", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000103] = new FieldMeta(  array( "uid" => "10000103", "name" => "countfpost", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000104] = new FieldMeta(  array( "uid" => "10000104", "name" => "countfthread", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000105] = new FieldMeta(  array( "uid" => "10000105", "name" => "counttag", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000106] = new FieldMeta(  array( "uid" => "10000106", "name" => "countuser", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000107] = new FieldMeta(  array( "uid" => "10000107", "name" => "countphotoitem", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000108] = new FieldMeta(  array( "uid" => "10000108", "name" => "countcomment", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000109] = new FieldMeta(  array( "uid" => "10000109", "name" => "countad", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000110] = new FieldMeta(  array( "uid" => "10000110", "name" => "countcompany", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000111] = new FieldMeta(  array( "uid" => "10000111", "name" => "countpressrelease", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000112] = new FieldMeta(  array( "uid" => "10000112", "name" => "countmess", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][10000118] = new FieldMeta(  array( "uid" => "10000118", "name" => "countentity", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
//$GLOBALS['CONFIG']['FIELD'][10000118] = new FieldMeta(  array( "uid" => "10000118", "name" => "countentity", "title" => "system counter", "type" => "integer", "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30007] = new FieldMeta(  array( "uid" => "30007", "name" => "countsold", "title" => "Число продаж", "type" => "integer", "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30008] = new FieldMeta(  array( "uid" => "30008", "name" => "countthankyou", "title" => "Число благодарностей", "type" => "integer",  "disabled" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][30010] = new FieldMeta(  array( "uid" => "30010", "name" => "countphotoitem", "title" => "Число фото", "type" => "integer",  "disabled" => true, "default" => 0 )  );


$GLOBALS['CONFIG']['FIELD'][97381] = new FieldMeta(  array( "uid" => "97381", "name" => "freeareas", "title" => "freeareas", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][97382] = new FieldMeta(  array( "uid" => "97382", "name" => "histogram64", "title" => "histogram64", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][97383] = new FieldMeta(  array( "uid" => "97383", "name" => "binaryimage", "title" => "binaryimage", "type" => "string", "raw"=>true )  );
//$GLOBALS['CONFIG']['FIELD'][97384] = new FieldMeta(  array( "uid" => "97384", "name" => "color", "title" => "color", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][97385] = new FieldMeta(  array( "uid" => "97385", "name" => "color2", "title" => "color2", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][2117] = new FieldMeta(  array( "uid" => "2117", "name" => "tags", "title" => "Теги", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1232] = new FieldMeta(  array( "uid" => "1232", "name" => "session", "title" => "session", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][2111] = new FieldMeta(  array( "uid" => "2111", "name" => "city", "title" => "Город", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][2112] = new FieldMeta(  array( "uid" => "2112", "name" => "datebirdth", "title" => "День рождения", "type" => "date" )  );
$GLOBALS['CONFIG']['FIELD'][2113] = new FieldMeta(  array( "uid" => "2113", "name" => "about", "title" => "О себе", "type" => "text" )  );
$GLOBALS['CONFIG']['FIELD'][2123] = new FieldMeta(  array( "uid" => "2123", "name" => "description", "title" => "Описание", "type" => "text" )  );
$GLOBALS['CONFIG']['FIELD'][2114] = new FieldMeta(  array( "uid" => "2114", "name" => "skype", "title" => "Skype", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][2115] = new FieldMeta(  array( "uid" => "2115", "name" => "icq", "title" => "ICQ", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][2116] = new FieldMeta(  array( "uid" => "2116", "name" => "vk", "title" => "В контакте", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][2119] = new FieldMeta(  array( "uid" => "2119", "name" => "fb", "title" => "Facebook", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][1922] = new FieldMeta(  array( "uid" => "1922", "name" => "year", "title" => "Год", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][106412] = new FieldMeta(  array( "uid" => "106412", "name" => "bankinfo", "title" => "Платежная инфорация", "type" => "text" )  );

$GLOBALS['CONFIG']['FIELD'][19221335] = new FieldMeta(  array( "uid" => "19221335", "name" => "priority", "title" => "Приоритет", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][3000201] = new FieldMeta(  array( "uid" => "3000201", "name" => "ratedesign", "title" => "Дизайн", "type" => "integer",  "disabled" => false, "default" => 0  )  );
$GLOBALS['CONFIG']['FIELD'][3000202] = new FieldMeta(  array( "uid" => "3000202", "name" => "rateusability", "title" => "Удобство использования", "type" => "integer",  "disabled" => false, "default" => 0  )  );
$GLOBALS['CONFIG']['FIELD'][3000203] = new FieldMeta(  array( "uid" => "3000203", "name" => "ratecontentamount", "title" => "Количество содержания", "type" => "integer",  "disabled" => false, "default" => 0  )  );
$GLOBALS['CONFIG']['FIELD'][3000204] = new FieldMeta(  array( "uid" => "3000204", "name" => "ratecontentquolity", "title" => "Качество содержания", "type" => "integer",  "disabled" => false, "default" => 0  )  );

$GLOBALS['CONFIG']['FIELD'][3000205] = new FieldMeta(  array( "uid" => "3000205", "name" => "feedbacktext", "title" => "Текст отзыва", "type" => "text", "disabled" => false  )  );
$GLOBALS['CONFIG']['FIELD'][3000208] = new FieldMeta(  array( "uid" => "3000208", "name" => "reportreason", "title" => "Причина жалобы", "type" => "string", "disabled" => false  )  );

$GLOBALS['CONFIG']['FIELD'][3000101] = new FieldMeta(  array( "uid" => "3000101", "name" => "countrefg", "title" => "Число заходов с Google", "type" => "integer",  "disabled" => true, "default" => 0  )  );
$GLOBALS['CONFIG']['FIELD'][3000102] = new FieldMeta(  array( "uid" => "3000102", "name" => "countrefya", "title" => "Число заходов с Yandex", "type" => "integer",  "disabled" => true, "default" => 0  )  );

$GLOBALS['CONFIG']['FIELD'][5008] = new FieldMeta(  array( "uid" => "5008", "name" => "mediaserver", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][81] = new FieldMeta(  array( "uid" => "81", "name" => "startdate", "title" => "Дата начала", "type" => "date" )  );
$GLOBALS['CONFIG']['FIELD'][82] = new FieldMeta(  array( "uid" => "82", "name" => "enddate", "title" => "Дата окончания", "type" => "date" )  );
$GLOBALS['CONFIG']['FIELD'][87] = new FieldMeta(  array( "uid" => "87", "name" => "closedat", "title" => "Закрыт в", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false )  );
$GLOBALS['CONFIG']['FIELD'][84] = new FieldMeta(  array( "uid" => "84", "name" => "payedat", "title" => "Оплачен в", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false )  );
//$GLOBALS['CONFIG']['FIELD'][88] = new FieldMeta(  array( "uid" => "88", "name" => "date", "title" => "Дата", "type" => "date" )  );
$GLOBALS['CONFIG']['FIELD'][8881] = new FieldMeta(  array( "uid" => "8881", "name" => "duration", "title" => "Продолжительность", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][12] = new FieldMeta(  array( "uid" => "12", "name" => "metadesc", "title" => "Meta Description", "type" => "text" )  );
$GLOBALS['CONFIG']['FIELD'][12987] = new FieldMeta(  array( "uid" => "12987", "name" => "metakeywords", "title" => "Meta Keywords", "type" => "text" )  );
$GLOBALS['CONFIG']['FIELD'][12988] = new FieldMeta(  array( "uid" => "12988", "name" => "seotext", "title" => "SEO текст", "type" => "text" )  );

$GLOBALS['CONFIG']['FIELD'][77721] = new FieldMeta(  array( "uid" => "77721", "name" => "_attributes", "title" => "_attributes", "type" => "json", "system"=>true, "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][77722] = new FieldMeta(  array( "uid" => "77722", "name" => "_attributeoverlay", "title" => "_attributeoverlay", "type" => "json", "system"=>true, "raw"=>true )  );


$GLOBALS['CONFIG']['FIELD'][100001] = new FieldMeta(  array( "uid" => "100001", "name" => "subject", "title" => "Тема", "type" => "string" )  );


$GLOBALS['CONFIG']['FIELD'][8882] = new FieldMeta(  array( "uid" => "8882", "name" => "lastlogin", "title" => "Время последнего входа", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false, 'system'=>true )  );

$GLOBALS['CONFIG']['FIELD'][1881] = new FieldMeta(  array( "uid" => "1881", "name" => "dtstart", "title" => "Время начала", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false )  );
$GLOBALS['CONFIG']['FIELD'][1882] = new FieldMeta(  array( "uid" => "1882", "name" => "dtend", "title" => "Время окончания", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false )  );


$GLOBALS['CONFIG']['FIELD'][814] = new FieldMeta(  array( "uid" => "814", "name" => "ondate", "title" => "Весь день", "type" => "date" )  );

$GLOBALS['CONFIG']['FIELD'][81] = new FieldMeta(  array( "uid" => "81", "name" => "startdate", "title" => "Дата начала", "type" => "date" )  );
$GLOBALS['CONFIG']['FIELD'][82] = new FieldMeta(  array( "uid" => "82", "name" => "enddate", "title" => "Дата окончания", "type" => "date" )  );
$GLOBALS['CONFIG']['FIELD'][888] = new FieldMeta(  array( "uid" => "888", "name" => "duration", "title" => "Продолжительность", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][8010] = new FieldMeta(  array( "uid" => "8010", "name" => "place", "title" => "Место", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][9001] = new FieldMeta(  array( "uid" => "9001", "name" => "oldid", "title" => "Старый ID", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][9002] = new FieldMeta(  array( "uid" => "9002", "name" => "icaluid", "title" => "iCal UID", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][9091] = new FieldMeta(  array( "uid" => "9091", "name" => "timelimit", "title" => "timelimit", "type" => "integer" )  );


$GLOBALS['CONFIG']['FIELD'][9923] = new FieldMeta(  array( "uid" => "9923", "name" => "hostuser", "type" => "string", "title"=>"Host User" )  );
$GLOBALS['CONFIG']['FIELD'][9924] = new FieldMeta(  array( "uid" => "9924", "name" => "directpath", "type" => "string", "title"=>"directpath" )  );

$GLOBALS['CONFIG']['FIELD'][20101] = new FieldMeta(  array( "uid" => "20101", "name" => "basetype", "type" => "set", "title"=>"Тип", "options" => array('string'=>'Строка','text'=>'Текст','integer'=>'Число','float'=>'Дробное число','option'=>'Опция') )  );
$GLOBALS['CONFIG']['FIELD'][20103] = new FieldMeta(  array( "uid" => "20103", "name" => "continuumtype", "type" => "set", "title"=>"Континиум", "options" => array('string'=>'Номинал','text'=>'Интервал','string'=>'Строка','free'=>'Свободный') ) );
$GLOBALS['CONFIG']['FIELD'][20102] = new FieldMeta(  array( "uid" => "20102", "name" => "units", "type" => "string", "title"=>"Единицы измерения" )  );

$GLOBALS['CONFIG']['FIELD'][1998] = new FieldMeta(  array( "uid" => "1998", "name" => "icon",  "title"=>"Иконка", "type" => "image" )  );
$GLOBALS['CONFIG']['FIELD'][2014] = new FieldMeta(  array( "uid" => "2014", "name" => "base64image",  "title"=>"base64 image", "type" => "image", 'virtual' => true )  );
$GLOBALS['CONFIG']['FIELD'][2015] = new FieldMeta(  array( "uid" => "2015", "name" => "base64data",  "title"=>"base64 data", "type" => "image", 'virtual' => true )  );

$GLOBALS['CONFIG']['FIELD'][102030] = new FieldMeta(  array( "uid" => "102030", "name" => "thumbnail",  "title"=>"Thumbnail base64", "type" => "image" )  );
$GLOBALS['CONFIG']['FIELD'][921032] = new FieldMeta(  array( "uid" => "921032", "name" => "original",  "title"=>"Original url", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][9977] = new FieldMeta(  array( "uid" => "9977", "name" => "amount", "type" => "float", "precision" => 2, "title" => "Количество" )  );
$GLOBALS['CONFIG']['FIELD'][993] = new FieldMeta(  array( "uid" => "993", "name" => "total", "type" => "money", "title" => "Сумма" )  );
$GLOBALS['CONFIG']['FIELD'][998] = new FieldMeta(  array( "uid" => "998", "name" => "price",  "title"=>"Цена", "type" => "money", "units" => "уе" )  );
$GLOBALS['CONFIG']['FIELD'][9980] = new FieldMeta(  array( "uid" => "9980", "name" => "discount",  "title"=>"Скидка %", "type" => "integer", "units" => "%" )  );
$GLOBALS['CONFIG']['FIELD'][9988] = new FieldMeta(  array( "uid" => "9988", "name" => "pricein",  "title"=>"Цена закупки", "type" => "float" )  );




$GLOBALS['CONFIG']['FIELD'][888801] = new FieldMeta(  array( "uid" => "888801", "name" => "origin", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][888802] = new FieldMeta(  array( "uid" => "888802", "name" => "resource", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][888803] = new FieldMeta(  array( "uid" => "888803", "name" => "details", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][888804] = new FieldMeta(  array( "uid" => "888804", "name" => "delegate", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][888805] = new FieldMeta(  array( "uid" => "888805", "name" => "exampleeventdata", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][888806] = new FieldMeta(  array( "uid" => "888806", "name" => "examplemaildata", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][921] = new FieldMeta(  array( "uid" => "921", "name" => "max", "type" => "integer", "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][922] = new FieldMeta(  array( "uid" => "922", "name" => "used", "title" => "Использовано", "type" => "integer", "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][923] = new FieldMeta(  array( "uid" => "923", "name" => "type", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][961] = new FieldMeta(  array( "uid" => "961", "name" => "container", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][962] = new FieldMeta(  array( "uid" => "962", "name" => "containerval", "type" => "integer" )  );


$GLOBALS['CONFIG']['FIELD'][9574] = new FieldMeta(  array( "uid" => "9574", "name" => "floated", "type" => "float", "precision" => 3, "title" => "Что-то float" )  );

$GLOBALS['CONFIG']['FIELD'][20201] = new FieldMeta(  array( "uid" => "20201", "name" => "address", "title" => "Адрес (формат - Город, Улица Дом, Кв)", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][20202] = new FieldMeta(  array( "uid" => "20202", "name" => "number", "title" => "Номер телефона", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][20203] = new FieldMeta(  array( "uid" => "20203", "name" => "nickname", "title" => "Псевдоним", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][10111] = new FieldMeta(  array( "uid" => "10111", "name" => "alt", "title" => "Alt", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1011] = new FieldMeta(  array( "uid" => "1011", "name" => "imgalt", "title" => "img alt", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1012] = new FieldMeta(  array( "uid" => "1012", "name" => "imgcopyright", "title" => "img copyright", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1013] = new FieldMeta(  array( "uid" => "1013", "name" => "author", "title" => "Автор", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1014] = new FieldMeta(  array( "uid" => "1014", "name" => "srcurl", "title" => "Источник ссылка", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][101491] = new FieldMeta(  array( "uid" => "101491", "name" => "url", "title" => "URL", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][1015] = new FieldMeta(  array( "uid" => "1015", "name" => "srctitle", "title" => "Источник имя", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1016] = new FieldMeta(  array( "uid" => "1016", "name" => "simpletags", "title" => "Теги", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][1021] = new FieldMeta(  array( "uid" => "1021", "name" => "wwwurl", "title" => "URL", "type" => "string", "raw"=>true )  );

$GLOBALS['CONFIG']['FIELD'][790] = new FieldMeta(  array( "uid" => "790", "name" => "floatrating", "title" => "Рейтинг дробный", "type" => "float", "noneditable" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][791] = new FieldMeta(  array( "uid" => "791", "name" => "ratingf", "title" => "Рейтинг", "type" => "float", "noneditable" => true, "default" => 0 )  );

$GLOBALS['CONFIG']['FIELD'][890] = new FieldMeta(  array( "uid" => "890", "name" => "rating", "title" => "Рейтинг", "type" => "integer", "noneditable" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][893] = new FieldMeta(  array( "uid" => "893", "name" => "ratingp", "title" => "Рейтинг+", "type" => "integer", "noneditable" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][892] = new FieldMeta(  array( "uid" => "892", "name" => "ratingn", "title" => "Рейтинг-", "type" => "integer", "noneditable" => true, "default" => 0 )  );
$GLOBALS['CONFIG']['FIELD'][891] = new FieldMeta(  array( "uid" => "891", "name" => "commentcount", "title" => "Кол-во комментариев", "type" => "integer", "noneditable" => true, "default" => 0 )  );

$GLOBALS['CONFIG']['FIELD'][1000] = new FieldMeta(  array( "uid" => "1000", "name" => "counter", "title" => "Counter", "type" => "integer", "noneditable" => true, "default" => 0 )  );

$GLOBALS['CONFIG']['FIELD'][1234] = new FieldMeta(  array( "uid" => "1234", "name" => "ordered", "title" => "Порядок", "type" => "integer","system"=>true )  );
$GLOBALS['CONFIG']['FIELD'][201] = new FieldMeta(  array( "uid" => "201", "name" => "embedyoutube", "title" => "YouTube", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][202] = new FieldMeta(  array( "uid" => "202", "name" => "embedvimeo", "title" => "Vimeo", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][203] = new FieldMeta(  array( "uid" => "203", "name" => "embedrutube", "title" => "Rutube", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][1] = new FieldMeta(  array( "uid" => "1", "name" => "title", "title" => "Заголовок", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][2] = new FieldMeta(  array( "uid" => "2", "name" => "uri", "title" => "URI", "type" => "string" /*'disabled' => true*/)  );
$GLOBALS['CONFIG']['FIELD'][2320] = new FieldMeta(  array( "uid" => "2320", "name" => "folder", "title" => "Папка", "type" => "string")  );
$GLOBALS['CONFIG']['FIELD'][101101] = new FieldMeta(  array( "uid" => "101101", "name" => "oneline", "title" => "Одной строкой", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][4] = new FieldMeta(  array( "uid" => "4", "name" => "text", "title" => "Текст", "type" => "richtext", "illustrated" => true, "illustrations" => "urn-img",
"htmlallowed" => "h2,h3,h4,h5,h6,p,span,br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => true, "nofollow" => false )  );

// ! settings.js need span[class] for user styles, p[style] for text align

$GLOBALS['CONFIG']['FIELD'][5] = new FieldMeta(  array( "uid" => "5", "name" => "anons", "title" => "Анонс", "type" => "richtext",
"htmlallowed" => "p,br,a[href],cite,b,strong,i,em", "autoparagraph" => true, "nofollow" => false )  );

$GLOBALS['CONFIG']['FIELD'][501] = new FieldMeta(  array( "uid" => "501", "name" => "ctext", "title" => "Пользовательский текст", "type" => "richtext", 
							     "htmlallowed" => "p,br,a[href],cite,b,strong,i,em,img[alt|src]", "autoparagraph" => true, "nofollow" => true )  );


$GLOBALS['CONFIG']['FIELD'][5551] = new FieldMeta(  array( "uid" => "5551", "name" => "mailhtml", "title" => "Email html текст с параметрами", "type" => "richtext", 
							     "htmlallowed" => "h1,h2,h3,h4,h5,h6,p[class|style],br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => false, "nofollow" => false )  );

$GLOBALS['CONFIG']['FIELD'][5501] = new FieldMeta(  array( "uid" => "5501", "name" => "gtext", "title" => "Текст", "type" => "text" )  );

$GLOBALS['CONFIG']['FIELD'][5559] = new FieldMeta(  array( "uid" => "5559", "name" => "fullhtml", "title" => "Полный html", "type" => "richtext", 
							      'raw' => true )  ); // "htmlallowed" => "h1,h2,h3,h4,h5,h6,p[style],span[style],br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => true, "nofollow" => false,

/*New MailTemplate*/
$GLOBALS['CONFIG']['FIELD'][55561] = new FieldMeta(  array( "uid" => "55561", "name" => "headerhtml", "title" => "Шапка письма", "type" => "richtext",
									     'raw' => true )  ); // "htmlallowed" => "h1,h2,h3,h4,h5,h6,p[style],span[style],br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => true, "nofollow" => false,
$GLOBALS['CONFIG']['FIELD'][55562] = new FieldMeta(  array( "uid" => "55562", "name" => "contenthtml", "title" => "Контент письма  Ссылка [link http://*text*attribute]", "type" => "richtext",
										'raw' => true )  ); // "htmlallowed" => "h1,h2,h3,h4,h5,h6,p[style],span[style],br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => true, "nofollow" => false,
$GLOBALS['CONFIG']['FIELD'][55563] = new FieldMeta(  array( "uid" => "55563", "name" => "footerhtml", "title" => "Футер письма", "type" => "richtext",
										'raw' => true )  ); // "htmlallowed" => "h1,h2,h3,h4,h5,h6,p[style],span[style],br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => true, "nofollow" => false,
$GLOBALS['CONFIG']['FIELD'][55564] = new FieldMeta(  array( "uid" => "55564", "name" => "specialhtml", "title" => "Special", "type" => "richtext",
                                                            'raw' => true )  ); // "htmlallowed" => "h1,h2,h3,h4,h5,h6,p[style],span[style],br,a[href],cite,b,strong,i,em,img[alt|src],table[summary],tbody,th[abbr],tr,td[abbr],ul,ol,li,dl, dt, dd, blockquote", "autoparagraph" => true, "nofollow" => false,


$GLOBALS['CONFIG']['FIELD'][55565] = new FieldMeta(  array( "uid" => "55565", "name" => "headerplain", "title" => "Шапка письма", "type" => "text",'raw' => false )  );
$GLOBALS['CONFIG']['FIELD'][55566] = new FieldMeta(  array( "uid" => "55566", "name" => "contentplain", "title" => "Контент письма  Кнопка [link http://*text] Заголовок [H text] Текст [TEXT text]", "type" => "text",'raw' => false )  );
$GLOBALS['CONFIG']['FIELD'][55567] = new FieldMeta(  array( "uid" => "55567", "name" => "footerplain", "title" => "Футер письма", "type" => "text",'raw' => false )  );
$GLOBALS['CONFIG']['FIELD'][55568] = new FieldMeta(  array( "uid" => "55568", "name" => "specialplain", "title" => "Special", "type" => "text",'raw' => false )  );

/*New MailTemplate*/



// not suppoted by htmlpurify:
// form[method|action], input[type|name|value] ,
// iframe[src|frameborder|width|height|name|sandbox|scrolling|align]
// Attribute 'target' in element 'a' not supported (for information on implementing this, see the support forums)
// script[src|type|language|defer],
// center

$GLOBALS['CONFIG']['FIELD'][5504] = new FieldMeta(  array( "uid" => "5504", "name" => "color", "title" => "Цвет", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][9] = new FieldMeta(  array( "uid" => "9", "name" => "language", "title" => "Язык", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][2002] = new FieldMeta(  array( "uid" => "2002", "name" => "target", "title" => "Target", "type" => "string", /*'disabled' => true*/)  );

$GLOBALS['CONFIG']['FIELD'][88201] = new FieldMeta(  array( "uid" => "88201", "name" => "taken", "title" => "Время съемки", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false )  );

$GLOBALS['CONFIG']['FIELD'][882] = new FieldMeta(  array( "uid" => "882", "name" => "created", "title" => "Время создания", "type" => "timestamp", "createDefault"=> 'now', "updateDefault"=> false )  ); // Nnow U-
$GLOBALS['CONFIG']['FIELD'][881] = new FieldMeta(  array( "uid" => "881", "name" => "updated", "title" => "Время последнего изменения", "type" => "timestamp", "createDefault"=> 'now', "updateDefault"=> 'now', "disabled" => true )  ); // N-?now Unow
$GLOBALS['CONFIG']['FIELD'][888] = new FieldMeta(  array( "uid" => "888", "name" => "edited", "title" => "Время последнего редактирования", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false, "disabled" => true )  ); // N-?now Unow
$GLOBALS['CONFIG']['FIELD'][883] = new FieldMeta(  array( "uid" => "883", "name" => "published", "title" => "Опубликован", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false, "disabled" => true )  ); // N- U- (is null if created in future )
// replace with job that will remove on time
$GLOBALS['CONFIG']['FIELD'][885] = new FieldMeta(  array( "uid" => "885", "name" => "actualto", "title" => "Актуально до", "type" => "timestamp", "createDefault"=> false, "updateDefault"=> false, "disabled" => true )  ); // N- U- (is null if created in future )


$GLOBALS['CONFIG']['FIELD'][100010191] = new FieldMeta( array( "uid" => "100010191", "name" => "context", "title" => "Контекст", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][100] = new FieldMeta( array( "uid" => "100", "name" => "communitytext", "title" => "Текст сообщения", "type" => "text" )  );

$GLOBALS['CONFIG']['FIELD'][50] = new FieldMeta(  array( "uid" => "50", "name" => "quota", "title"=>"Quota (Mb)", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][550] = new FieldMeta(  array( "uid" => "550", "name" => "capacity", "title"=>"Объем (Mb)", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][51] = new FieldMeta(  array( "uid" => "51", "name" => "width", "type" => "integer", 'title' => 'Ширина' )  );
$GLOBALS['CONFIG']['FIELD'][62] = new FieldMeta(  array( "uid" => "62", "name" => "length", "type" => "integer", 'title' => 'Длина' )  );
$GLOBALS['CONFIG']['FIELD'][52] = new FieldMeta(  array( "uid" => "52", "name" => "height", "type" => "integer", 'title' => 'Высота' )  );
$GLOBALS['CONFIG']['FIELD'][53] = new FieldMeta(  array( "uid" => "53", "name" => "filesize", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][54] = new FieldMeta(  array( "uid" => "54", "name" => "filename", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][56] = new FieldMeta(  array( "uid" => "56", "name" => "filepath", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][57] = new FieldMeta(  array( "uid" => "57", "name" => "realpath", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][59] = new FieldMeta(  array( "uid" => "59", "name" => "weight", "title"=>"Вес", "type" => "float", 'units' => 'кг' )  );
$GLOBALS['CONFIG']['FIELD'][55] = new FieldMeta(  array( "uid" => "55", "name" => "size", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][222] = new FieldMeta( array( "uid" => "222", "name" => "mediatype", "title" => "Тип медиа", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][223] = new FieldMeta( array( "uid" => "223", "name" => "ext", "title" => "ext", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][801] = new FieldMeta(  array( "uid" => "801", "name" => "keywords", "title" => "Ключевые слова", "type" => "text", "disabled" => true )  );

$GLOBALS['CONFIG']['FIELD'][9980001] = new FieldMeta( array( "uid" => "9980001", "name" => "code", "title" => "code", "type" => "integer", 'system'=>false )  );

$GLOBALS['CONFIG']['FIELD'][99801] = new FieldMeta( array( "uid" => "99801", "name" => "dynamicsalt", "title" => "Dynamic Salt", "type" => "integer", 'system'=>true )  );
$GLOBALS['CONFIG']['FIELD'][99802] = new FieldMeta( array( "uid" => "99802", "name" => "userid64", "title" => "userid64", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][99803] = new FieldMeta( array( "uid" => "99803", "name" => "oauthaccesstoken", "title" => "oauthaccesstoken", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][99804] = new FieldMeta( array( "uid" => "99804", "name" => "appid", "title" => "appid", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][99805] = new FieldMeta( array( "uid" => "99805", "name" => "appsecret", "title" => "appsecret", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][99806] = new FieldMeta( array( "uid" => "99806", "name" => "scriptname", "title" => "scriptname", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][99807] = new FieldMeta( array( "uid" => "99807", "name" => "urloauthlogin", "title" => "urloauthlogin", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][99808] = new FieldMeta( array( "uid" => "99808", "name" => "urltokentrade", "title" => "urltokentrade", "type" => "string", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][99809] = new FieldMeta( array( "uid" => "99809", "name" => "hash", "title" => "hash", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][998010] = new FieldMeta( array( "uid" => "998010", "name" => "securehash", "title" => "securehash", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][998011] = new FieldMeta( array( "uid" => "998011", "name" => "renewhash", "title" => "renewhash", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][998012] = new FieldMeta( array( "uid" => "998012", "name" => "activationcode", "title" => "activationcode", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][998013] = new FieldMeta( array( "uid" => "998013", "name" => "emailssent", "title" => "emailssent", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][998014] = new FieldMeta( array( "uid" => "998014", "name" => "scopes", "title" => "scopes", "type" => "string" )  );
//$GLOBALS['CONFIG']['FIELD'][9980] = new FieldMeta( array( "uid" => "9980", "name" => "", "title" => "", "type" => "" )  );


// CRM
$GLOBALS['CONFIG']['FIELD'][1009] = new FieldMeta(  array( "uid" => "1009", "name" => "homephone", "title" => "Домашний телефон", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][10091] = new FieldMeta(  array( "uid" => "10091", "name" => "workphone", "title" => "Рабочий телефон", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][1011] = new FieldMeta(  array( "uid" => "1011", "name" => "web", "title" => "Web-сайт", "type" => "string" )  );


$GLOBALS['CONFIG']['FIELD'][21] = new FieldMeta(  array( "uid" => "21", "name" => "email", "title" => "E-mail", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][22] = new FieldMeta(  array( "uid" => "22", "name" => "password", "title" => "Пароль", "type" => "string", 'system'=>true )  );
$GLOBALS['CONFIG']['FIELD'][2200] = new FieldMeta(  array( "uid" => "2200", "name" => "plainpassword", "title" => "Видимый пароль", "type" => "string", 'system'=>false )  );

$GLOBALS['CONFIG']['FIELD'][9321273] = new FieldMeta(  array( "uid" => "9321273", "name" => "homeuri", "title" => "Home", "type" => "string", 'system'=>false )  );

// VIRTUAL (NON SAVED)
$GLOBALS['CONFIG']['FIELD'][220071] = new FieldMeta(  array( "uid" => "220071", "name" => "providedpassword", "title" => "Пароль", "type" => "string", 'virtual' => true)  );
$GLOBALS['CONFIG']['FIELD'][220072] = new FieldMeta(  array( "uid" => "220072", "name" => "providedpasswordcopy", "title" => "Повторите пароль", "type" => "string", 'virtual' => true )  );

/**
YES NO - checkbox (SQL 1/0)
A or B - radiobutton (SQL 1/2)
*/
$GLOBALS['CONFIG']['FIELD'][91] = new FieldMeta(  array( "uid" => "91", "name" => "face", "title" => "Лицевая", "type" => "option", "values" => array(array('yes'=>'да'), array('no'=>'нет')) )  );

$GLOBALS['CONFIG']['FIELD'][110] = new FieldMeta(  array( "uid" => "110", "name" => "name", "title" => "Имя", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][114] = new FieldMeta(  array( "uid" => "114", "name" => "phone", "title" => "Номер телефона", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][115] = new FieldMeta(  array( "uid" => "115", "name" => "localphone", "title" => "Стационарный телефон", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][141] = new FieldMeta(  array( "uid" => "141", "name" => "position", "title" => "Должность", "type" => "text" )  );

$GLOBALS['CONFIG']['FIELD'][800] = new FieldMeta(  array( "uid" => "800", "name" => "wallet", "title" => "Счет", "type" => "money", "units" => 'у.е.' )  );
$GLOBALS['CONFIG']['FIELD'][801] = new FieldMeta(  array( "uid" => "801", "name" => "bonus", "title" => "Бонусный счет", "type" => "float", "units" => 'у.е.' )  );

$GLOBALS['CONFIG']['FIELD'][50] = new FieldMeta(  array( "uid" => "50", "name" => "quota", "title"=>"Quota (Mb)", "type" => "integer" )  );
$GLOBALS['CONFIG']['FIELD'][550] = new FieldMeta(  array( "uid" => "550", "name" => "capacity", "title"=>"Объем (Mb)", "type" => "integer" )  );




$GLOBALS['CONFIG']['FIELD'][999] = new FieldMeta(  array( "uid" => "999", "name" => "urnlink", "title" => "URN link", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][9991] = new FieldMeta(  array( "uid" => "9991", "name" => "lastinnerlink", "title" => "Inner URN link", "type" => "string", 'disabled' => true )  );


$GLOBALS['CONFIG']['FIELD'][9981] = new FieldMeta(  array( "uid" => "9981", "name" => "_parent", "title" => "tree parent", "type" => "integer" )  );

$GLOBALS['CONFIG']['FIELD'][129] = new FieldMeta(  array( "uid" => "129", "name" => "login", "title" => "Login", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][987] = new FieldMeta(  array( "uid" => "987", "name" => "ip", "title" => "IP адрес", "type" => "ipv4" )  );
$GLOBALS['CONFIG']['FIELD'][988] = new FieldMeta(  array( "uid" => "988", "name" => "host", "title" => "hostname", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][9876] = new FieldMeta(  array( "uid" => "9876", "name" => "params", "title" => "params json", "type" => "string" )  );
$GLOBALS['CONFIG']['FIELD'][9877] = new FieldMeta(  array( "uid" => "9877", "name" => "struct", "title" => "Структура", "type" => "json", "raw"=>true )  );
$GLOBALS['CONFIG']['FIELD'][9879] = new FieldMeta(  array( "uid" => "9879", "name" => "metainfo", "title" => "Meta информация", "type" => "json", "raw"=>true )  );

$GLOBALS['CONFIG']['FIELD'][108] = new FieldMeta(  array( "uid" => "108", "name" => "gender", "title" => "Пол", "type" => "option", "values" => array(array('M'=>'М'), array('F'=>'Ж')) )  );

$GLOBALS['CONFIG']['FIELD'][9876001] = new FieldMeta(  array( "uid" => "9876001", "name" => "langcode", "title" => "Код зяыка", "type" => "string" )  );

$GLOBALS['CONFIG']['FIELD'][263711] = new FieldMeta(  array( "uid" => "263711", "name" => "moderatorcomment", "title" => "Комментарий модератора", "type" => "string", 'system'=>false )  );
$GLOBALS['CONFIG']['FIELD'][26371101] = new FieldMeta(  array( "uid" => "26371101", "name" => "managercomment", "title" => "Комментарий менеджера", "type" => "string", 'system'=>false )  );
$GLOBALS['CONFIG']['FIELD'][1089103] = new FieldMeta(  array( "uid" => "1089103", "name" => "wmscount", "title" => "Число на складе", "type" => "integer", "default" => 0)  );
$GLOBALS['CONFIG']['FIELD'][1089304] = new FieldMeta(  array( "uid" => "1089304", "name" => "foreignid", "title" => "Внешний ID", "type" => "integer", "default" => 0)  );

$GLOBALS['CONFIG']['FIELD'][9710102] = new FieldMeta(  array( "uid" => "9710102", "name" => "wrbacgroups", "title" => "WRBAC Groups", "type" => "iarray", "default" => '{}')  );

?>