<?php
require dirname(__FILE__).'/../../goldcut/boot.php';
//define('DEBUG_SQL', true);

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

try
{
	$m = new Message($_POST);
	Log::info($m, 'admincrud');
	$r = $m->deliver();
}
catch (Exception $e)
{
	Log::error($m, 'admincrud');
	println($e);
}

if ($r->urn)
{
	$urnt = $r->urn->generalize();

	$lang = $_POST['lang'] ? $_POST['lang'] : SystemLocale::default_lang();
	if ($_POST['returnto'] == 'list')
	{
		$adminUrl = "/goldcut/admin/?urn={$urnt}&action=list&lang={$lang}";
	}
	else if ($_POST['returnto'] == 'self')
	{
		$adminUrl = "/goldcut/admin/?urn={$r->urn}&action=edit&lang={$lang}";
		//$adminUrl = $_SERVER['HTTP_REFERER'];
	}
	else
	{
		$adminUrl = $_POST['returnto'];
        $adminUrl = "/goldcut/admin/?urn={$r->urn}&action=list&lang={$lang}";
    }

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Location: {$adminUrl}");

	//echo "<meta http-equiv=refresh content='1;/goldcut/admin/?urn={$urnt}&action=list'>";
	echo "<a href='{$adminUrl}'>BACK TO ADMIN $adminUrl</a>";

}
else
{
	printH('error in query');
	println($m);
	printlnd($r);
}
?>