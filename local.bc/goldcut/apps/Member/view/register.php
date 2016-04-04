<div class="FL rama rama2 FH">

	<h1>Регистрация</h1>
	
	<form action="/member/register" method="post" id="member-register" class="gcform">
	
	<label for="email"><b>Ваш e-mail</b></label> <input type="text" name="email" class="forminput" value=""><br>
	<label for="email"><b>Пароль</b></label> <input type="password" name="providedpassword" class="forminput" value=""><br>
	<label for="email"><b>Пароль (повторите)</b></label> <input type="password" name="providedpasswordcopy" class="forminput" value=""><br>

	<?php 
	if (REGISTER_USE_CAPTCHA === true)
	{
		echo '<div id="captchadiv" style="margin-top: 10px;"></div>';
		echo '<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>';
		echo '
		<script>
			function gconformfail()
			{
				Recaptcha.reload();
			}
			Recaptcha.create("'.RECAPTCHA_PUBLIC.'", "captchadiv", {
				 theme: "clean",
				 lang: "ru",
				 callback: Recaptcha.focus_response_field
			});
         </script>
		';
	}
	?>
	
	<br>
		<!-- <p>На указанный ящик электронной почты Вы получите свой пароль для <i>Входа</i> в специальную часть сайта.</p> -->
	
		<div class='formcontrols'>
			<input type="submit" value="Регистрация" class="leftfloat">
			<div class="leftfloat leftpad1 controlinfo"></div>
			<div class="rightfloat controlstatus"></div>
			<br style="clear: both;">
		</div>

	</form>
	
	<br><p><a href="/oauth/loginwith">Быстрый вход через социальные сети</a></p>

</div>