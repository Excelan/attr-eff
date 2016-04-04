<?php
namespace Events;

class GetLastComments extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

		$m = new \Message();
		$m->action = 'load';
		$m->urn = "urn:Communication:Comment:Level2withEditingSuggestion";
		$m->cancel = true;
		$m->document = $data['subjectURN'];
		$m->order = array('created' => 'DESC');
		$m->last = 1;
		$last = $m->deliver();

		if(count($last) == 0) return ['status' => 404];

		if(!is_null($last->autor->title)) $name = $last->autor->title;
		else $name = $last->autor->urn;

		if(strlen($last->content) == 0) $content = '-';
		else $content = $last->content;

		$arr = array();
		$arr['author'] = $name;
		$arr['content'] = $content;
		$arr['created'] = date('Y-m-d H:i:s',$last->created);

		//array_push($arr,['author'=>(string)$last->autor->urn, 'content'=>$last->content, 'created'=>date('Y-m-d H:i:s',$last->created)]);


		return ['status' => $arr];
	}

}

?>