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

include "jscss.head.html";

foreach (Entity::each_managed_entity() as $m => $es)
{
	println("$m manager",1,TERM_GRAY);
    print "<ul id='dashboard-actions'>";
	foreach ($es as $E)
	{
        $show = false;

        if ($_GET['domain'] && $E->prototype->getInDomain() == $_GET['domain']) $show = true;

        if ($_GET['notdomain'] && $E->prototype->getInDomain() != $_GET['notdomain']) $show = true;

        if ($_GET['nonsystem'] && $E->is_system()) $show = false;

        if (!$E->directmanage) $show = false;

        if ($show) print "<li><a style='width: 720px; background: #eee; color: #000' href=\"/goldcut/admin/?urn=urn:{$E->prototype}&action=list&lang=ru\">{$E->title['ru']} <span style='font-sizeL 75%; color: gray;'>{$E->prototype}</span></a></li>";

        //if (!$show) println($E,2);
	}
    print "</ul>";
}

?>
<style>
#dashboard-actions li a:hover {
    background-color: yellow !important;
}
</style>
