<?php
namespace HardCopy;

class GetPDF extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		$urn = new \URN($data['docurn']);
		$pdfLatex = \LatexUtils::buildLatexPDF($urn);
		$latex = $pdfLatex[0];
		$pdfURI = $pdfLatex[1];

		return ['status' => 200, 'uri' => $pdfURI];
	}

}

?>
