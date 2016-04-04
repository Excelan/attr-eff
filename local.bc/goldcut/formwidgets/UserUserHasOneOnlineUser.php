<?php 
class UserUserHasOneOnlineUser extends FormWidget
{
	protected function build()
	{
		$ff = $this->subject->name;
		$online = $this->eo->$ff;
		if (count($online)) $this->html = "Пользователь вошел с IP {$online->ip} в {$online->created}";
		else $this->html = 'У пользователя нет Online записи';
	}
}
?>