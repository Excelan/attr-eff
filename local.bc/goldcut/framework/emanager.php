<?php

class EManager
{
	public $name;
	protected $behaviors = array();
	protected $limits = false;

	function __construct($name)
	{
		$this->name = $name;
		$this->config();
	}
	
	// !override in Manager
	protected function config()
	{
		//$this->behaviors[] = 'general_crud'; // TODO remove def
	}	

	public function recieve($message) // recieve(Message $message)
	{
		// ?action
		$action = $message->action;
		if (!$action) throw new Exception("Message without action {$message}");
		
		// User space & System Space + add Class Message
		if (ENABLE_WRBAC === true && get_class($message) == 'ManagedMessage')
		{
			if (!$message->user) throw new WRBACException("Access denied. No .user in ManagedMessage");
			//dprintln("emanager recieved ManagedMessage with ->user",1,TERM_GRAY);
			$userID = $message->user->uuid->toInt();
			Log::info("$message->user $action {$message->urn}", 'wrbac');
			dprintln("$message->user $action {$message->urn}",1,TERM_GRAY);
			$userWRBAC = WRBAC::getUserByID($userID); // TODO recreate in memory user, groups add it to to user.rolesar
			if ($userWRBAC)
			{
				$es = $userWRBAC->acessibleActionsOnEntity((string)$message->urn);
				dprintln(json_encode($es).' userWRBAC.acessibleActionsOnEntity '.$message->urn,1,TERM_GRAY);
				if (!in_array($action, $es)) throw new WRBACException("Access denied. Allowed actions: ".json_encode($es));
				else Log::info("$userID do $action on {$message->urn}", 'audit');
			}
			else
				throw new WRBACException("Access denied. User {$message->user} not found in WRBAC");
		}
		
		// _security (!MOVE IT AFTER CHECK IS METHOD EXISTS!)
		if ($this->limits === true) 
		{
			// Log::info('Limited','_limit');
			$securityController = new Preaction_Limits();
			$action_pre = $action.'_pre';
			if (method_exists($securityController, $action_pre))
			{
				$er = $securityController->$action_pre($message);
			}
			if ($er->error)
			{
				if ($message->throwerrors == true)
					throw new Exception($er);
				else
					return $er;
			}
		}
		else
		{
			// Log::info('UNLimited','_limit');
		}
		
		// action!
		if (method_exists($this, $action))
		{
			$actionResult = $this->$action($message);
		}
		else
		{
			if ($d = $this->delegate($action))
				$actionResult = $d->$action($message);
			else
				throw new Exception("No manager has interest in [".print_r($action, true) . "]. Message: {$message}");
		}
		// merge result with pre_limits security check
		if ($er->status)
			$actionResult->security = $er;
		return $actionResult;
	}
		
	protected function delegate($action)
	{
		foreach($this->behaviors as $behavior)
		{
			if (class_exists($behavior, true)) // , false == dont try to autoload
			{
				$class = new $behavior;
				if (method_exists($class, $action))
				{
					return $class;
				}
			}
		}
		return false;
	}

	function __toString()
	{
		return $this->name;
	}

}
?>