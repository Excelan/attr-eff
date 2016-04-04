<?php
namespace DMS\UniversalDocument;

class Search extends \Gate
{

	function gate()
	{
		$data = $this->data;
		return ['status' => 501];
	}

}

?>