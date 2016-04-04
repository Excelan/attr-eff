<?php

class GateRequest
{
    /**
     TODO COOKIES, USERID, is mobile
     * TODO GHOSTBUSTER for ajax app, gate, external
     */

    /*
     * private function currentRoleUser()
	{
		if ($this->user !== null) return true;
		$us = new Message('{"urn": "urn-user", "action": "session"}');
		$sess = $us->deliver();
		if ($sess->warning || $sess->error)
		{
			$this->role = "ANONYMOUSE";
		}
		else if ($user_urn = $sess->user)
		{
			$user = $user_urn->resolve()->current();
			$this->role = "USER";
			$this->user = $user;
            // $this->userrole = $user->role; // Lazy load
			if (ENABLE_WRBAC === true)
				WRBAC::unserializeUser($user->id);
		}
	}
     */

    static function dispatch($URI, $data)
    {
        //Utils::startTimer('gaterequest');
        Log::info($URI, 'gaterequest');
        Log::debug($data, 'gates');

        $URI = '/'.$URI;

//        println($URI,1,TERM_YELLOW);
//        printlnd($data,1,TERM_GREEN);
//        printlnd($data['b'],2,TERM_GREEN);

        $env = array('uri'=>$URI);

        $ns = str_replace('/', '\\', $URI);
        
        if (class_exists($ns)) {
            $x = new $ns($env, $data);

            try {
                $response = $x->gate($data);
                $x->checkResponse($response);
                return $response;
            } catch (Exception $e) {
                //println(get_class($e));
                //println($e,1,TERM_RED);
                //return null;
                throw $e;
            }


        }
        else
        {
            throw new Exception('Gate not found', 404);
            //return null;
        }
    }
}

?>