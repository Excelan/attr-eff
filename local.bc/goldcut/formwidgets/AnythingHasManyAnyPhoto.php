<?php 
/*
$str = <<<EOD
Parse $vars
EOD;

$str = <<<'EOD'
No parse
EOD;
*/

class AnythingHasManyAnyPhoto extends FormWidget
{
	protected function build()
	{
		try 
		{
			$ff = $this->subject->name;
			$this->setData($this->eo->$ff);
	
			//if ($this->subject->class == 'Photo') $this->tip = 'размеры фото: '.json_encode($this->subject->mediaoptions);
			
			$mm = "[]";
			if ($this->data)
			{
				$mm = json_encode($this->data->toArray());
			}
			$this->html .= "<script> var mm = $mm; </script>"; // ho_{$ff} = $ho;
			
			//$this->html .= json_encode($this->subject->required);
			$firsttext = null;
			$editableFields = array();
			foreach ($this->subject->required as $required)
			{
				$F = Field::id($required);
				if ($F->type == 'string' || $F->type == 'integer' || $F->type == 'float') $editableFields[$F->name] = $F->title;
				if (!$firsttext && $F->type == 'richtext') $firsttext[$F->name] = $F->title;
			}
			$this->html .= "<script> var editableFields = ".json_encode($editableFields)." </script>";
			$this->html .= "<script> var firstText = ".json_encode($firsttext)." </script>";
			
			$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
			$jsfile = BASE_DIR.$jsuri;
			if ($jsfile) $this->html .= "<script src='{$jsuri}'></script>";
			
			$this->html .= "<div id='photosContainer'></div>"; // TODO _{$ff}
			/*
			foreach ($this->data as $img)
			{
				//$this->html .= "<input id='query-{$img->urn}' type=hidden name='{$ff}[]' value='{$img->urn}'>"; // inline in <photosContainer
				//$imgs[] = "<table border=0 width=100%><tr><td rowspan=2 width=100>{$imgc}</td></tr><tr><td><input type='text' name='{$img->urn}' value='$img->title' style='width:90%'></td><td><input type='text' name='{$img->urn}' value='$img->alt' style='width:90%'></td></tr></table>"; // for <dropbox-container>
			}
			*/
			
			
			
			$this->html .= "<br><div class=\"dropbox-container\" id=\"related_photos_{$ff}\" data-entity=\"{$ff}\" data-host=\"{$this->host}\" data-destination=\"urn-{$ff}\" data-target=\"hasmanyphotos\"></div>";
			// TODO hm_{$ff}, '{$ff}'
			$this->html .= "<script> build_photoitem(mm); </script>";
		}
		catch (Exception $e)
		{
			$this->html = $e;
		}
	}
}	
?>