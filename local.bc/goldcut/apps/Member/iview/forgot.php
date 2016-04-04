<?php 
	extract($this->context);
	
	if ($r->notify)
		echo "<h1>Новый пароль выслан Вам на почту</h1>";
	else	
		echo "<h1>Email не существует</h1>";

	echo "<a href='/member/login'>Вход</a>";	
?>