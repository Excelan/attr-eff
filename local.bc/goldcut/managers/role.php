<?php

class Role extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
		$this->behaviors[] = 'general_graph';
		$this->behaviors[] = 'general_list';
        $this->behaviors[] = 'general_multylang';
	}
}

?>