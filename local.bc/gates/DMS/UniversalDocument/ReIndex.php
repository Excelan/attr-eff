<?php
namespace DMS\UniversalDocument;

class ReIndex extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>