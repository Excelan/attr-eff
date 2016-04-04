<?php

class DocumentCapaDeviationPlugin extends RowPlugin
{

	public function adminview()
	{
		if ($this->ROW->name) $uri = $this->ROW->name;
		//elseif ($this->ROW->email) $uri = $this->ROW->email;
		else $uri = $this->ROW->id;
		return $uri;
	}


}

?>