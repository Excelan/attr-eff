<?php
namespace RBAC;

class revokeAtomicRight extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>