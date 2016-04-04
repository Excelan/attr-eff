<?php

class PageApp extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional 
{
	function resource($uri)
	{
	
		$uri = Security::filterString09AZaz_dahed($uri);
		
		$m = new Message();
		$m->action = 'load';
		$m->urn = "urn-page";
		$m->uri = $uri;
		$read = $m->deliver();
		
		if (count($read)) $read = $read->current();
		else throw new Exception('Page not found');
		
		$read->text = Embeds::video($read->text);
		$this->context['read'] = $read;
		
		$this->register_widget('title', 'pagetitle', array("title"=> $read->title ));
		
	}
}

?>