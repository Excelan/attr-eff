<?php
extract($this->context);

$standartFormRegisterEnabled = true;
if (STANDARTFORMREGISTERDISABLE == true) $standartFormRegisterEnabled = false;

$oauthEnabled = false;
try {
    $selectedProviders = Config::get('site', 'oauth.providers');
    $oauthEnabled = true;
}
catch (Exception $e) {}

?>

<?php echo $flash; ?>


<h1>Вход</h1>

<form action="/member/login" method="post" id="member-login" class="gcform">

	<label for="email">E-mail</label><input type="text" name="email" class="forminput" value="<?php echo $defaultlogin; ?>">
	<br>
	<label for="password">Пароль</label><input type="password" name="password" class="forminput" value="">
	
	<div class='formcontrols'>
		<input type="submit" value="Войти" class="leftfloat">
		<div class="leftfloat leftpad1 controlinfo"></div>
		<div class="rightfloat controlstatus"></div>
		<br style="clear: both;">
	</div>
	
</form>

<p style="font-size: 16px;">
	<a class="simplebutton" href='/member/forgot'>Забыли пароль? &mdash; мы вышлем на email новый</a>
</p>

<?php
if ($standartFormRegisterEnabled == true)
{
?>
<p class="TM2"><a class="simplebutton" href="/member/register">Регистрация (требуется E-mail)</a></p>
<?php
}
if ($oauthEnabled == true)
{
?>
    <p class="TM1"><a class="simplebutton" href="/oauth/loginwith">Простая и быстрая регистрация через социальные сети</a></p>
<?php
}
?>