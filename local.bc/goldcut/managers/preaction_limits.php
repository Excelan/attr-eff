<?php 
/**
General_Security
*/
class Preaction_Limits
{
	
	public function create_pre($message)
	{	
		if ($message->destination) 
			$target_urn = $message->destination;
		else 
			$target_urn = $message->urn;
		
		if ($message->user)
		{
			$m = new Message('{"action": "load", "urn": "urn-user_limits"}');
			$m->user = $message->user;
			$uid = $target_urn->entitymeta->uid;
			$m->entity = $target_urn->entitymeta->urn.'-'.$uid;
			$ename = $target_urn->entitymeta->name;
			$r = $m->deliver();
			
			if ($r && $r->count())
			{
				if ($r->used < $r->max)
				{
					$ul = new Message();
					$ul->urn = $r->urn;
					//$ul->action = 'update';
					//$ul->used = $r->used + 1;
					$ul->action = 'increment';
					$ul->field = 'used';
					$ul->value = 1;
					$ul->deliver();
					
					$m = new Message();
					$m->status = 200;
					$m->used = $ul->used;
					$m->max = $r->max;
					return $m;
				}
				else
				{
					$er = new Message();
					$er->error = "UL";
					$er->status = 900;
					$er->max = $r->max;
					$er->info = "Max user data limit created reached for {$ename}";
					return $er;
				}
			}
			// else throw new Exception('UserLimits record not exists');
		}
		else
			throw new Exception('Cant secure limit creation without owner User');
	}
	
	public function delete_pre($message)
	{
		// TODO add MINIMIM check
		if ($message->user)
		{
			$m = new Message('{"action": "load", "urn": "urn-user_limits"}');
			$m->user = $message->user;
			$uid = $message->urn->entitymeta->uid;
			$m->entity = $message->urn->entitymeta->urn.'-'.$uid;
			$ename = $message->urn->entitymeta->name;
			$r = $m->deliver();
			
			if ($r && $r->count())
			{
				if ($r->used > 0) // > MIN
				{
					$ul = new Message();
					$ul->urn = $r->urn;
					//$ul->action = 'update';
					//$ul->used = $r->used - 1;
					$ul->action = 'decrement';
					$ul->field = 'used';
					$ul->value = 1;
					$ul->deliver();
					
					$m = new Message();
					$m->status = 200;
					$m->used = $ul->used;
					$m->max = $r->max;
					return $m;
				}
			}
			// else throw new Exception('UserLimits record not exists');
		}
		else
			throw new Exception('Cant secure removal without related User');
	}
}
?>