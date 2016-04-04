<?php 
/**
Legacy MVC web app
New - Web(Http)Node. App results - main widget(resource-list)/screen html, metadata, registered widgets per places

return only data for widgets from app, NOT HTML!
Цель приложения - сформировать полную веб страницу и http заголовки или отдать ответ "кеш не изменен" (заголовки отдаются в форме массива, реальные http заголовки отдает sitemap router)
Задача приложения - проверить права доступа rbac, приготовить список виджетов и данные для них, а так же опции веб страницы и виджетов
	контроллер виджетов формирует html из их шаблонов и данных от приложения
		presentation контроллер приложений формирует body html из шаблона приложения и шаблона сайта (приложение может забросить уникальный шаблон сайта или иметь свой замещающий)

widgets can be in xml config
layout - metatags, scripts (packed), css (merged), body html
main entry - <body> or <div id="placeholder"></div>
app view - html

можно отдать все ссылки из всех виджетов, пока html открыт в виде Dom
*/
class WebApplication extends Application
{
	
	protected $base = '';
	
	// deprecate
	function setBase($base)
	{
		// Log::debug('setBase', 'ctx');
		// Log::debug($base, 'ctx');
		$this->base = $base;
		$this->context['base'] = $this->base;
		$this->context['baseuri'] = $this->base->url;
		if (SystemLocale::$REQUEST_LANG != SystemLocale::default_lang())
			$this->context['langbaseuri'] = '/'.SystemLocale::$REQUEST_LANG.$this->base->url;
		else 
			$this->context['langbaseuri'] = $this->base->url;		
	}
	
	function init()
	{
		
	}
	
	function __construct($R, $uri)
	{
		parent::__construct($R, $uri);
		
		// ?
		$this->context['currentURI'] = $this->path();
		
		// def view for /app(/index)
		if ($this->uri(1))
		{
			$this->view = $this->uri(1);
		}
		else
		{
			$this->view = 'request';
		}
		
		$this->viewpath = array();
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (ISMOBILE === true) array_push($this->viewpath, 'imview/');
			array_push($this->viewpath, 'iview/');
		}
		else
		{
			//if (SystemLocale::$REQUEST_LANG != SystemLocale::default_lang()) array_push($this->viewpath,'view-'.SystemLocale::$REQUEST_LANG.'/');
			if (ISMOBILE === true) array_push($this->viewpath, 'mview/');
			array_push($this->viewpath, 'view/');
		}
	}
	
	function register_widget($position, $widget_name, $options=null)
	{
		$this->widgets[] = array('position' => $position, 'widget_name' => $widget_name, 'options' => $options);
		//$this->widget_options[$widget_name] = $options;
		//$this->widget_options["{$position}-{$widget_name}"] = $options;
	}
	
	// view
	function load_view() 
	{
		if ($this->view === false) return false;
		if (!is_array($this->viewpath))
		{
			$viewpath = $this->path.'/'.$this->viewpath.$this->view.'.php';
			if (file_exists($viewpath))
			{
				include $viewpath;
				return true;
			}
		}
		else
		{
			foreach($this->viewpath as $vp)
			{
				$viewpath = $this->path.'/'.$vp.$this->view.'.php';
				if (file_exists($viewpath))
				{
					include $viewpath;
					return true;
				}	
			}
		}
		//echo $viewpath;		
		//else 
		throw new Exception("View template $viewpath not found");
	}
	
}
?>