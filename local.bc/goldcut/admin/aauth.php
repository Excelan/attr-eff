<?php
require dirname(__FILE__).'/../../goldcut/boot.php';

$root_login = 'root';
$root_password = ROOT_PASS;

if ($_REQUEST['username'])
{
	if ($_REQUEST['username'] == $root_login && $_REQUEST['password'] == $root_password)
	{
		setcookie('login', md5($_REQUEST['username'].$_REQUEST['password']), null, "/");
		header('Location: /goldcut/admin/');
	}
	else
	{
//		echo "<h3>Неверный пароль</h3>";
//		Log::error('Admin incorrect password', 'security');
        setcookie('login', md5($_REQUEST['username'].$_REQUEST['password']), null, "/");
        header('Location: /goldcut/admin/');
	}
}

echo '
<form method="post" action="aauth.php">
	login: <input type="text" name="username">	<br>
	password: <input type="password" name="password">	<br>
	<input type="submit" value="Log In">
</form>
';

?>