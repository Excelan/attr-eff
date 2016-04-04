<?php
/**
last login date
login history with ip/os/browser
FB, OpenID login
SESSION BY URN NOT HASH (32 bit weak(( )

! password in usereditfields to allow user provided pass
*/

class MemberControl extends AjaxApplication implements ApplicationFreeAccess, ApplicationUserOptional
{

	function register()
	{
		// TODO $u->autologin

		if (REGISTER_USE_CAPTCHA === true)
		{
			require (BASE_DIR.'/lib/recaptcha/recaptchalib.php');
			$publickey = RECAPTCHA_PUBLIC;
			$privatekey = RECAPTCHA_PRIVATE;
			$resp = null;
			$error = null;
			
			//Log::info(json_encode($_POST),'xxx');
			
			if ($_POST["recaptcha_response_field"])
			{
				$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $this->message->recaptcha_challenge_field, $this->message->recaptcha_response_field);
			}
			
			if (!$resp->is_valid)
			{
				$m = new Message();
				$m->status = 410;
				$m->text = 'Captcha invalid';
				throw new AjaxException($m, $m->status);
			}	
		}
		
		$m = new Message();
		$m->urn = "urn:Actor:User:System";
		$m->action = "register";
		$m->email = $this->message->email;

        // TODO conf
        $m->autologin = 1;

		$E = Entity::ref('Actor:User:System');
		foreach ($E->usereditfields as $fname)
		{
			$m->$fname = $this->message->$fname;
		}
		
		if ($this->message->providedpassword)
		{
			$m->providedpassword = $this->message->providedpassword;
			$m->providedpasswordcopy = $this->message->providedpasswordcopy;
		}
		
		Log::info($m, 'register');
		
		$r = $m->deliver();
		if ($r->error)
		{
			$m = new Message();
			$m->status = 400;
			$m->text = $r->text;
			throw new AjaxException($m, $m->status);
			//$this->view = 'error';
			//$this->context['error'] = $r;
		}
		else
		{
			if (!$this->message->providedpassword)
			{
                //Log::info('no pass, sent to email. now login', 'register');
				//Session::put('flash', "WE SENT YOU EMAIL WITH PASSWORD", false);
				$TO = "/member/login?login={$this->message->email}";
			}
			else
			{
                //Log::info('auto auth from membercontrol', 'register');
				// auto login, redirect to /account
				$l = new Message('{"action": "authentificate", "urn": "urn:Actor:User:System"}');
				$l->email = $m->email;
				$l->password = $m->providedpassword;
				$r = $l->deliver();
				if (!$r->error) $TO = '/account';
				else throw new Exception('Autologin failed '.$r->error);
			}
			$m = new Message();
			$m->status = 307;
			$m->redirect = $TO;
			return $m;
		}
	}
	
	function login()
	{
		$m = $this->message;
		$l = new Message('{"action": "authentificate", "urn": "urn:Actor:User:System"}');
		$l->email = ltrim(rtrim($m->email));
		$l->password = ltrim(rtrim($m->password));
		$r = $l->deliver();
		if ($r->error)
		{
			throw new AjaxException('{"text": "Неверный логин или пароль"}', 403);
			//$this->redirect("/member/login");
			//println($r->error);
			//println($r->message);
		}
		else
		{
			$returnUrl = Session::pop("AuthedReturnUrl"); // возврат к урлу, в котором нужна была авторизация
			if ($returnUrl)
				$redirect = $returnUrl;
			else
				$redirect = "/account";
			$m = new Message();
			$m->status = 200;
			$m->text = 'Добро пожаловать';
			$m->redirect = $redirect;
			return $m;
			/*
			if ($returnUrl)
				$this->redirect("/{$returnUrl}");
			else
				$this->redirect("/account");
			*/
		}
	}
	
	function logout()
	{
        if (!$this->user) throw new Exception('Not logged in');
		$l = new Message('{"action": "logout"}');
		$l->urn = $this->user->urn;
//		assertURN($l->urn);
		//printlnd($this->user);
		//println($this->user->urn);
//		println($l);
		$r = $l->deliver();
		//$this->redirect("/member/login");
		$m = new Message();
		$m->status = 200;
		$m->text = 'Сессия завершена';
		$m->redirect = '/member/login';
		return $m;
	}
	
	function forgot()
	{
		if (!strlen($this->message->email)) 
		{
			throw new AjaxException('{"text": "Не указан Email"}', 400);
		}
		$l = new Message('{"action": "forgot", "urn": "urn:Actor:User:System"}');
		$l->email = trim($this->message->email);
		$r = $l->deliver();
		//$this->context['r'] = $r;
		$m = new Message();
		if ($r->notify)
		{
			$m->status = 200;
			$m->text = "Новый пароль выслан Вам на почту";
		}
		else
		{
			throw new AjaxException('{"text": "Email не существует"}', 400);
		}
		return $m;	
	}

	
	/**
	TODO NEW MODEL PASS COMPARE!
	*/
	function changepassword()
	{
		/*
		$m = new Message();
		$m->status = 321;
		$m->text = 'inc pass';
		throw new AjaxException($m, $m->status);
		*/
		
		if (!strlen($this->message->old_password) or !strlen($this->message->password)) 
		{
			throw new AjaxException('{"text": "Не указан пароль"}', 400);
			//throw new AjaxException("Не указан пароль");
		}
		
		if ($this->user->password == sha1(ltrim(rtrim(($this->message->old_password)))))
			
		//if (true)
		{
			$newpass = ltrim(rtrim($this->message->password));
			
			$m = new Message();
			$m->urn = $this->user->urn;
			$m->action = 'update';
			$m->password = sha1($newpass);
			$m->deliver();

			$m = new Message();
			$m->status = 200;
			$m->text = 'Пароль обновлен';
			return $m;
		}
		else
		{
			$m = new Message();
			$m->status = 400;
			$m->text = 'Неверный старый пароль';
			throw new AjaxException($m, $m->status);
			//$this->view = 'incorrect.old_password';
			//$this->redirect("/member/changepassword");
		}
	}

}
?>