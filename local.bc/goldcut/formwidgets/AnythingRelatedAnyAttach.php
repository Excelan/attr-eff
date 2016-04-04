<?php 
/*
$str = <<<EOD
Parse $vars
EOD;

$str = <<<'EOD'
No parse
EOD;
*/

class AnythingRelatedAnyAttach extends FormWidget
{
	protected function build()
	{		
		$ff = $this->subject->name;
		$this->setData($this->eo->$ff);

		$mm = json_encode($this->data->toArray());
		$this->html .= "<script> var mm = $mm; </script>"; // ho_{$ff} = $ho;
		
		$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
		$jsfile = BASE_DIR.$jsuri;
		if ($jsfile) $this->html .= "<script src='{$jsuri}'></script>";
		
		$this->html .= "<div id='photosContainer'></div>"; // TODO _{$ff}
		
		$this->html .= "<br><div class=\"dropbox-container\" id=\"related_photos_{$ff}\" data-entity=\"{$ff}\" data-host=\"{$this->host}\" data-target=\"hasmanyphotos\"></div>"; //  
		// TODO hm_{$ff}, '{$ff}'
		$this->html .= "<script> build_photoitem(mm); </script>";
	}
}	
?>