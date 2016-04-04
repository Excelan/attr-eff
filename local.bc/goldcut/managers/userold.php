<?php 
class User extends EManager
{
	function activate($m)
	{
		$user = new Message('{"action": "load", "urn": "urn-user"}');
		$user->email = $m->email;
		$r = $user->deliver();
		if ($r->count())
		{
			$user = new Message('{"action": "update"}');
			$user->urn = $r->urn;
			$user->email = $m->email;
			$user->active = true;
			$user->deliver();
		}		
	}
	
	/**
	check exists
	start transaction
	*/
	function register($m)
	{
		dprintln('LEGACY USER MODEL', 1, TERM_GRAY);
		if (!defined('NEWUSERMODEL')) dprintln('NEWUSERMODEL NOT DEFINED', 1, TERM_GRAY);
		
		if (!strlen($m->email)) return new Message('{"error": "blank_email", "text": "Не указан email"}');
		// check email
		$user = new Message('{"action": "load", "urn": "urn-user"}');
		$user->email = $m->email;
		$r = $user->deliver();
		if (count($r)) return new Message('{"error": "user_exists", "text": "Пользователь с таким email существует"}');

		// create user
		$user = new Message('{"action": "create", "urn": "urn-user"}');
		$user->email = $m->email;
		
		$E = Entity::ref('user');
		foreach ($E->usereditfields as $fname)
			$user->$fname = $m->$fname;
		
		// activate default?
		$user->active = false; // TODO CONFIGURABLE
		
		// gen pass or use provided
		if ($m->password)
			$user->password = $m->password;
		else
			$user->password = Security::generatePassword();
		$plainPassword = $user->password; 
		$user->password = sha1($user->password);

		if ($m->id) $user->id = $m->id;

		$r = $user->deliver();
		$r->password = $plainPassword;

		Broker::instance()->send($r, "MANAGERS", "user.onregister");

		//return $createduser->asMessage();
		return $r;
	}
	
	/**
	Аутентификация по логину и паролю
	TODO если сессия уже есть (другой браузер или закрытие без логаута) - вернуть ее же
	*/
	public function authentificate($m)
	{
		if (!strlen($m->email) || !strlen($m->password))
		{
			$auth = new Message();
			$auth->error = "bad_request";
			$auth->message = "Неполный запрос";
			return $auth;
		}
		$load = new Message('{"action": "load", "urn": "urn-user"}');
		$load->email = $m->email;
		$load->password = sha1($m->password);
		$user = $load->deliver();

		$auth = new Message();
		if (!$user->isempty())
		{
			if (!$user->active && $user->lastlogin)
			{
				$auth->error = "user_valid_but_inactive";
				$auth->message = "Юзер не активен";
				return $auth;
			}
			
			// set Active, Lastlogin
			$update_user = new Message();
			$update_user->action = "update";
			$update_user->urn = $user->urn;
			$update_user->active = true; // Activate by first login
			$update_user->lastlogin = time();
			$update_user->deliver();
			
			if (!$user->lastlogin)
			{
				Broker::instance()->send($user->current(), "MANAGERS", "user.onfirstlogin");
			}
			Broker::instance()->send($user->current(), "MANAGERS", "user.onlogin");
			
			// Online save
			$su = new Message();
			$su->action = 'create';
			$su->urn = "urn-online";
			$su->hash = Security::genLoginHashNumber();
			$su->ip = ip2long($_SERVER["REMOTE_ADDR"]);
			$su->user = $user->urn;
			$createdOnlineSession = $su->deliver();
			
			// Session init
			$hash = (string) $createdOnlineSession->urn->uuid;
			$auth->user = $user->urn;
			$auth->urn = $user->urn;
			$auth->hash = $hash;
			
			Session::put("hash", (string) $hash); // INT ID of Online row
			Session::put("user", (string) $user->urn->uuid());
		}
		else
		{
			$auth->message = "Неверный пароль";
			$auth->error = "incorrect_password";
		}
		
		return $auth;
	}
	
	/**
	Used in ajax check who am I
	rename to session_by_direct_hash (non cookie)
	*/
	public function session_by_cookie($m)
	{
		$s = new Message();
		if ($m->hash)
		{
			$su = new Message();
			$su->urn = 'urn:online';
			$su->action = 'load';
			$su->id = (int) $m->hash;
			$lu = $su->deliver();
			if (count($lu))
			{
				$s->user = $lu->user->urn;
				$s->hash = $lu->hash;
				$s->created = $lu->created;
				$s->ip = long2ip($lu->ip);
			}
			else
				$s->warning = "anonymous"; 
		}
		else
		{
			$s->error = "no hash provided";
		}
		return $s;
	}

	public function session($m)
	{
		$s = new Message();
		$su = new Message();
		$su->urn = 'urn:online';
		$su->action = 'load';
		if ($hash = Session::get("hash")) // by hash in cookie
		{
			$su->id = (int) $hash;
		}
		else if (strlen($m->hash)>0) // by hash in message
		{
			$su->id = (int) $m->hash;
		}
		else
		{
			return new Message('{"error": "no_hash_in_message_or_cookie"}');
		}
		$lu = $su->deliver();
		if ($lu && count($lu) == 1) // lu - urn-online
		{
			// IS LOGGED IN
			$useronline = $lu->user;
			if (!count($useronline))
			{
				return new Message('{"error": "stale online db record. user associated user deleted"}');
			}
			$s->user = $useronline->urn;
			$s->hash = $lu->hash;
			$s->created = $lu->created;
			$s->ip = long2ip($lu->ip);
			$s->online = $lu->urn;
			
			/**
			$curip = ip2long($_SERVER["REMOTE_ADDR"]);
			if ($lu->ip != $curip)
			{
				// Session ok but ip changed
				$s = new Message();
				$s->warning = "anonymous";
				$s->reason = "cookie hash exists but user changed ip";
				$s->hash = $hash;
			}
			*/
		}
		else
		{
			// ANON
			$s->warning = "anonymous";
			$s->reason = "cookie hash exists but has no online record";
			$s->hash = $hash;
		}
		return $s;
	}

	// TODO logout with hash in message for ajax logout
	// del 1 sess or all by user_id?
	public function logout($m)
	{
		$m = null;
		$us = new Message('{"urn":"urn-user", "action":"session"}');
		$sess = $us->deliver();
		if (count($sess))
		{
			//printlnd($sess);
			$su = new Message('{"action": "delete"}');
			$su->urn = $sess->online;
			$lu = $su->deliver();
		}
		//printlnd($lu);
		Session::destroy();
		return new Message('{"warning": "logged_out"}');
	}

	// password send on forgot
	public function forgot($m)
	{
		$c = new Message('{"action": "load", "urn": "urn-user"}');
		$c->email = $m->email;
		$user = $c->deliver();
		if (count($user))
		{
			// generate password
			$newpass = Security::generatePassword();
			// HASH user password with sha1
			$update_user = new Message();
			$update_user->action = "update";
			$update_user->urn = $user->urn;
			$update_user->password = sha1($newpass);
			$update_user->deliver();
			
			$user->newpassword = $newpass;
			
			// send email, update related passworded (ftp etc)
			Broker::instance()->send($user, "MANAGERS", "user.onforgot");
			
			return new Message(array("notify" => "Password sent"));
		}
		else
			return new Message(array("error" => "email not registered"));
	}
	
	public function terminate($m)
	{
		$userURN = $m->urn;
		// $user = $userURN->resolve();
		dprintlnd($m);
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn:ad';
		$m->user = $userURN;
		$srs = $m->deliver();
		foreach ($srs as $sr)
		{
			dprintln($sr);
			$m = new Message();
			$m->action = 'delete';
			$m->urn = $sr->urn;
			$m->deliver();
		}
		
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn:online';
		$m->user = $userURN;
		$srs = $m->deliver();
		foreach ($srs as $sr)
		{
			dprintln($sr);
			$m = new Message();
			$m->action = 'delete';
			$m->urn = $sr->urn;
			$m->deliver();
		}
		
		$m = new Message();
		$m->action = 'delete';
		$m->urn = $userURN;
		$m->deliver();
		
	}
	

}
?>