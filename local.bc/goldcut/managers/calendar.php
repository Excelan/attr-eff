<?php

class Calendar extends EManager
{
    protected function config()
    {
        $this->behaviors[] = 'general_crud';
        $this->behaviors[] = 'general_graph';
        $this->behaviors[] = 'general_list';
        $this->behaviors[] = 'general_multylang';
        $this->behaviors[] = 'general_clone';
        $this->behaviors[] = 'general_ordered';
    }
}

?>