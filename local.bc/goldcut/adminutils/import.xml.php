<?php 
gc_enable();

$imported_callback_each = function($created) {
	//println($created->urn,1,TERM_GRAY);
};
$imported_callback_before = function($entity) {
	//println("urn-".$entity->name." ", TERM_BLUE);
};
$imported_callback_after = function($entity) {
	//if (is_web_request()) echo '<br>'; 
	//print "\n";
};

define("JUSTSTORE", true);

if ($_GET['pretruncate'] == 'yes' || $argv[1] == 'pretruncate')
{
    $db = DB::link();
    foreach (Entity::each_managed_entity() as $m => $es)
    {
        $db->raw_query("TRUNCATE TABLE " . SQLQT . 'mappings' . SQLQT);
        foreach($es as $entity) {
            $db->raw_query("TRUNCATE TABLE " . SQLQT . $entity->name . SQLQT);
        }
    }
}
if (!($_GET['ns']))
{
    println("Provide ns=");
    foreach (scandir(FIXTURES_DIR) as $dir)
    {
        if (strpos($dir,'.') === 0) continue;
        if (strpos($dir,'_saved') > 0) continue;
        // TODO skip incomplete
        print "<p>Import NS <a href='/goldcut/admin/?localplugin=import.xml&ns={$dir}'>{$dir}</a> with <a href='/goldcut/admin/?localplugin=import.xml&ns={$dir}&pretruncate=yes'>pre truncate</a></p>";
    }
}
else
{
    $printdebug = true;
    XMLData::iterateXMLfolders($_GET['ns'], null, null, $imported_callback_each, $imported_callback_before, $imported_callback_after, $printdebug);
}
?>