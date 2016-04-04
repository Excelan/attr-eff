<?php

class DefinitionTypeBusinessObjectPlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->title}";
    }

}

?>