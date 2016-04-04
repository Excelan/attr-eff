<?php

class Data extends EManager
{
    protected function config()
    {
        $this->behaviors[] = 'general_crud';
        $this->behaviors[] = 'general_graph';
        $this->behaviors[] = 'general_list';
    }

}

?>