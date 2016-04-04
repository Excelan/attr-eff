<?php

class StudyRegulationStudyQPlugin extends RowPlugin
{

	public function adminview()
	{
		if ($this->ROW->content) $uri = $this->ROW->content;
		//elseif ($this->ROW->email) $uri = $this->ROW->email;
		else $uri = $this->ROW->id;
		return $uri;
	}


}

?>