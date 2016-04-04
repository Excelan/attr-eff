<?php
namespace RBAC;

class grantAtomicRight extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>