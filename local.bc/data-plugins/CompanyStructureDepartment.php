<?php

class CompanyStructureDepartmentPlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->title}";
    }

}

?>