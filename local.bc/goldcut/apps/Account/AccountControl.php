<?php

class AccountControl extends AjaxApplication implements ApplicationAccessManaged
{

	function request() {}
	
	function update()
	{
		$post = $this->message; // POST params
		$m = $post;
		$m->action = 'update';
		$m->urn = $this->user->urn;
		$created = $m->deliver();
		$m = new Message();
		$m->status = 200;
		$m->text = 'Обновлено';
		//$m->redirect = $created->urn->resolve()->url;
		$m->redirect = str_replace('URN', $created->urn, $this->message->_redirectNext);
		return $m;
	}
}
?>