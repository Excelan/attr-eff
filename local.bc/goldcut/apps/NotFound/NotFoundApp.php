<?php

class NotFoundApp extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
	function request()
	{
		$this->register_widget('title', 'pagetitle', array("sitetitle"=>'404 Page Not Found'));
	}
}

?>