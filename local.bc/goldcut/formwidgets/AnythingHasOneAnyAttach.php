<?php 

class AnythingHasOneAnyAttach extends FormWidget
{
	
	protected function build()
	{
		$ff = $this->subject->name;
		$this->setData($this->eo->$ff);
		
		$this->html .= "<div id='attachContainer'></div>"; // TODO ID FOR 2 BLOCKS!
		$this->html .= "<input id='hasoneattachurn' type='hidden' name='attach'>";
		
		if ($this->data) 
			$ho = json_encode($this->data->toArray());
		else
			$ho = '[]';
		
		$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
		$jsfile = BASE_DIR.$jsuri;
		if ($jsfile) $this->html .= "<script src='{$jsuri}'></script>";
	
		$this->html .= "<script> var hoa = $ho; </script>";
		$this->html .= "<div class=\"dropbox-container\" id=\"one_one_attach_{$ff}\" data-entity=\"{$ff}\" data-target=\"hasoneattach\"></div>"; // data-host=\"{$this->host}\" // data-destination=\"urn-attach\"
		
		$this->html .= "<script> build_hasoneattach(hoa); </script>";
		
		
	}	
}	

?>