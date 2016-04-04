<?php
// webrequest = http request
// api request = http ajax request. immediate answer, deferred answer to websoket (reverse address is session id) + can be sent to xmpp, email
// no params in uri. only post or websocket
// api gate (access, rates, max deferred answer hold) in: gate name, message, sender session. out: answer message or registered income mess id for deferred answer (push,pull)
	// а нужны ли вообще gate посредники и deferreds?? 
		// js клиент формирует меседж полностью без rewite in gate, а доступ проверяется ниже гейта
		// YES facebook code, liqpay payed - /legacygate/name
	// workflow справляется без них, только на сообщениях и mq
	// object flow /ad/urn/editphotos > save =? 
// gate can form new message from incoming
// file upload with inline message (admin gate?). named - just upload photo (no message needed), message will be formed (post /gate/avatar)
// rbac in emanager recieve. Message is non access controlled
// manager is gate. gate only resend command to mq. job is done in processor (mq_rpc listeners)
// I used the localStorage object for communication between tabs in some occasions. The localStorage object has an event system to tell another tab or window of the same origin that some data has changed 
// http://www.codediesel.com/javascript/sharing-messages-and-data-across-windows-using-localstorage/   http://www.codediesel.com/demos/localstorage/index.php  support http://www.html5rocks.com/ru/features/storage
// vk tabs notify http://habrahabr.ru/post/153937/
/**
Цель - в ответ на http запрос отдать веб страницу (или редирект, или ошибку)
Задачи
	/gate/name ajax (name is duplicated from urn-name for rate control only) (http get, post) urn-name, urn-name-uuid
	OR
	/someapp (legacy web app (http get-post) or new app (http get, non ajax))
	делегировать sitemap роутеру определение приложения по uri и поддомену запроса
	запустить приложение
		проверить кеш, если включен для приложения. если в кеше - отдать кеш (при условном запросе сравнить даты кеша без запроса к дб по ключу приложения (res uri и тп))
		узнать ожидаемые-допустимые GET/POST параметры и передать их
		(приложение проверяет доступ)
		(приложение возвращает данные и метаданные (title, meta keys, desc, robots allow, follow, microtags!), cache allow, modified time)
		(page builder строит html страницу из данных и шаблона)
			mobile
			web html5 (XML/s1gen.php)
			ios code generate
			android code generate
*/
class WebRequest 
{
	static function dispatch($RAWURI)
	{
		Utils::startTimer('webrequest');
		
		// URI SECURITY
		
		//$URI = Security::filterStringURI($RAWURI);
		//if ($URI != $RAWURI) throw new SecurityException("[ {$URI} : {$RAWURI} ]");
		//if ($URI != $RAWURI) { header("HTTP/1.0 503 Server Error"); print 'URI security exception'; exit(0); }
		
		$URI = $RAWURI;
		
		$SiteMap = new SiteMap();
		if (ENABLE_WEBREQUEST_LOG === true) Log::info($URI, 'webrequest');
		
		try
		{
			if (ENABLE_REQUEST_CACHE === true && $appresult = Cache::get($URI)) // TODO Dont use cache check if App not used it!
			{
				$appresult = $appresult; // unserialized
				if (ENABLE_WEBREQUEST_LOG === true) Log::debug("From cache $URI ".$appresult['metadata']->modified, 'webrequest');
			}
			else // not in cache or cache disabled
			{
				// route to App
				// run App
					// recieve data & widgets from app > webpage builder (data + template = html webpage)
				ob_start();
				$appresult = $SiteMap->route($URI);
				$nodirectoutput = ob_get_clean(); // we expect no direct output. discard all direct prints
				if (ENABLE_WEBREQUEST_LOG === true) Log::debug("First gen [$URI]. Metadata: modified: ".$appresult['metadata']->modified.', enable_cache: '.$appresult['metadata']->enable_cache, 'webrequest');
				// Cache if enabled
				if ($appresult['metadata']->enable_cache) $cachedOk = Cache::put($URI, $appresult);
			}
			// Content type
			if ($appresult['metadata']->ctype) header("Content-type: ".$appresult['metadata']->ctype);

			// IF MODIFIED
			$appdata_modified_ts = (int) $appresult['metadata']->modified;
			$appdata_expires_ts = round($appresult['metadata']->expires);
			
			$modified_gmt = http_modified_date($appdata_modified_ts);
			if ($appdata_expires_ts) 
			{
				$expires_gmt = http_modified_date($appdata_modified_ts + $appdata_expires_ts);
			}
			$etag = '"'.md5($modified_gmt).'"'; // IF has modified?
			
			if ($appdata_modified_ts)
			{
				if (ENABLE_WEBREQUEST_LOG === true) Log::debug("Has modified: {$appdata_modified_ts} ETag:{$etag} GMT:{$modified_gmt}", 'webrequest');
				if (ConditionalHttpGet($modified_gmt, $etag))
				{
					Log::debug("Conditional first request", 'webrequest');
					if ($expires_gmt) 
					{
						header("Expires: $expires_gmt");
						header("Cache-Control: max-age={$appdata_expires_ts}, public");
					}
					// TODO for RoleApp cache control private
					header("Pragma: public"); // cache
					header("ETag: $etag");
					header('Last-Modified: '.$modified_gmt);
					header('Content-Length: '.strlen($appresult['data'])); // no more chunked data atter this header
					//echo $appresult['data']; // moved down
					/**
					TODO pre gzip before cache response
					*/
				}
				else
				{
					if (ENABLE_WEBREQUEST_LOG === true) Log::debug('Conditional 304 Not Modified: '.$URI, 'webrequest');
					// контент не изменился, передам Http 304
					header('HTTP/1.0 304 Not Modified');
					exit(0);
				}
			}
			// вывод результата приложения и окончание запроса 
			//echo strlen($appresult['data']);
			echo $appresult['data'];
			//exit(0);
		}
		catch (AccessException $e) // ???????? intersect with Member app return url
		{
			Log::info('AccessException in: '. $URI, 'security');
			header("Location: /AccessException");
		}
		catch (RedirectException $e)
		{
			if (ENABLE_WEBREQUEST_LOG === true) Log::debug('Redirect permanent '.$URI.' to '.$e->getMessage(), 'webrequest');
			header("HTTP/1.0 301 Moved Permanently"); 
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 11:00:00 GMT"); // Date in the past		
			header("Location: ".$e->getMessage());
			exit(0);
		}
		catch (TempRedirectException $e)
		{
			if (ENABLE_WEBREQUEST_LOG === true) Log::debug('Redirect temp '.$URI.' to '.$e->getMessage(), 'webrequest');
			header("HTTP/1.0 302 Moved Temporarily"); 
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 11:00:00 GMT"); // Date in the past		
			header("Location: ".$e->getMessage());
			exit(0);
		}
		catch (AjaxRedirectException $e)
		{
            header("Content-type: application/json");
			if (ENABLE_WEBREQUEST_LOG === true) Log::debug('Redirect AJAX '.$URI.' to '.$e->getMessage(), 'webrequest');
			$m = new Message();
			$m->error = 'Not logged in';
			$m->status = 302;
			$m->uri = '/member/login';
			header("HTTP/1.0 403 Forbidden");
			echo $m;
			exit(0);
		}
		catch (NoAppException $e)
		{
			Log::error('No app at '.$URI, 'app');
			if (LEGACY_URLS_CATCH_ALL === true) 
			{
				header("HTTP/1.0 301 Moved Permanently");
				header('Location: /');
			}
			else
			{
				//print 'NoAppException <pre>'.$e.'</pre>';
                header("HTTP/1.0 404 Not Found");
                WebRequest::dispatch('/404');
			}
			
		}
		catch (NoResourceException $e)
		{
			Log::debug('No resource (but can be redirected) at '.$URI, 'app');
			// Redirect
			/**
			TODO count redirect, last use
			TODO optimize redirect 1>2>3 == (1>3, del 2 if 2 if fresh error) or (1>3, 1>2 if 1 and 2 both historycaly active) 
			*/
			if ($URI[strlen($URI)-1] == '/') $URI = substr($URI, 0, strlen($URI)-1);
			$mm = new Message();
			$mm->action = 'load';
			$mm->urn = 'urn:redirect';
			$mm->uri = $URI;
			$redirect = $mm->deliver();
			if (!count($redirect))
			{
				if ($URI[0] != '/') $URI = '/'.$URI;
				$mm->uri = $URI;
				$redirect = $mm->deliver();
			}
			if (count($redirect))
			{
				if (substr($redirect->target,0,1) != '/') $redirect->target = '/' . $redirect->target;
				// throw new RedirectException('/'.$redirect); TODO we cant throw here. nobody catches this
				header("HTTP/1.0 301 Moved Permanently");
				header('Location: '.$redirect->target);
				exit(0);
			}
			else
			{
				if (defined('REDIRECT_LEGACY'))
				{
					header("HTTP/1.0 301 Moved Permanently");
					header('Location: '.REDIRECT_LEGACY);
					exit(0);
				}
				else
				{
					header("HTTP/1.0 404 Not Found");
                    WebRequest::dispatch('/404');
				}
			}
		}
		catch (NoAppMethodException $e)
		{
			Log::error('No app method at '.$URI, 'app');
			// TODO 404 page with sitemap
			// TODO suggest similar pages, correct typing errors by similarity
			// TODO !!! report 404 pages
			header("HTTP/1.0 301 Moved Permanently");
			header('Location: /');
			//header("HTTP/1.0 404 Not Found");
			//if (ENV === 'DEVELOPMENT') print 'NoAppMethodException <pre>'.$e.'</pre>';
		}
        catch (NoRouteException $e)
        {
            Log::error('No route method at '.$URI, 'app');
            header("HTTP/1.0 301 Moved Permanently");
            header('Location: /');
        }
		/**
		4** - client error
		5** - server error		
		*/		
		catch (AjaxException $e)
		{
            header("Content-type: application/json");
            $ae = $e->getCode();
            if ($ae > 0) {} else {
                $ae = 503;
            }
			Log::error("AjaxException ".$e->getMessage()." in ".$URI, 'ajax');
			header("HTTP/1.0 ".$ae." Ajax soft error");

            $error = GCException::ghostBuster($e, $URI);

			$er = json_decode($e->getMessage());
			$m = new Message();
			$m->status = $e->getCode();
			if ($er === NULL)
			{
				$m->text = $e->getMessage();
			}
			else
			{
				if ($er->status > 0) $m->status = $er->status;
				$m->text = $er->text;
			}
            $m->report = $error;
			print $m;
		}
		catch (Exception $e) // General error in app
		{
            $error = GCException::ghostBuster($e, $URI);

            header("HTTP/1.0 503 Server Error");

			if (ENV === 'DEVELOPMENT')
			{
                print '<pre>'.$error.'</pre>';
			}
			else 
			{
				header('Content-Type: text/html; charset=UTF-8');
				print "Эта страница временно недоступна";
			}
		}
	
		$ctime = Utils::reportTimer('webrequest');
		if (ENABLE_WEBREQUEST_LOG === true) Log::info("@ RUN APP ON URI:[$URI] IN TIME: [{$ctime['time']}]",'webrequest');
		
	}
}

?>