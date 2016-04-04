<?php
namespace Capa;

class UpdateSolution extends \Gate
{

	function gate()
	{



			$data = $this->data;
			if(!is_array($data)){
				$data=$data->toArray();
			}



			$m = new \Message();
			$m->action = 'load';
			$m->urn = $data['mpe'];
			$mpe = $m->deliver();

			if($mpe->currentstage != 'Correction'){
				return ['status' => 404, 'text' => 'Сохранение невозможно. Этап обсуждения завершен!'];
			}

			$m = new \Message();
			$m->action = 'update';
			$m->urn = $data['urn'];
			if ($data['realizationtype']) $m->realizationtype = $data['realizationtype'];
			if ($data['realizationdate']) $m->realizationdate = $data['realizationdate'];
			if ($data['executor']) $m->executor = $data['executor'];
			if ($data['cost']) $m->cost = $data['cost'];
			if ($data['descriptionsolution']) $m->descriptionsolution = $data['descriptionsolution'];
			$m->deliver();





		return ['status' => 501, 'text' => $data['descriptionsolution']];
	}

}

?>