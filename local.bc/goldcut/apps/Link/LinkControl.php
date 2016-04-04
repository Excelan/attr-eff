<?php

class LinkControl extends AjaxApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
	function request()
	{
		$d = $this->message;
		$listname = $d->listname;
		
		if ($d->object == 'user')
		{
			$object = $this->user->urn;
			if (!$object) 
			{
				//throw new Exception('User as object not Anonymous');
				$m = new Message();
				$m->context = $listname;
				$m->error = 'Anonymous';
				return $m;
			}
		}
		else 
			$object = $d->object;
		if ($d->subject == 'user')
			$subject = $this->user->urn;
		else 
			$subject = $d->subject;
		
		if ($d->action == 'link')
		{
			$action = 'add';
			$dir = 'to';
		}
		elseif ($d->action == 'unlink')
		{
			$action = 'remove';
			$dir = 'from';
		}
			
		try
		{
			$m = new Message();
			$m->action = $action;
			$m->urn = (string) $subject;
			$m->$dir = new URN($object.'-'.$listname);
			Log::info($m,'linkcontrol');
			$r = $m->deliver();
			
			if ($action == 'add')
				return '{"status": 200, "link": "established"}';
			elseif ($action == 'remove')
				return '{"status": 201, "link": "removed"}';
			else
				throw new Exception('Unknown list action');
		}
		catch (Exception $e)
		{
			Log::debug($e,'linkcontrol');
			return $e;
		}
		
	}
	
	function check()
	{
		$d = $this->message;
		$listname = $d->listname;
	
		if ($d->object == 'user')
		{
			$object = $this->user->urn;
			if (!$object) 
			{
				//throw new Exception('User as object not Anonymous');
				$m = new Message();
				$m->context = $listname;
				$m->error = 'Anonymous';
				return $m;
			}
		}
		else 
			$object = $d->object;
		if ($d->subject == 'user') 
			$subject = $this->user->urn;
		else 
			$subject = $d->subject;
			
		try
		{
			//Log::debug((string)$d,'linkcontrol');
			$m = new Message();
			$m->action = 'exists';
			$m->urn = (string) $subject;
			$m->in = new URN($object.'-'.$listname);
			//Log::debug($m,'linkcontrol');
			$r = $m->deliver();
			//Log::info($m,'linkcontrol');
			//Log::info($r,'linkcontrol');
			$m = new Message();
			if ($r->exists == true)
			{
				$m->exists = 1;
				//Log::info('YES','linkcontrol');
			}
			else
			{
				$m->exists = 0;
				//Log::info('NO','linkcontrol');
			}
			return $m;
		}
		catch (Exception $e)
		{
			Log::debug($e,'link-check');
			return $e;
		}
		
	}
	
}
?>