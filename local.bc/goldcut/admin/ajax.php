<?php
require dirname(__FILE__).'/../../goldcut/boot.php';
/*
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
*/
$base = count($_POST) ? $_POST : $_GET;  
// TODO POST only actions
$m = new Message($base);
$res = $m->deliver();

if ($res instanceof Message)
{
	print $res;
}
else if ($res instanceof DataSet)
{
	print $res->toJSON();
}
else
{
	print $res;
}

?>