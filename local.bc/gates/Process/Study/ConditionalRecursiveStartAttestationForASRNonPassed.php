<?php
namespace Process\Study;

class ConditionalRecursiveStartAttestationForASRNonPassed extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>