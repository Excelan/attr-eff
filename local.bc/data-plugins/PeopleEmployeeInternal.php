<?php

class PeopleEmployeeInternalPlugin extends RowPlugin
{

	public function adminview()
	{
		if ($this->ROW->title) $uri = $this->ROW->title;
		elseif ($this->ROW->email) $uri = $this->ROW->email;
		else $uri = $this->ROW->id;
		return $uri;
	}

	public function date()
	{
		return date('m/d', $this->ROW->created);
	}

	public function time()
	{
		return date('H:i', $this->ROW->created);
	}


}

?>