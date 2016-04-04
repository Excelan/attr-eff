<?php

class UID extends UUID
{
    protected function generate()
    {
        $id = mt_rand(100000000, 999999999);
        $this->uuid = $id;
    }
}

?>