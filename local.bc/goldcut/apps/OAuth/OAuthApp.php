<?php

/**
REMOTE USER IDS ARE REAL 64bit. !!! 32 bit php (int) will broke them!
*/

class OAuthApp extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
	
	/**
	list all oauth providers
	*/
	function loginwith()
	{
		$this->view = false;
		
		//$v = Config::get('/ns1/core','database.abc')->string;
		//var_dump($v);
		//$v = Config::get('ns1/core','database.someparam')->option;
		//var_dump($v);
		
		
		
		/**
		$v = Config::get('site','oauth.credentials.facebook');
		var_dump($v->appid, $v->appsecret);
		
		$selectedProviders = Config::get('site', 'oauth.providers');
		
		foreach (Config::get('/manager/oauth', 'providers')->structs($selectedProviders) as $k => $s)
		{
			print_r($s->name);
			print_r($s->title->string);
			print_r($s->urloauthlogin->url);
			print "<br>";
		}
		*/
		
		/**
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn-oauth2service';
		//$m->active = true;
		$oauthProviders = $m->deliver();
		foreach ($oauthProviders as $oauthProvider)
		*/
		try
		{
			$selectedProviders = Config::get('site', 'oauth.providers');
		}
		catch (Exception $e)
		{
			if (ENV == 'DVELOPMENT')
				println($e,1,TERM_RED);
			else
				echo "<h2>Вход через социальные сети недоступен</h2>";
			return;
		}
		
		foreach (Config::get('/manager/oauth', 'providers')->structs($selectedProviders) as $name => $oauthProvider)
		{
			if (!$oauthProvider->oauthV1) // v2
			{
				// TODO !!! SECURITY! add redirect for CODE assigment
				$appCallbackURL = new URL("/oauth/{$name}/callback");
				$url = new URL($oauthProvider->urloauthlogin->url);
				//$localConf = Config::get();
				//$url->setParamIfExists('client_id', $localConf->appid);
				$url->setParamIfExists('client_id', Config::value("site/oauth.credentials.$name.appid"));
				$url->setParamIfExists('redirect_uri', $appCallbackURL->url);
				$localScope = Config::value("site/oauth.credentials.$name.scopes");
				$scope = $localScope ? $localScope : $oauthProvider->scopes;
				//printlnd($scope);
				$url->setParam('scope', $scope);
				
				echo "<h3><a href='$url'>Вход через {$oauthProvider->title->string}</a></h3>";
			}
			else // v1
			{
				$url = str_replace('{ns}', $name, "http://vsehradost.ru/oauth1/{ns}/redirect");
				echo "<h3><a href='$url'>Вход через {$oauthProvider->title->string}</a></h3>";
			}
		}
	}
	
	/**
	detect oauth provider by NS (referrer)
	
	*/
	function callback($ns)
	{
		$name = $ns;
		$this->view = false;
		
		/**
		$m = new Message();
		$m->action = 'load';
		$m->urn = 'urn-oauth2service';
		$m->scriptname = $ns;
		$m->last = 1;
		$oauthProvider = $m->deliver();
		*/
		//$oauthProvider = Config::get('/manager/oauth', 'providers.'.$name);
		$oauthProvider = Config::value("/manager/oauth/providers.$name");
		
		$code = $_GET['code'];
		//httpRequest($url, $post)
		if ($code)
		{
			//var_dump($code);
			$url = new URL($oauthProvider->urltokentrade->url);
			//$localConf = Config::get('site','oauth.credentials.'.$name);
			//$oauthProviderAppid = $localConf->appid;
			//$oauthProviderAppSecret = $localConf->appsecret;
			//$url = str_replace('{appid}', $oauthProviderAppid, $url);
			//$url = str_replace('{appsecret}', $oauthProviderAppSecret, $url);
			//$url = str_replace('{code}', $code, $url);
			$url->setParam('client_id', Config::value("site/oauth.credentials.$name.appid"));
			$url->setParam('client_secret', Config::value("site/oauth.credentials.$name.appsecret"));
			$url->setParam('code', $code);
			$callbackurl = new URL("/oauth/{$oauthProvider->name}/callback");
			$url->setParamIfExists('redirect_uri', $callbackurl->url);
			//$url = str_replace('{URI}', , $url);
			$url = (string) $url;
			
			// TRADE CODE FOR TOKEN & USER_ID
			//$url = "https://graph.facebook.com/oauth/access_token?client_id={appid}&client_secret={appsecret}&code={code}"; // &redirect_uri=$URI
			//var_dump($url->url);
			//die();
			// Одноклассники, Mail, Yandex, Google - POST (Std!) trade, Fb, Vk - GET
			// TODO !!!
			if ($oauthProvider->tokenhttpget->option) // GET token trade
			{
				$accesstr = httpRequest($url);
			}
			else // POST token trade
			{
				$urlp = parse_url($url);
				//print_r($urlp);
				//print "{$urlp['scheme']}://{$urlp['host']}{$urlp['path']}";
				$query = array();
				parse_str($urlp['query'], $query);
				//print_r($query);
				//throw new Exception('POST not realized yet');
				$accesstr = httpRequest($url, $query);
				//var_dump($accesstr);
			}
            $accesstr = $accesstr['data'];
			
			//var_dump($accesstr);
			
			// RESULT IN FORMAT k=v& OR {k: v} 
			// !!! Только FB отдает URI формат, остальные JSON
			$access = json_decode($accesstr, true);
            /*
            print '<pre>';
            var_dump($access);
            print '</pre>';
            print $accesstr;
            die();
            */
			if ($access['error']) 
			{
				print $accesstr;
				throw new Exception($access['error']['message']);
			}
			if ($ns == 'facebook') parse_str($accesstr, $access); // key=v&k=v
			
			if (!count($access)) 
			{
				var_dump($accesstr);
				var_dump($access);
				throw new Exception('Error parsing responce with access_token');
			}
			else
			{
				if ($access['error']) throw new Exception(json_encode($access));
				$access_token = $access['access_token']; // + token
				if ($access['expires']) $expires_in = (int)$access['expires'];
				if ($access['expires_in']) $expires_in = (int)$access['expires_in'];
				
				if ($ns == 'vk') // VK HAS EARLY USERID64
				{
					if ($access['user_id']) $userID64 = $access['user_id'];
					//var_dump('USER_ID',$userID64);
					if (!$userID64) throw new Exception('NO USER_ID NEAR ACCESS_TOKEN');
				}
				//var_dump('expires_in',$expires_in);
				//var_dump($access);
			}
		}
		else
		{
			print "NO OAUTH CODE!";
		}
		
		// WE HAVE ACCESS_TOKEN
		if ($access_token)
		{
			
			// RUN OAUTH SCRIPT FOR REMOTE USER INFO GET
			// $oauthProvider->scriptname MQ?
			if ($ns == 'facebook')
			{
				$url = "https://graph.facebook.com/me?access_token=$access_token";
				$info = httpRequest($url);
                $info = $info['data'];
				$info = json_decode($info, true);
				//print_r($info);
				$userID64 = $info['id'];
				$email = $info['email'];
			}
			if ($ns == 'vk')
			{
				$url = "https://api.vk.com/method/users.get?uids=$userID64&fields=uid,first_name,last_name,nickname,screen_name,sex,bdate,city,country,timezone,photo_big,photo_rec,about,counters,can_post,can_write_private_message&access_token=$access_token";
				$info = httpRequest($url);
                $info = $info['data'];
				$info = json_decode($info, true);
				// first_name] [last_name sex=2=M bdate] => DD.M.YYYY [city] => 314 [country] => 2 [timezone] => 1
				// [photo_big] => url [photo_rec] => url
				$username = $info['response'][0]['first_name'] .' '. $info['response'][0]['last_name'];
				//$userID64 = $info['uid']; // but VK has it earlier near token
			}
			if ($ns == 'yandex')
			{
				//GET 
				$url = "https://login.yandex.ru/info?format=json&oauth_token=$access_token";
				$info = httpRequest($url);
                $info = $info['data'];
				$info = json_decode($info, true);
				//print_r($info);
				$userID64 = $info['id'];
				$email = $info['emails'][0];
				if ($info['default_email']) $email = $info['default_email'];
				//display_name (nickname)
				$username = $info['real_name'];
				//print_r($info);
			}
			if ($ns == 'google')
			{
				$url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=$access_token";
				$info = httpRequest($url);
                $info = $info['data'];
				$info = json_decode($info, true);
				//print_r($info);
				$userID64 = $info['id'];
				$email = $info['email'];
				$username = $info['name'];
				// link - google plus profile
				// picture - avatar
				// gender - male
				// locale
			}
			if ($ns == 'od')
			{
				// $userID64 = info['']; ??
				
				//GET http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser&access_token={access_token}&application_key={public_key}&sig={sign}
				//sign — трахнутая на все байты md5-подпись
				//sign=hex_md5('application_key={public_key}method=users.getCurrentUser'+hex_md5({access_token}+{secret_key}))
				//http://dev.odnoklassniki.ru/wiki/display/ok/REST+API+-+users.getCurrentUser
				//http://dev.odnoklassniki.ru/wiki/display/ok/REST+API+-+users.getCurrentUser
			}
			if ($ns == 'mailru')
			{
				// $userID64 = info['']; ??
				
				//REFRESH TOKEN
				//POST /oauth/token HTTP/1.1
				//Host: connect.mail.ru
				//grant_type=refresh_token&client_id=31337&client_secret=deadbeef&refresh_token=8xLOxBtZp8
			}
			
			// WE HAVE USERID64
			if (!$userID64) throw new Exception('NO USERID64');
			
			$m = new Message();
			$m->action = 'load';
			$m->urn = 'urn:oauth2link';
			$m->userid64 = $userID64;
			$m->oauth2service = $oauthProvider->name;
			$m->last = 1;
			$oauth2link = $m->deliver();
			if (!count($oauth2link)) // no user or old user not linked with oauth 
			{
                Log::info('no user or old user not linked with oauth', 'oauth');
				if ($info['email'])
				{
                    Log::info('has info email '.$info['email'], 'oauth');
					$m = new Message();
					$m->action = 'load';
					$m->urn = 'urn:user';
					$m->email = $email;
					$existsuser = $m->deliver();
					if (count($existsuser)) // link old user with oauth cause emails are same
					{
                        Log::info('link old user with oauth cause emails are same '.$existsuser, 'oauth');
						//printlnd($existsuser->current());
						// link with
						$m = new Message();
						$m->action = 'create';
						$m->urn = 'urn:oauth2link';
						$m->userid64 = $userID64;
						$m->user = $existsuser->urn;
						$m->oauth2service = $oauthProvider->name;
						$oauth2link = $m->deliver();
						
						$userExistsAndLinked = true;
					}
				}
				if (!$userExistsAndLinked) // create new user & link with 
				{
                    Log::info('Create new user & link with', 'oauth');
					// create user
					$m = new Message();
					$m->action = 'create';
					$m->urn = 'urn:user';
					if ($email) $m->email = $email; // << NEW USER FIELDS
					$m->name = $username;
					$m->active = true;
					$m->lastlogin = time(); // DEFER TO SESSION CREATE 
					
					$plainPassword = Security::generatePassword();
					$m->password = $plainPassword;
					$m->dynamicsalt = mt_rand();
					$hashedSaltedPassword = sha1($m->dynamicsalt . $m->password . SECURITY_SALT_STATIC);
					$m->password = $hashedSaltedPassword;
					
					$newuser = $m->deliver();
					// USER CREATED
					//printlnd($newuser);
					
					/**
					$m->password = $plainPassword;
					Broker::instance()->send($m, "MANAGERS", "user.onregister");
					*/

                    Log::info('Create oauth2link', 'oauth');
					// link with
					$m = new Message();
					$m->action = 'create';
					$m->urn = 'urn:oauth2link';
					$m->userid64 = $userID64;
					$m->user = $newuser->urn;
					$m->oauth2service = $oauthProvider->name;
					$oauth2link = $m->deliver();
				}
				//
			}
			else
			{
                Log::info('Oauth2link exists', 'oauth');
				//println($oauth2link->current(),1,TERM_GREEN);
				$existsuser = $oauth2link->user;
			}
			// else // init session for exist linked user 

            Log::info('INIT SESSION', 'oauth');
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
            Log::info('SESSION PUT HASH '.$hash, 'oauth');
			//Session::put("user", (string) (count($existsuser)) ? $existsuser->urn->uuid() : $newuser->urn->uuid());
			
			// now save access token for later use
			$m = new Message();
			$m->action = 'create';
			$m->urn = 'urn:oauth2session';
			$m->oauthaccesstoken = $access_token;
			$m->user = (count($existsuser)) ? $existsuser->urn : $newuser->urn;
			$m->oauth2service = $oauthProvider->name;
			$m->deliver();

            Log::info('Create oauth2session '.$m, 'oauth');
			
			//println($m->user, 1, TERM_RED);
			$redirectLoggedUserTo = Config::value('site/login.redirect');
			$redirectLoggedUserTo = $redirectLoggedUserTo ? $redirectLoggedUserTo : '/oauth/info';
			$this->redirect($redirectLoggedUserTo);
			
		}
		else
		{
			print "TOKEN NOT TRADED!";
		}
	}
	
	
	function info()
	{
		$this->view = false;
		printlnd($this->user);
	}

}
?>