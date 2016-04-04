<?php
namespace Questionnaire;

class getQA extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		$m = new \Message();
		$m->action = 'load';
		$m->urn = $data['urn'];
		$questionnaire = $m->deliver();



		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:Study:RegulationStudy:Q';
		$m->DocumentRegulationsTA = $questionnaire->urn;
		$questions = $m->deliver();


		$arrQ = array();
		foreach($questions as $question) {

			$m = new \Message();
			$m->action = 'load';
			$m->urn = 'urn:Study:RegulationStudy:A';
			$m->StudyRegulationStudyQ = $question->urn;
			$answers = $m->deliver();

			$arrA = array();
			foreach($answers as $answer) {
				array_push($arrA, ['urn' => (string)$answer->urn, 'text' => $answer->content]);
			}

			array_push($arrQ, ['urn' => (string)$question->urn, 'text' => $question->content, 'answers'=>$arrA]);
		}

		$time = '00:00:00';
		if($questionnaire->time < 10) $time = '00:0'.$questionnaire->time.':00';
		else $time = '00:'.$questionnaire->time.':00';


		$arr = array();
		array_push($arr, ['time' => $time, 'questions' => $arrQ]);





		return ['options' => $arr];
	}

}

?>