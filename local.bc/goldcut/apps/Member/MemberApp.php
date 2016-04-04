<?php

class MemberApp extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
    public function init()
    {
        $this->register_widget('left', 'logo');
    }

    public function login()
    {
        if ($returnto = $_GET['returnto']) {
            Session::put("AuthedReturnUrl", $returnto);
        }
        /**
        если что-то не работает:
        проверить время на сервере (если часы отстают, куки поставится в прошлое и сразу удалится - сессия не начнется)
        */
        if ($flash = Session::pop('flash')) {
            $this->context['flash'] = "<div class='flash'>{$flash}</div>";
        }
        $this->context['defaultlogin'] = $_GET['login'];
        //echo 'Current role: '.$this->role.'<br>';
        //echo 'Return to: '.Session::get("AuthedReturnUrl");
    }

    public function session()
    {
        printlnd(Session::get("hash"));
        printlnd(Session::get("user"));

        printlnd($this->user);

        if ($this->role == 'USER') {
            //$this->metadata->modified = mysqldate2timestamp('2011-01-01 10:10:10');
                    //$this->metadata->modified = time();
                    println('$this->role == USER');
            println('USER');
            println($this->user);

            println($this->origin);

            if (count($this->employee)) {
                println('EMPLOYEE');
                println($this->employee->current());
            }

            if (count($this->agent)) {
                println('AGENT');
                println($this->agent->current());
            }

            println('MANAGEMENTROLE');
            if (count($this->managementrole)) {
                println($this->managementrole->current());
                println($this->managementrole->urn);
            }
        } else {
            println('$this->role != USER');
            println($this->role);
        }
            // Session already loaded in Application
            // $us = new Message('{"urn":"urn:Actor:User:System","action":"session"}');
            //$us->hash = $_POST['hash'];
            // $sess = $us->deliver();
            // println('SESSION BY COOKIE RELOAD (user есть в Application): ');
            // println($sess->origin);
            // printlnd($sess->employee);
            // printlnd($sess->ManagementPostIndividual);

            if ($_GET['hash']) {
                $us = new Message('{"urn":"urn:Actor:User:System","action":"session"}');
                $us->hash = $_GET['hash'];
                $sess = $us->deliver();
                println('SESSION MANUAL HASH RELOAD (?hash=) : ');
                println($sess);
            } else {
                println("OR USE GET ?hash=", 1, TERM_BLUE);
            }
    }

    public function changepassword()
    {
        // $this->register_widget('left', 'logo');
    }

    public function register()
    {
    }

    public function forgot()
    {
    }


    public function logout()
    {
        if (!$this->user) {
            throw new Exception('Not logged in');
        }
        $l = new Message('{"action": "logout"}');
        $l->urn = $this->user->urn;
        //assertURN($l->urn);
        //println($l);
        $r = $l->deliver();

        $this->redirect('/member/login');
        return $m;
    }
}
