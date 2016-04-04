<?php 

class AjaxApplication extends Application
{
	function __construct($R, $uri)
	{
		parent::__construct($R, $uri);
		$this->layout = 'empty'; // TODO == false
		$this->view = false;
	}

	function init()
	{
        $this->layout = 'empty';
	}	
}

?>