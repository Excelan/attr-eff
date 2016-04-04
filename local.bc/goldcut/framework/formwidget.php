<?php

class FormWidget
{
	
	protected $object, $reference, $subject, $usedas;
	
	protected $html = null;
	protected $data = null;
	protected $eo = null;
	protected $host = null;
	
	protected $tip;
	
	protected $meta = array();
	
	protected $name; // as fileName
	
	protected $title = '';
	
	private $titled = true;

	function __construct(EntityMeta $object, $reference, EntityMeta $subject, $usedas=null)
	{
		$this->object = $object;
		$this->reference = $reference;
		$this->subject = $subject;
		$this->usedas = $usedas;
		$this->name = join("", array( ucfirst($this->object->name), ucfirst($this->object->manager->name), $this->reference, ucfirst($this->subject->name), ucfirst($this->subject->manager->name)));
		$this->title = $this->subject->title['ru'];
	}

	// form every variant, try to load first or return blank widget
	private static function tryLoadAny($list)
	{
		foreach ($list as $widgetClass)
		{
			//printlnd($widgetClass);
						
			$tryLocal = FORMWIDGETS_LOCAL_DIR.'/'.$widgetClass.'.php';
			$trySystem = FORMWIDGETS_DIR.'/'.$widgetClass.'.php';
			if (file_exists($tryLocal))
			{
				//println("file_exists($tryLocal)");
				try
				{
					require_once $tryLocal;
					return $widgetClass;
					/**
					if (class_exists($widgetClass, true)) return $widgetClass;
					else
					{
						if (ENV === 'DEVELOPMENT') println("LOCAL Widget file exists at $tryLocal but CLASS $widgetClass not found in file");
						return false;
					}
					*/
				}
				catch (Exception $e)
				{
					if (ENV === 'DEVELOPMENT') println("LOCAL Widget file exists at $tryLocal but CLASS $widgetClass not found in file OR throws an Exception $e");
					return false;
				}
			}
			else if (file_exists($trySystem))
			{
				//println("file_exists($trySystem)");				
				
				require_once $trySystem;
				return $widgetClass;
				
				/**
				if (class_exists($widgetClass, false))
				{
					//if (in_array($widgetClass, array('AnyContentRelatedAnyContent'))) continue;
					return $widgetClass;
				}
				else
				{
					if (ENV === 'DEVELOPMENT') println("SYSTEM Widget file exists at $trySystem but CLASS $widgetClass not found in file");
					return false;
				}
				*/
			}
			else
			{
				$tried[] = $widgetClass;
			}
		}
		//print "<br>".join(",<br> ", $tried)."<br>";
		return false;
	}

	public static function optimal(EntityMeta $object, $reference, EntityMeta $subject, $usedas=null)
	{
		
		if (!$object) throw new Exception('NO OBJECT IN FORMWIDGET');
		if (!$subject) throw new Exception('NO SUBJECT IN FORMWIDGET');
		if (!in_array($reference, array("List", "HasOne","HasMany","Related","BelongsTo", "UseOne", "UseMany"))) throw new Exception('NO ["List", "HasOne","HasMany","Related","BelongsTo", "UseOne", "UseMany"] ($reference) REFERENCE BETWEEN [$object, $subject] IN FORMWIDGET');

		$classList[] = join("", array( ucfirst($object->name), ucfirst($object->manager->name), $reference, ucfirst($subject->name), ucfirst($subject->manager->name)));
		$classList[] = join("", array( ucfirst($object->name), ucfirst($object->manager->name), $reference, "Any", ucfirst($subject->manager->name)));
		$classList[] = join("", array( "Any", ucfirst($object->manager->name), $reference, ucfirst($subject->name), ucfirst($subject->manager->name)));
		$classList[] = join("", array( "Any", ucfirst($object->manager->name), $reference, "Any", ucfirst($subject->manager->name)));
		$classList[] = join("", array( "Any", ucfirst($object->manager->name), $reference, "Any", "thing"));		
		$classList[] = join("", array( "Any", "thing", $reference, ucfirst($subject->name), ucfirst($subject->manager->name)));
		$classList[] = join("", array( "Any", "thing", $reference, "Any", ucfirst($subject->manager->name)));
		$classList[] = join("", array( "Any", "thing", $reference, "Any", "thing"));
		
		if ($widgetClass = self::tryLoadAny($classList))
		{
			$widget = new $widgetClass($object, $reference, $subject, $usedas);
			return $widget;
		}
		else
		{
			/**
			если виджет не найден, будет загружен базовый клас с пустой реализаций и выводом сообщения "Не найден" в режиме ENV = DEVELOPMENT
			*/
			return new FormWidget($object, $reference, $subject, $usedas);
		}
	}
	
	public function setData($data)
	{
		$this->data = $data;
	}

	public function withFieldset($t)
	{
		$this->titled = $t;
	}

	public function setParentData($eo)
	{
		$this->eo = $eo;
	}
	
	public function setMeta($meta)
	{
		$this->meta = $meta;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function setHost($urn)
	{
		$this->host = $urn;
	}
	
	/**
	оригинальный метод, который виджеты переопределяют.
	*/
	protected function build()
	{
		if (ENV === 'DEVELOPMENT') 
			$this->html = "<p>Not implemented yet for $this->name </p>\n";
	}
	
	public function __toString()
	{
		return $this->makeHtml();
	}
	
	public function makeHtml()
	{
		// lazy build
		try 
		{	
			$this->build();
			if ($this->html)
			{
				if ($this->titled)
				{
					$html = "<fieldset class='gcfs'>\n";
					$html .= "<legend>{$this->title} {$this->tip}</legend>";
				}
				$html .= $this->html;
				if ($this->titled)
				{
					$html .= '</fieldset>';
				}
				return $html;
			}
			else
			{
				if (ENV === 'DEVELOPMENT')
					return "<p>Implemented but empty widget for $this->name </p>\n";
				else 
					return "";
			}
		}
		catch (Exception $e) {
			return (string) $e;
		}
	}
	
}

?>