<?php

class Comment extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
	}
}

?>