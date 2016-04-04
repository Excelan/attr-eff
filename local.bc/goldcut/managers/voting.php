<?php

class Voting extends EManager
{

	public function create($message)
	{
		$m = new Message('{"action": "increment", "urn": "urn:user"}');
		$m->urn = $message->user;
		$m->field = 'rating';
		$m->value = 1;
		$user = $m->deliver();
	
		return Entity::store($message);
	}



	public function vote($message)
	{
		$mediacontent_urn = $message->mediacontent;
		$news_urn = $message->news;

		if ($mediacontent_urn)
		{
			$upurn = $mediacontent_urn;
			$general = 'mediacontent';
		}	
		if ($news_urn)
		{
			$upurn = $news_urn;
			$general = 'news';
		}

		$v = new Message();
		$v->urn = 'urn:vote';
		$v->action = 'load';
		if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true)
			$v->ipd = $_SERVER["REMOTE_ADDR"];
		else
			$v->ipd = ip2long($_SERVER["REMOTE_ADDR"]);
		$v->$general = $upurn;
		$vx = $v->deliver();
		//$vx = false;
		if ($vx && $vx->count() > 0) 
		{
			$hasvote = new Message('{"warn":"has_vote","ip":"'.$_SERVER["REMOTE_ADDR"].'","votes": '.$vx->count().'}');			
			return $hasvote;
		}
		else
		{
			$v = new Message();
			$v->urn = 'urn:vote';
			$v->action = 'create';
			$v->ipd = ip2long($_SERVER["REMOTE_ADDR"]);
			$v->date = date('Y-m-d');
			$v->time = date('H:i:s');
			$v->$general = $upurn;
			$vx = $v->deliver();
			
			$up = new Message();
			$up->urn = $upurn;
			$up->action = 'increment';
			$up->field = 'rating';
			$up->value = 1;
			$up->deliver();

			$voted = new Message('{"result":"ok","ip":"'.$_SERVER["REMOTE_ADDR"].'"}');			
			return $voted;
		}

	
	}

}

?>