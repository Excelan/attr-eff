<?php
namespace Process\Study;

class ConditionalStartUKDForASRPassed extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>