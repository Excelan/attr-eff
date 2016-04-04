<?php
namespace Process\Study;

class ExtendSOPASRRequiredFor extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>