<?php

class SetPlugin
{

	protected $ENTITY;
	protected $SET;

	function __construct($ENTITY, $SET)
	{
		$this->ENTITY = $ENTITY;
		$this->SET = $SET;
	}

}
?>