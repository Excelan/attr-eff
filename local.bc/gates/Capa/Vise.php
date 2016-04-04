<?php
namespace Capa;

class Vise extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}


		//если количество выбраных решений не равно количеству мероприятий по капе - возвращаем 404
		$c1 = count($data['selected_variants']);

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Document:Correction:Capa';
		$m->DocumentCapaDeviation = $data['surn'];
		$corrections = $m->deliver();

		$c2 = count($corrections);

		if($c1 != $c2){
			return ['status' => '404'];
		}



		foreach ($data['selected_variants'] as $v) {

			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'urn:Document:Solution:Correction';
			$m->DocumentCorrectionCapa = $v['correctionUrn'];
			$solutions = $m->deliver();

			foreach($solutions as $solution){
				$m = new \Message();
				$m->action = 'remove';
				$m->urn = $data['userUrn'];
				$m->from = new \URN($solution->urn.':visauser');
				$m->deliver();
			}


			$m = new \Message();
			$m->action = 'exists';
			$m->urn = $data['userUrn'];
			$m->in = new \URN($v['solutionUrn'].':visauser');
			$e = $m->deliver();

			if($e->exists == 0) {
				$m = new \Message();
				$m->action = 'add';
				$m->urn = $data['userUrn'];
				$m->to = new \URN($v['solutionUrn'] . ':visauser');
				$m->deliver();
			}

		}


		return ['status' => 501];
	}

}

?>