<?php

require_once(BASE_DIR."/lib/twitteroauth/twitteroauth/twitteroauth.php");

/**
Callback URL defined on Twitter side
Scrope - read, read write - on tw side
*/

class OAuth1App extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
	
	function clear()
	{
		$this->view = false;
		session_start();
		session_destroy();
		print "CLEARED";
	}
	
	function redirect($ns)
	{
		$oauthProvider = Config::value('/manager/oauth/providers.twitter');
		if (!$oauthProvider) die('NO TWITTER OAUTH PROVIDER');
		
		$this->view = false;
		session_start();
		session_destroy();
		session_start();
		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth(Config::value("site/oauth.credentials.twitter.appid"), Config::value("site/oauth.credentials.twitter.appsecret"));
		/* Get temporary credentials. */
		$callback = new URL("/oauth1/twitter/callback");
		$request_token = $connection->getRequestToken($callback->url);
		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code) 
		{
			case 200:
				/* Build authorize URL and redirect user to Twitter. */
				$url = $connection->getAuthorizeURL($token);
				header('Location: '.$url); 
				break;
			default:
				/* Show notification if something went wrong. */
				//print_r($connection);
				throw new Exception('Could not connect to Twitter OAuth');
		}
		
	}
	
	/**
	detect oauth provider by NS (referrer)
	*/
	function callback($ns)
	{
		$this->view = false;
		
		$oauthProvider = Config::get('/manager/oauth', 'providers.twitter');
		if (!count($oauthProvider)) die('NO TWITTER OAUTH PROVIDER');
		/**
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn-oauth2service';
		$m->scriptname = $ns;
		$m->last = 1;
		$oauthProvider = $m->deliver();
		if (!count($oauthProvider)) die('NO SUCH OAUTH PROVIDER');
		*/
		
		session_start();	
		/* If the oauth_token is old redirect to the connect page. */
		if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) 
		{
			//$_SESSION['oauth_status'] = 'oldtoken';
			throw new Exception('the oauth_token is old redirect');
			//header('Location: /oauth1/clear');
		}
		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$connection = new TwitterOAuth(Config::value("site/oauth.credentials.twitter.appid"), Config::value("site/oauth.credentials.twitter.appsecret"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
		
		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$_SESSION['access_token'] = $access_token;
		/* Remove no longer needed request tokens */
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if (200 == $connection->http_code) 
		{
			/* The user has been verified and the access tokens can be saved for future use */
			//$_SESSION['status'] = 'verified';
			$connection = new TwitterOAuth(Config::value("site/oauth.credentials.twitter.appid"), Config::value("site/oauth.credentials.twitter.appsecret"), $access_token['oauth_token'], $access_token['oauth_token_secret']);
			/* If method is set change API call made. Test is called by default. */
			$info = $connection->get('account/verify_credentials');
			$userID64 = $info->id;
			
			$m = new Message();
			$m->action = 'load';
			$m->urn = 'urn:oauth2link';
			$m->userid64 = $userID64;
			$m->oauth2service = $oauthProvider->name;
			$m->last = 1;
			$oauth2link = $m->deliver();
			if (!count($oauth2link)) // no user or old user not linked with oauth 
			{
				// create user
				$m = new Message();
				$m->action = 'create';
				$m->urn = 'urn:user';
				$m->name = $info->name; // from Twitter
				$m->active = true;
				$m->lastlogin = time(); // DEFER TO SESSION CREATE 
				
				$plainPassword = Security::generatePassword();
				$m->password = $plainPassword;
				$m->dynamicsalt = mt_rand();
				$hashedSaltedPassword = sha1($m->dynamicsalt . $m->password . SECURITY_SALT_STATIC);
				$m->password = $hashedSaltedPassword;
				
				$newuser = $m->deliver();
				//printlnd($newuser);
				
				/**
				$m->password = $plainPassword;
				Broker::instance()->send($m, "MANAGERS", "user.onregister");
				*/
				
				// link with
				$m = new Message();
				$m->action = 'create';
				$m->urn = 'urn:oauth2link';
				$m->userid64 = $userID64;
				$m->user = $newuser->urn;
				$m->oauth2service = $oauthProvider->name;
				$oauth2link = $m->deliver();
			}
			else
			{
				//println($oauth2link->current(),1,TERM_GREEN);
				$existsuser = $oauth2link->user;
			}
			
			// !!! INIT SESSION
			$su = new Message();
			$su->action = 'create';
			$su->urn = "urn-online";
			$su->hash = Security::genLoginHashNumber();
			$su->securehash = Security::genLoginHashNumber();
			if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true)
				$su->ip = $_SERVER["REMOTE_ADDR"];
			else
				$su->ip = ip2long($_SERVER["REMOTE_ADDR"]);
			$su->user = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
			$createdOnlineSession = $su->deliver();
			
			$hash = (string) $createdOnlineSession->urn->uuid;
			$auth->user = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
			$auth->urn = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
			$auth->hash = $hash;
			Session::put("hash", (string) $hash); 
			//Session::put("user", (string) (count($existsuser)) ? $existsuser->urn->uuid() : $newuser->urn->uuid());
			
			// delete old sessions
			$m = new Message();
			$m->action = 'load';
			$m->urn = 'urn:oauth2session';
			$m->user = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
			$m->oauth2service = $oauthProvider->name;
			$sessions = $m->deliver();
			foreach ($sessions as $sess)
			{
				$m = new Message();
				$m->action = 'delete';
				$m->urn = $sess->urn;
				$m->user = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
				$m->deliver();
			}
			// now save access token for later use
			$m = new Message();
			$m->action = 'create';
			$m->urn = 'urn:oauth2session';
			$m->oauthaccesstoken = $access_token['oauth_token'];
			$m->oauthtokensecret = $access_token['oauth_token_secret'];
			$m->user = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
			$m->oauth2service = $oauthProvider->name;
			$m->created = time();
			//$m->expire = ?;
			$m->deliver();
			
			//println($m->user, 1, TERM_RED);
			
			$redirectLoggedUserTo = Config::value('site/login.redirect');
			$redirectLoggedUserTo = $redirectLoggedUserTo ? $redirectLoggedUserTo : '/oauth1/info';
			$this->redirect($redirectLoggedUserTo);
			
			//header('Location: /oauth1/success');
			//exit(0);
		}
		else 
		{
			/* Save HTTP status for error dialog on connnect page.*/
			throw new Exception($connection->http_code);
			//header('Location: /oauth1/clear');
			exit(0);
		}
		
	}
	
	function info()
	{
		$oauthProvider = Config::get('/manager/oauth', 'providers.twitter');
		if (!count($oauthProvider)) die('NO TWITTER OAUTH PROVIDER');
		
		$this->view = false;
		
		printlnd($this->user);
		
		return;
		
		session_start();
		/* If access tokens are not available redirect to connect page. */
		if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
			die('access tokens are not available redirect to connect page');
			//header('Location: ./clearsessions.php');
		}
		/* Get user access tokens out of the session. */
		$access_token = $_SESSION['access_token'];
		/* Create a TwitterOauth object with consumer/user tokens. */
		$connection = new TwitterOAuth(Config::value("site/oauth.credentials.twitter.appid"), Config::value("site/oauth.credentials.twitter.appsecret"), $access_token['oauth_token'], $access_token['oauth_token_secret']);
		/* If method is set change API call made. Test is called by default. */
		$content = $connection->get('account/verify_credentials');
		print "<pre>";
		print_r($content);
		/**
		profile_image_url
		profile_image_url_https
		name (nickname) = screen_name
		followers_count
		statuses_count
		*/
		print "</pre>";
	}
	
}
?>