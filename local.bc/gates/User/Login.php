<?php
namespace User;

class Login extends \Gate
{

	function gate()
	{
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        \Log::info($data, 'user-login');

        $l = new \Message('{"action": "authentificate", "urn": "urn:Actor:User:System"}');
        $l->email = ltrim(rtrim($data['email']));
        $l->password = ltrim(rtrim($data['password']));
        $r = $l->deliver();
        if ($r->error)
        {
            throw new \AjaxException('{"text": "Неверный логин или пароль"}', 403);
        }
        else
        {
            $returnUrl = \Session::pop("AuthedReturnUrl"); // возврат к урлу, в котором нужна была авторизация
            if ($returnUrl)
                $redirect = "/".$returnUrl;
            else
            {
                $redirect = "/member/roleroute";
            }

            return ['status' => 200, 'redirect' => $redirect];
        }
	}

}

?>