<?php

class DepartmentPlugin extends RowPlugin
{

    public function adminview()
    {
        return $this->ROW->department;
    }

}

?>