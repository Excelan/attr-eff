<?php

abstract class RowPlugin
{

	protected $ENTITY;
	protected $ROW;

	function __construct($ENTITY, $ROW)
	{
		$this->ENTITY = $ENTITY;
		$this->ROW = $ROW;
	}

}
?>