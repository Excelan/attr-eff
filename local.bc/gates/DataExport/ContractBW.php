<?php
namespace DataExport;

class ContractBW extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>