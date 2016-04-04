<?php

class DefinitionDocumentClassForPrototypePlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->title} ({$this->ROW->name})";
    }

}

?>