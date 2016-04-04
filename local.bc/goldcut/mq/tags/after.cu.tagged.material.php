<?php

/**
!!! taggedEntities CONST
*/

function after_create_tagged_material($new)
{
	if (!$new->urn->entity->has_field('tags')) return;
	
	foreach (explode(',',$new->tags) as $tagtitle)
	{
		if ($tagtitle)
			$newtags []= trim($tagtitle);
	}
	
	$grow = $newtags;
	
	foreach ($grow as $tagtitle)
	{
		$tagtitle = trim($tagtitle);
		if (!$tagtitle) continue;
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn:tag';
		$m->title = $tagtitle; // by uri(title) faster
		$tag = $m->deliver();
		if (!count($tag))
		{
			$m = new Message();
			$m->action = 'create';
			$m->urn = 'urn:tag';
			$m->title = $tagtitle;
			$tag = $m->deliver();
		}
		/**
		$m = new Message();
		$m->action = 'link';
		$m->urn = $new->urn;
		$m->tag = $tag->urn;
		$m->deliver();
		*/
		
		$m = new Message();
		$m->action = 'add';
		$m->urn = $tag->urn;
		$m->to = new URN($new->urn.'-tag');
		$m->deliver();
		/*
		$m = new Message();
		$m->action = 'add';
		$m->urn = $new->urn;
		$m->to = new URN($tag->urn.'-news');
		$m->deliver();
		*/
		$m = new Message();
		$m->urn = $tag->urn;
		$m->action = 'increment';
		$m->field = 'countentity';
		$m->value = 1;
		$m->deliver();
	}
	
}


function after_update_tagged_material($m)
{
	
	$old = $m[1]; // DataRow
	$new = $m[0]; // Message
	
	//Log::info(json_encode($old),'tagged');
	
	if (!$old->urn->entity->has_field('tags')) return;
	if (!$new->tags) return;

	//Log::info('E has tags','tagged');
	
	foreach (explode(',',$old->tags) as $tagtitle)
	{
		if ($tagtitle)
			$oldtags []= trim($tagtitle);
	}
	
	foreach (explode(',',$new->tags) as $tagtitle)
	{
		if ($tagtitle)
			$newtags []= trim($tagtitle);
	}
	
	$lost = array_diff((array)$oldtags, (array)$newtags);
	$grow = array_diff((array)$newtags, (array)$oldtags);
	
	//Log::info('lost, grow'.json_encode($lost).json_encode($grow),'tagged');
	
	foreach ($lost as $unneededtag)
	{
		if (!$unneededtag) continue;
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn:tag';
		$m->title = $unneededtag;
		$deltag = $m->deliver();
		if (count($deltag))
		{
			/**
			$m = new Message();
			$m->action = 'unlink';
			$m->urn = $old->urn;
			$m->tag = $deltag->urn;
			$m->deliver();
			*/
			
			$m = new Message();
			$m->action = 'remove';
			$m->urn = $deltag->urn;
			$m->from = new URN($new->urn.'-tag');
			$m->deliver();
			
			$m = new Message();
			$m->urn = $deltag->urn;
			$m->action = 'decrement';
			$m->field = 'countentity';
			$m->value = 1;
			$m->deliver();
		}
	}
	
	foreach ($grow as $tagtitle)
	{
		$tagtitle = trim($tagtitle);
		if (!$tagtitle) continue;
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn:tag';
		$m->title = $tagtitle; // by uri(title) faster
		$tag = $m->deliver();
		if (!count($tag))
		{
			$m = new Message();
			$m->action = 'create';
			$m->urn = 'urn:tag';
			$m->title = $tagtitle;
			$tag = $m->deliver();
		}
		/**
		$m = new Message();
		$m->action = 'link';
		$m->urn = $old->urn;
		$m->tag = $tag->urn;
		$m->deliver();
		*/
		
		$m = new Message();
		$m->action = 'add';
		$m->urn = $tag->urn;
		$m->to = new URN($new->urn.'-tag');
		$m->deliver();
		
		$m = new Message();
		$m->urn = $tag->urn;
		$m->action = 'increment';
		$m->field = 'countentity';
		$m->value = 1;
		$m->deliver();
		//Log::info("$tagtitle processed",'tagged');
	}
	
}

$taggedEntities = array();
$ce = explode(' ', taggedEntities);

if (count($ce)) 
{
	$taggedEntities = $ce;
	$broker = Broker::instance();
	foreach ($taggedEntities as $te)
	{
		$broker->queue_declare ("ENITYUPDATECONSUMERTAGGED{$te}", DURABLE, NO_ACK);
		$broker->queue_declare ("ENITYCREATECONSUMERTAGGED{$te}", DURABLE, NO_ACK);
		
		$broker->bind("ENTITY", "ENITYUPDATECONSUMERTAGGED{$te}", "after.update.{$te}");
		$broker->bind_rpc ("ENITYUPDATECONSUMERTAGGED{$te}", "after_update_tagged_material");
		$broker->bind("ENTITY", "ENITYCREATECONSUMERTAGGED{$te}", "after.create.{$te}");
		$broker->bind_rpc ("ENITYCREATECONSUMERTAGGED{$te}", "after_create_tagged_material");
	}
}

?>