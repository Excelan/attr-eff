<?php require dirname(__FILE__).'/../boot.php';

define('DEBUG_SQL',TRUE);

class RegisterLoginForgotLoginFeed implements TestCase
{
	private $urns = array();
	private $data = array();
	
	public $fixtures = false;

	function registerUser()
	{
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
	
	function firstLogin()
	{
		$user = $this->data['user'];
		$m = new Message();
		$m->action = 'authentificate';
		$m->urn = 'urn:Actor:User:System';
		$m->email = 'm@attracti.com';
		$m->password = $user->password;
		println($m,1,TERM_VIOLET);
		$sess = $m->deliver();
		println($sess,1,TERM_VIOLET);
	}
	
	function forgotPassword()
	{
		$m = new Message('{"action": "forgot", "urn": "urn:Actor:User:System"}');
		$m->email = 'm@attracti.com';
		$new = $m->deliver();
		$this->data['user']->password = $new->newpassword;
		println($new);
		assertSelectorExists($new, 'newpassword');
	}
	
	function secondLogin()
	{
		$user = $this->data['user'];
		$m = new Message();
		$m->action = 'authentificate';
		$m->urn = 'urn:Actor:User:System';
		$m->email = 'm@attracti.com';
		$m->password = $user->password;
		$sess = $m->deliver();
		println($sess);
		assertMessageWithoutError($sess);
	}
}
?>