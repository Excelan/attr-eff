<?php

class AccountApp extends WebApplication implements ApplicationAccessManaged
{
		
	function init()
	{
		$this->view = false;
		$this->register_widget('title', 'pagetitle', array("title"=> array('Аккаунт')));
		$this->register_widget('activityfeed', 'activityfeed', array('user' => $this->user, 'limit' => 20));
		print "<p><a href='/account'>Аккаунт</a></p><br>";
	}
	
	function request()
	{
		echo '<p><a href="/account/selfedit">Изменение профиля</a></p>';
		echo '<p><a href="/usercontent">Редактируемые пользователем сущности</a></p>';
		echo '<br><hr><p><a href="/member/session">Отладка сессии</a></p>';
	}
	
	function selfedit()
	{
		echo FormUniversal::build($this->user->urn, $this->user, "/account/{$this->user->urn}/update", null, 'Сохранить', "/account/URN/selfedit", $this->user->entity->usereditfields, null, array('providedpassword','providedpasswordcopy')); // , $onlyFields
	}
	
}

?>