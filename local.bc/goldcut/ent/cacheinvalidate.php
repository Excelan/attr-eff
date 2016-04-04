<?php 
	/**
	CacheInvalidate
	TODO Warm cache after invalidate
	*/
	class CacheInvalidate 
	{
		public static function all($new, $old=null)
		{
			$lang = $old->lang;
			$lang_prefix = '';
			if ($lang != DEFAULT_LANG) $lang_prefix = $lang.'/';
			//printlnd($lang_prefix);
			//printlnd($old); // DR
			//printlnd($new); // M
			// NO LANG
			if (DISABLE_CACHE === true) return null;
			
			//println(' >> update ' . $new->urn->E()->name);
			
			$cl = array();
			if ($new->urn->E()->name == 'category')
			{
				// TODO if category changed uri or title (has impact on all(by cat) news pages)
				//array_push($cl, "news", "news/collections", "news/kabluki", "news/bikini", "news/photosessions"); // if category changed text or meta (no impact on news pages)
				array_push($cl, "{$lang_prefix}news", "{$lang_prefix}news/{$new->uri}");
			}
			if ($new->urn->E()->name == 'news')
			{
				//println('+updateNews');
				array_push($cl, "{$lang_prefix}rss");
				if ($new->uri) array_push($cl, "{$lang_prefix}moda/{$new->uri}");
				if ($old->uri) array_push($cl, "{$lang_prefix}moda/{$old->uri}");
				// TODO add if old cat != new cat
				$new_resolved = $new->urn->resolve();
				$new_category = $new_resolved->category;
				if (count($new_category))
				{
					$mm = new Message();
					$mm->action = 'update';
					$mm->urn = $new_category->urn;
					$mm->uri = $new_category->uri;
					$mm->deliver();
				}
				// TODO update index of status top = 1 in new
				array_push($cl, "{$lang_prefix}news");
				//array_push($cl, "news", "news/collections", "news/kabluki", "news/bikini", "news/photosessions"); // TODO only news->category not all cats
			}
			else if ($new->urn->E()->name == 'hub')
			{
				array_push($cl, "{$lang_prefix}pearl/{$old->uri}", "{$lang_prefix}pearl/{$new->uri}");
			}
			else if ($new->urn->E()->name == 'page')
			{
				array_push($cl, "{$lang_prefix}cat", "{$lang_prefix}about");
			}
			//array_push($cl, "sitemap.xml"); // only if uri cahcged or new added. not on text changes
			$uris = array_unique($cl);
			foreach ($uris as $uri) 
			{
				//dprintln($uri);
				Cache::clear($uri);
			}
			//println(' << update ' . $new->urn->E()->name);
		}
	}
?>