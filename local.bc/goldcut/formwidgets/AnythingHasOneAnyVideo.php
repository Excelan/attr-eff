<?php 

class AnythingHasOneAnyVideo extends FormWidget
{
	
	protected function build()
	{
		$ff = $this->subject->name;
		$this->setData($this->eo->$ff);
		
		$this->html .= "<div id='videoContainer'></div>"; // TODO ID FOR 2 BLOCKS!
		$this->html .= "<input id='hasonevideourn' type='hidden' name='video'>";
		
		if ($this->data)
			$ho = json_encode($this->data->toArray());
		else
			$ho = '[]';
		
		{		
			$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
			$jsfile = BASE_DIR.$jsuri;
			if ($jsfile) $this->html .= "<script src='{$jsuri}'></script>";
		}
		$this->html .= "<script> var hov = $ho; </script>";
		$this->html .= "<div class=\"dropbox-container\" id=\"one_one_video__{$ff}\" data-entity=\"{$ff}\" data-target=\"hasonevideo\"></div>"; // data-host=\"{$this->host}\" // data-destination=\"urn-video\"
		
		$this->html .= "<script> build_hasonevideo(hov); </script>";
		
	}	
}	

?>