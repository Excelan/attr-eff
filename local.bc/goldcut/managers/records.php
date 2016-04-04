<?php

class Records extends EManager
{
    protected function config()
    {
        $this->behaviors[] = 'general_crud';
    }
}

?>