<?php

class Feedback extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
	}
}

?>