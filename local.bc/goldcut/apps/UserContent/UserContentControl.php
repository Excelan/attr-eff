<?php

class UserContentControl extends AjaxApplication implements ApplicationAccessManaged
{

	function request() {}
	
	function create()
	{
		$post = $this->message; // POST params
		
		//Log::debug($post, 'usereditable'); // look sent POST data in log/usereditable.log
		
		foreach ($post->urn->entity->usereditfields as $field)
		{
			if (Field::exists($field))
			{
				$F = Field::id($field);
				$title = $F->title;
			}
			else 
			{
				$F = Entity::ref($field);
				$title = $F->title['ru'];
			}
			if (in_array($field, $post->urn->entity->required) && !$post->$field)
			{
				throw new AjaxException("Не указан обязательный {$title}");
			}
		}
		
		$m = $post;
		$m->action = 'create';
		$created = $m->deliver();
		$m = new Message();
		$m->status = 200;
		$m->text = 'Создан';
		//$m->redirect = $created->urn->resolve()->url;
		$m->redirect = str_replace('URN', $created->urn, $this->message->_redirectNext);
		return $m;
	}
	
	function update()
	{
		$post = $this->message; // POST params
		$m = $post;
		$m->action = 'update';
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