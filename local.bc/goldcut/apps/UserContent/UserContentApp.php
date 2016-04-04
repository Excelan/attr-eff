<?php

class UserContentApp extends WebApplication implements ApplicationAccessManaged
{
		
	function init()
	{
		$this->view = false;
		$this->register_widget('title', 'pagetitle', array("title"=> array('Аккаунт')));
		print "<p><a href='/usercontent'>Управление пользовательским контентом</a></p><br>";
	}
		
	function request()
	{
		foreach (Entity::each_managed_entity($filter) as $m => $es)
		{
			//printH($m);
			foreach($es as $entity)
			{
				if (!$entity->directmanage) continue; // skip uneditable
				if (count($entity->usereditfields)) // if xml has any usereditable fields
				{
					echo "<p>{$entity->title['ru']} <a href='/usercontent/create/{$entity->urn}'>cоздать</a> <a href='/usercontent/listing/{$entity->urn}'>список</a> </p>";
				}
			}
		}
	}

	function listing($urn)
	{
		$m = new Message();
		$m->action = 'load';
		$m->urn = $urn;
		$objects = $m->deliver();
		foreach ($objects as $o)
		{
			echo "<p>".$o->adminview()." <a href='/usercontent/edit/{$o->urn}'>редактировать</a></p>";
		}
	}	
	
	function edit($urn)
	{
		$urn = new URN($urn);
		echo FormUniversal::build($urn, $urn->resolve(), "/usercontent/{$urn}/update", null, 'Сохранить', "/usercontent/URN/edit", $urn->entity->usereditfields, null, array('providedpassword','providedpasswordcopy')); // , $onlyFields
	}
	
	function create($urnstr)
	{
		$urn = new URN($urnstr);
		//println($urn->entity->usereditfields);
		echo FormUniversal::build($urn, null, "/usercontent/create", null, 'Создать', "/usercontent/URN/edit", $urn->entity->usereditfields, null, array()); // , $onlyFields
	}
	
	
	function selfedit()
	{
		echo FormUniversal::build($this->user->urn, $this->user, "/usercontent/{$urn}/update", null, 'Сохранить', "/usercontent/URN/selfedit", $urn->entity->usereditfields, null, array('providedpassword','providedpasswordcopy')); // , $onlyFields
	}
	
}

?>