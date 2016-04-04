<?php

class Limits extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';		
	}
	
	function update_security() 
	{
		printH('update_security');
	}
	function create_security() 
	{
		printH('create_security');
	}

}

?>