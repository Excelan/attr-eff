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

	//Register in gate /User/Register

    //Login in gate /User/Login
	
	function logout()
	{
		$l = new Message('{"action": "logout"}');
		$l->urn = $this->user->urn;
		assertURN($l->urn);
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
		$l = new Message('{"action": "forgot", "urn": "urn-user"}');
		$l->email = trim($this->message->email);
		$r = $l->deliver();
		//$this->context['r'] = $r;
		$m = new Message();
		if ($r->notify)
		{
			$m->status = 200;
			$m->text = "Новый пароль выслан Вам на почту";
			//$m->text = "All Ok )";
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