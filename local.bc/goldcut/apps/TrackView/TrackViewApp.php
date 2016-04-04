<?php

class TrackViewApp extends AjaxApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
	function request()
	{
		$this->metadata->ctype = 'application/x-javascript';
		
		if (!$_GET['urn']) 
		{
			Log::debug("NO URN in ".urldecode($_GET['u']), 'track');
			return '';
		}
		
		$urn = new URN($_GET['urn']);
		$ename = $urn->entity->name;
		
		if ($this->user->urn)
		{
			Log::debug("User track {$urn}", 'track');
			try
			{
				$m = new Message();
				$m->action = 'create';
				$m->urn = 'urn:visit';
				$m->user = $this->user->urn;
				$m->$ename = $urn;
				if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true)
					$m->ip = $_SERVER["REMOTE_ADDR"];
				else
					$m->ip = ip2long($_SERVER["REMOTE_ADDR"]);
				$visited = $m->deliver();	
			}
			catch (DatabaseDuplicateException $e) // same ip
			{
				//Log::debug($e->getMessage(),'track');
			}
			catch (Exception $e)
			{
				Log::error($e->getMessage(),'track');
			}
		}
		else
		{
			Log::debug("Anon track {$urn}", 'track');
		}
		
		$up = new Message();
		$up->urn = $urn;
		$up->action = 'increment';
		$up->field = 'countview';
		$up->value = 1;
		$up->deliver();
		
		$m = new Message();
		$m->urn = $urn;
		$m->action = 'load';
		$o = $m->deliver();
		$countvisits = $o->countview;
		
		Log::debug("Tracked {$urn} {$countvisits}", 'track');
		
		$js = <<<EOL
		var viewc = document.getElementById('viewc');
		if (viewc) viewc.innerHTML = "{$countvisits}";
		if (callbackViews) callbackViews('{$urn}', "{$countvisits}");
EOL;
		print $js;
	}
	
}
?>