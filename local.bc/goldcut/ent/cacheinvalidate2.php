<?php 
	/**
	CacheInvalidate
	TODO Warm cache after invalidate
	
	urn caches
	admin selector cache
	
	// TODO add if old cat != new cat
	// TODO update index of status top = 1 in new
	
	*/
	class CacheInvalidate 
	{
		public static function all($new, $old=null) // DR, M
		{
			
			if (Cache::is_disabled()) return null;
			
			//dprintln(' >> update cache ' . $new->urn->E()->name);
			
			//printlnd($old); // DataRow
			//printlnd($new); // Message
			//$old = $old->urn->resolve();
			
			$lang = $old->lang;
			$lang_prefix = '';
			if ($lang != DEFAULT_LANG) $lang_prefix = $lang.'/';
			
			// array of URIs to invalidate
			$uris = array();
			// array of URNs to invalidate
			$urns = array();
			
			// URNs invalidate
			array_push($urns, $old->urn);
			array_push($urns, $new->urn);
			
			// URLs invalidate
			array_push($uris, $old->url);
			array_push($uris, $new->url);
			
			if ($new->urn->E()->name == 'news')
			{
				$new_resolved = $new->urn->resolve();
				
				if ($new->uri) 
					array_push($uris, $new->url);
				if ($old->uri) 
					array_push($uris, $old->url);
				
				// cascade update category of news
				$new_category = $new_resolved->category;
				if (count($new_category))
				{
					$mm = new Message();
					$mm->action = 'update'; // TODO add TOUCH ACTION
					$mm->urn = $new_category->urn;
					$mm->uri = $new_category->uri; // just a "touch"
					$mm->deliver();
				}
				array_push($uris, "news"); // ?????
			}
			else
			{
				$entity = $new->urn->E();
				/**
				array_push($uris, "news", "news/{$new->uri}");
				*/
			}
					
			array_push($uris, "rss");
			array_push($uris, "sitemap.xml"); // only if uri cahcged or new added. not on text changes
			
			$uris = array_unique($uris);
			foreach ($uris as $uri) 
			{
				$uri = $lang_prefix.$uri;
				// TODO add lang prefix here!
				dprintln($uri);
				Cache::clear($uri);
			}
			//dprintln(' << cache updated ' . $new->urn->E()->name);
		}
	}
?>