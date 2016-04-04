<?php

class CompanyprocessPlugin extends RowPlugin
{

	public function adminview()
	{
        return $this->ROW->name;
	}

}

?>