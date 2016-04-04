<?php
namespace User;

class Forgot extends \Gate
{

	function gate()
	{

        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        if (!strlen($data['email']))
        {
            //Не указан Email
            return ['status' => 301, 'redirect' => '/'];
        }

        $l = new \Message('{"action": "forgot", "urn": "urn:Actor:User:System"}');
        $l->email = trim($data['email']);
        $r = $l->deliver();

        if ($r->notify)
        {
            //Все ок
            $redirect = '/member/login';
            return ['status' => 200, 'redirect' => $redirect];
        }
        else
        {
            //нет емейла в базе
            return ['status' => 404, 'redirect' => 'member/forgot'];
        }

	}

}

?>