<?php
namespace User;

class GetUsersByPost extends \Gate
{

	function gate()
	{
		$data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $users = [];

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:Actor:User:System';
        $m->post = $data['post'];
        $r = $m->deliver();

        foreach ($r as $user) {
            array_push($users, $user->id);

        }

        return ['users' => $users];
	}

}

?>