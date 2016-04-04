<?php
namespace User;

class Register extends \Gate
{

	function gate()
	{

        // TODO $u->autologin

        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        \Log::info($data, 'user-register');

        $m = new \Message();
        $m->urn = "urn:Actor:User:System";
        $m->action = "register";
        $m->email = $data['email'];

        // TODO conf
        $m->autologin = 1;

        $E = \Entity::ref('Actor:User:System');
        foreach ($E->usereditfields as $fname)
        {
            $m->$fname = $data[$fname];
        }

        if ($data['providedpassword'])
        {
            $m->providedpassword = $data['providedpassword'];
            $m->providedpasswordcopy = $data['providedpasswordcopy'];
        }

        \Log::info($m, 'register');

        $r = $m->deliver();
        if ($r->error)
        {
            $m = new \Message();
            $m->status = 400;
            $m->text = $r->text;
            throw new \AjaxException($m, $m->status);
            //$this->view = 'error';
            //$this->context['error'] = $r;
        }
        else
        {
            if (!$data['providedpassword'])
            {
                //Log::info('no pass, sent to email. now login', 'register');
                //Session::put('flash', "WE SENT YOU EMAIL WITH PASSWORD", false);
                $TO = "/member/login?login={$data['email']}";
            }
            else
            {
                //Log::info('auto auth from membercontrol', 'register');
                // auto login, redirect to /account
                $l = new \Message('{"action": "authentificate", "urn": "urn:Actor:User:System"}');
                $l->email = $data['email'];
                $l->password = $data['providedpassword'];
                $r = $l->deliver();
                if (!$r->error) {

                    if ( $data['external'] == "1" ) {
                        $TO = '/';

                        $user_email = $data['email'];

                        //update user status external
                        $m = new \Message();
                        $m->action = 'load';
                        $m->urn = 'urn:Actor:User:System';
                        $m->email = $user_email;
                        $u = $m->deliver();

                        $m = new \Message();
                        $m->action = 'update';
                        $m->urn = $u->urn;
                        $m->external = true;
                        $u = $m->deliver();

                    } else {
//                        $TO = '/home';
                        $TO = '/';
                    }
                }
                else throw new \Exception('Autologin failed '.$r->error);
            }

            return ['status' => 200, 'redirect' => $TO];
        }

	}

}

?>