<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require dirname(__FILE__).'/../../goldcut/boot.php';

$root_login = 'root';
$root_password = ROOT_PASS;
if ($_COOKIE['login'])
{
	if (md5($root_login.$root_password) == $_COOKIE['login'])
		$username = 'root';
	else
		die("You have sent a bad cookie.");
}
else
{
	header('Location: /goldcut/admin/aauth.php');
	exit(0);
}

Migrate::full();
println("Pre Migrate: done");

$dblink = DB::link();
$to_sqltype = MigratePGSQL::$mapping;

foreach (Entity::each_managed_entity() as $m => $es)
{
	println("$m",1,TERM_VIOLET);
	foreach ($es as $E)
	{
		println($E,2);
		foreach ($E->general_fields() as $k => $F)
		{
			//println("$k => $F",3);
			//"ADD NEW FIELD TO DB {$entity->getTableName()}.{$c} (NOT IN ".json_encode($columns[$entity->name]).")"
			$type = $to_sqltype[$F->type];
			if (!$type) throw new Exception("Unknown sql field type. name $k of type {$F->type}");
			$def = 'NULL';
			if ($F->default) $def = $F->default;
			try {
				$q = "ALTER TABLE ".SQLQT."{$E->getTableName()}".SQLQT." ALTER COLUMN ".SQLQT."{$k}".SQLQT." TYPE {$type} USING \"{$k}\"::{$type}";
				//println($q,3,TERM_GRAY);
				$dblink->nquery($q);
			}
			catch (Exception $e)
			{
				println($e->getMessage(),1,TERM_RED);
				$q = "ALTER TABLE ".SQLQT."{$E->getTableName()}".SQLQT." DROP ".SQLQT."{$k}".SQLQT;
				println($q,3,TERM_YELLOW);
				$dblink->nquery($q);
			}
		}
	}
}
println("Alter table: done");

Migrate::full();
println("After Migrate: done");

?>
