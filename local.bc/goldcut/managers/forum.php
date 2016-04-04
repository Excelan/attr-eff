<?php

class Forum extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
		$this->behaviors[] = 'general_security';
	}
}

?>