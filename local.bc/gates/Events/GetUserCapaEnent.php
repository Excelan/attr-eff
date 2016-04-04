<?php
namespace Events;

class GetUserCapaEnent extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		//\Log::info($r->missingdate, 'questionnaire');

		$year = $data['year'];
		$numberMonth = date('m',strtotime('10-'.$data['month'].$year));
		$stringMonth = $data['month'];
		if($numberMonth == date('m'))$numberDay = date('d');
		else $numberDay = 1;
		//$number = cal_days_in_month(CAL_GREGORIAN, $numberMonth, $year);
		$number = (int) date('t', mktime(0, 0, 0, $numberMonth, 1, $year));

		$globMiss = array();


		$m = new \Message();
		$m->action = 'load';
		$m->urn = $data['urn'];
		$capa = $m->deliver();

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Document:Correction:Capa';
		$m->DocumentCapaDeviation = (string)$capa->urn;
		$corrections = $m->deliver();


		$resultUserList = array();//---------------------------------СПИСОК ЮЗЕРОВ УЧАСТВУЮЩИХ В ПЛАНИРОВАНИИ

		//добавляем в список ответственных за мероприятие
		foreach($corrections as $correction){

			//array_push($resultUserList,$correction->controlresponsible);

            $m = new \Message();
            $m->action = 'load';
            $m->urn = (string)$correction->controlresponsible->urn;
            $cu = $m->deliver();
            array_push($resultUserList,$cu->current());

		}

		//добавляем в список основных визнатов
		foreach($capa->basevisants as $visant){

			$m = new \Message();
			$m->action = 'load';
			$m->urn = (string)$visant;
			$bv = $m->deliver();
			array_push($resultUserList,$bv->current());
		}

		//добавляем в список дополнительных визнатов
		foreach($capa->additionalvisants as $addvisant){

			$m = new \Message();
			$m->action = 'load';
			$m->urn = (string)$addvisant;
			$av = $m->deliver();
			array_push($resultUserList,$av->current());
		}


//		\Log::info($resultUserList,'rznasa');
//		\Log::info(array_unique($resultUserList),'rznasa');

		$arr = array();
		foreach(/*фильтруем одинаковых людей в списке array_unique*/array_unique($resultUserList) as $k=>$v){

			//\Log::info('k='.$k.'id='.(string)$v->urn,'rznasa');

			//загрузка дней в которые пользователю не выходит пройти
			$arrMiss = array();
			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'urn:Directory:MissingPeople:Item';
			$m->ManagementPostIndividual = (string)$v->urn;
			$m->missingdate = array(strtotime("first day of ".$stringMonth." ".$year."") , strtotime("last day of ".$stringMonth." ".$year.""));
			$missing = $m->deliver();

			foreach($missing as $s=>$r){

				array_push($arrMiss,date('d',$r->missingdate));
				array_push($globMiss,date('d',$r->missingdate));
			}

			for($o = 1 ; $o <= date('t',strtotime('10-'.$data['month'].'-'.$year)); $o++){
				if(date('N',strtotime($o.'-'.$data['month'].$year)) == 6 || date('N',strtotime($o.'-'.$data['month'].$year)) == 7){
					array_push($arrMiss,(string)$o);
					array_push($globMiss,"$o");
				}
			}

			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'urn:People:Employee:Internal';
			$m->ManagementPostIndividual = (string)$v->urn;
			$name = $m->deliver();

			$ListUserName = 'Без имени';
			if(count($name) > 0) $ListUserName = $name->title;

			//загрузка замен
			$replaceArr = array();

			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'urn:Directory:Replacement:Item';
			$m->missing = $v->id;
			$m->missingdate = array(strtotime("first day of ".$stringMonth." ".$year."") , strtotime("last day of ".$stringMonth." ".$year.""));
			$replacement = $m->deliver();

			foreach($replacement as $u=>$l){

				$listUser = array();

				$m = new \Message();
				$m->action = 'load';
				$m->urn = 'urn:People:Employee:Internal';
				$m->ManagementPostIndividual = 'urn:Management:Post:Individual:'.$l->replacement->id;
				$newName = $m->deliver();

				//if(count($newName) == 0) continue;

				$userName = 'Без имени';
				if(count($newName)>0)$userName = $newName->title;

				$userPost = 'Без должности';
				if(count($l->replacement)>0) $userPost = $l->replacement->title;


				array_push($listUser,['post'=>$userPost,'name'=>$userName,'urn'=>'urn:Management:Post:Individual:'.$l->replacement->id]);

				array_push($replaceArr,[date('d',$l->missingdate),$listUser]);

				//\Log::info($l->replacement->id, 'questionnaire');
			}

			array_push($arr,['post'=>$v->title,'name'=>$ListUserName,'urn'=>(string)$v->urn,'replace'=>$replaceArr,'missing'=>$arrMiss]);
		}



		return ['status' => $arr,'day'=>$numberDay,'mday'=>$number,'globMiss'=>array_unique($globMiss)];
	}

}

?>
