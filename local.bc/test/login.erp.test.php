<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB', true);
define('PRODUCTION_DB_IN_TEST_ENV', true);

set_time_limit(10);

class LoginERPTest implements TestCase
{
    private $urn;

    private $urns = array();
    private $data = array();

    public $fixtures = false;

    private function registerUser()
    {
        pendingTest();
        $m = new Message();
        $m->action = 'register';
        $m->urn = 'urn:Actor:User:System';
        $m->email = 'm@attracti.com';
        $m->providedpassword = '123';
        $m->providedpasswordcopy = '123';
        $m->phone = '+380674014544';
        $m->name = 'Max';
        $m->city = 'Kiev';
        $m->skype = 'maxbezugly';
        $user = $m->deliver();
        assertMessageWithoutError($user);
        $this->data['user'] = $user;
        $userreal = $user->urn->resolve();
        println($userreal->current());
        //assertEqual($userreal->city, 'Kiev');
    }

    public function firstLogin()
    {
        $user = $this->data['user'];
        $m = new Message();
        $m->action = 'authentificate';
        $m->urn = 'urn:Actor:User:System';
        $m->email = 'max@attracti.com';
        $m->password = '123'; //$user->password;
        println($m, 1, TERM_VIOLET);
        $sess = $m->deliver();
        println($sess, 1, TERM_VIOLET);
    }

    private function dropUser()
    {
        pendingTest();
        $user = $this->data['user'];
        $m = new Message();
        $m->action = 'delete';
        $m->urn = $user->urn;
        $sess = $m->deliver();
    }
}
