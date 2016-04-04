<?php

class ManagementPostGroupPlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->title}";
    }

}

?>