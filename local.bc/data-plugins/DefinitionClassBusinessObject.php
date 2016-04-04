<?php

class DefinitionClassBusinessObjectPlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->title}";
    }

}

?>