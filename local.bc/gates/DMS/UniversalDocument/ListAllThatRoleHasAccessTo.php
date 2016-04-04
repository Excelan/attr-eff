<?php
namespace DMS\UniversalDocument;

class ListAllThatRoleHasAccessTo extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>