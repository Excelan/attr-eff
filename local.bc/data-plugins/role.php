<?php

class RolePlugin extends RowPlugin
{

    public function adminview()
    {
        return $this->ROW->title;
    }
}

?>