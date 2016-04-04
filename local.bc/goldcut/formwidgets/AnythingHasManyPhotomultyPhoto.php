<?php 

class AnythingHasManyPhotomultyPhoto extends FormWidget
{
	
	protected function build()
	{
		try 
		{
			$ff = $this->subject->name;
	
			$langcodes = json_encode($this->subject->lang_codes());
			foreach ($this->subject->lang_codes() as $lang)
			{
				$m = new Message();
				$m->urn = 'urn:'.$ff;
				$m->action = 'load';
				$m->last = 1000; // !!!
				$m->photoalbum = $this->eo->urn;
				$m->lang = $lang;
				$onlang = $m->deliver();
				$mmonlang = json_encode($onlang->toArray());
				$this->html .= "<script> if (!mm) var mm = []; mm['$lang'] = $mmonlang; </script>";	
			}
			
			//$jsuriBase = '/goldcut/js/formwidgets/'.'AnythingHasManyAnyPhoto'.'.js';
			$jsuri = '/goldcut/js/formwidgets/'.'AnythingHasManyPhotomultyPhoto'.'.js';
			$jsfile = BASE_DIR.$jsuri;
			if ($jsfile) $this->html .= "<script src='{$jsuri}'></script>";
			
			$this->html .= "<div id='photosContainer'></div>"; // TODO! _{$ff}
			
			$this->html .= "<br><div class=\"dropbox-container\" id=\"related_photos_{$ff}\" data-entity=\"{$ff}\" data-host=\"{$this->host}\" data-destination=\"urn-{$ff}\" data-target=\"hasmanyphotos\"></div>";
			// TODO hm_{$ff}, '{$ff}'
			
			$this->html .= "<script> build_photoitem_international(mm, $langcodes); </script>";
		}
		catch (Exception $e)
		{
			$this->html = $e;
		}
	}
}	

?>