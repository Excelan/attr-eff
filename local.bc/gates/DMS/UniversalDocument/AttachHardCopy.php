<?php
namespace DMS\UniversalDocument;

class AttachHardCopy extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>