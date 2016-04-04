<?php 

class AnythingHasOneAnyPhoto extends FormWidget
{
	
	protected function build()
	{
		
		$ff = $this->subject->name;
		$this->setData($this->eo->$ff);
		
		$this->html .= "<div id='onePhoto_{$ff}'></div>"; // TODO ID FOR 2 BLOCKS! _{$ff}
		$this->html .= "<input id='hasonephotourn_{$ff}' type='hidden' name='{$ff}'>";
		
		//if ($this->subject->class == 'Photo') $this->tip = 'размеры фото: '.json_encode($this->subject->mediaoptions);
		
		if ($this->data)
		{
			$ho = json_encode($this->data->toArray());
		}
		else
		{
			$ho = '[]';
		}
		
		//$this->html .= json_encode($this->subject->required);
		$firsttext = null;
		$editableFields = array();
		foreach ($this->subject->required as $required)
		{
			$F = Field::id($required);
			if ($F->type == 'string' || $F->type == 'integer' || $F->type == 'float') $editableFields[$F->name] = $F->title;
			if (!$firsttext && $F->type == 'richtext') $firsttext[$F->name] = $F->title;
		}
		$this->html .= "<script> var editableFields_{$this->subject->name} = ".json_encode($editableFields)." </script>";
		$this->html .= "<script> var firstText_{$this->subject->name} = ".json_encode($firsttext)." </script>";
		
		$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
		$jsfile = BASE_DIR.$jsuri;
		if ($jsfile) $this->html .= "<script src='{$jsuri}'></script>";
	
		$this->html .= "<script> var ho_{$ff} = $ho; </script>";
		$this->html .= "<div class=\"dropbox-container\" id=\"one_photo_{$ff}\" data-entity=\"{$ff}\" data-destination=\"urn-{$ff}\" data-target=\"hasonephoto\"></div>"; // data-host=\"{$this->host}\"
		
		$this->html .= "<script> build_hasonephoto(ho_{$ff}, '{$ff}'); </script>";
		
	}	
}	

?>