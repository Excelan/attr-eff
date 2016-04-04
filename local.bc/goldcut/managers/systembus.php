<?php

class SystemBus extends EManager
{
    protected function config()
    {
        $this->behaviors[] = 'general_crud';
    }
}

?>