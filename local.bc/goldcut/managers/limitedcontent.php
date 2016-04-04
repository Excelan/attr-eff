<?php

class LimitedContent extends EManager
{
	protected $limits = true;
	
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
		$this->behaviors[] = 'general_list';
		$this->behaviors[] = 'general_multylang';
		$this->behaviors[] = 'general_clone';
		$this->behaviors[] = 'general_report';
		$this->behaviors[] = 'general_moderate';
		
	}
}

?>