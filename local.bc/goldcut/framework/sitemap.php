<?php

class RedirectException extends Exception {}
class NoAppException extends Exception {}
class NoRouteException extends Exception {}
class NoAppMethodException extends Exception {}
class NoResourceException extends Exception {}
class TempRedirectException extends Exception {}
class AjaxRedirectException extends Exception {}

class SiteMap
{

	private $node;
	
	private $role = null;
	private $user = null;
	
	public $layout;
	
	// public $widgets;
	// public $widget_options = array();
	
	function __construct()
	{
		$this->node = $GLOBALS['CONFIG']['SITEMAP'];
	}

	function findroot($uri, $uriar)
	{
		foreach ($this->node as $k => $n) 
		{
			if ($n['uri'] == $uri) return $n;
		}
		$sr = BASE_DIR."/config/route/special_routes.php"; 
		if (file_exists($sr)) require $sr;
		return false;
	}

	function route($url, $base=null)
	{
		if (substr($url,0,1) == "/") $url = substr($url,1); // trailing slash
		$route = array();
		$uriar = explode('/', $url);
		
		// LANG
		if (in_array($uriar[0], SystemLocale::$ALL_LANGS))
		{
			$lang = array_shift($uriar);
			SystemLocale::$REQUEST_LANG = $lang;
            SystemLocale::$URILANG = $lang;
		}
		else
		{
            SystemLocale::$URILANG = null;
			SystemLocale::$REQUEST_LANG = SystemLocale::default_lang();
		}
		
		// if blank '' then /index by default
		if (!$uriar[0]) $uriar[0] = 'index';
		
		// ROUTE
		if ($R = $this->findroot($uriar[0], $uriar))
		{
			// APP RUN
//            $db = DB::link();
//            $db->begin(); // ALL APP RUN IN TRANSACTION
			try
			{
				$App = AppLoader::get($R, $uriar);
				if ($App instanceof WebApplication) @include BASE_DIR.'/helpers/item_renderers.php';
				// legacy
				$appresult = $App->runApp($App, $R, $uriar, $base); // > res HTML	$HTML = $appresult['data'];
                if ($App instanceof AjaxApplication) $appresult['metadata']->ctype = 'application/json';
				// new
				// app->run()
//                $db->commit();
			}
			catch (Exception $e) // General error in app
			{
                $e->user = $App->user;
//                $db->rollback();
				if ($App instanceof AjaxApplication) {
					$error = GCException::ghostBuster($e, $url);
					throw new AjaxException($e->getMessage(), $e->getCode(), $e);
				}
				else
					throw $e;
			}
			// here was legacy widgets
		}
		else
		{
			throw new NoAppException("Unknown root route $uriar[0]");
		}

		// widgets, layout moved to abstract Application (legacy)
		
		// full html page ready
		return array("data" => $appresult['data'], "metadata" => $appresult['metadata']);
	}
	
	
}

?>