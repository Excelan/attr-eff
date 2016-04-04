<?php
namespace RBAC\DMS;

class PrototypeStartersToAtomicPermissions extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>