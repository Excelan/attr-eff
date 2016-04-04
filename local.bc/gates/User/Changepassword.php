<?php
namespace User;

class Changepassword extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}


		if (!strlen($data['old_password']) or !strlen($data['password']))
		{
			//не введены пароли
			$redirect = '/member/changepassword';
			return ['status' => 301, 'redirect'=>$redirect];

		}

		$m = new \Message();
		$m->urn = $data['userUrn'];
		$m->action = 'load';
		$user = $m->deliver();

		$inputPassword = sha1($user->dynamicsalt . trim($data['old_password']) . SECURITY_SALT_STATIC);
		$passInBase = $user->password;

		if ($passInBase == $inputPassword){

			$newpass = trim($data['password']);

			$m = new \Message();
			$m->urn = $data['userUrn'];
			$m->action = 'update';
			$m->password = sha1($user->dynamicsalt . $newpass . SECURITY_SALT_STATIC);
			$m->deliver();

			$redirect = '/member/login';


			return ['status' => 501, 'redirect'=>$redirect];
		}
		else
		{
			return ['status' => 404, 'redirect'=> '/member/changepassword'];
		}







	}

}

?>