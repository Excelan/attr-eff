<?php

class PosttypePlugin extends RowPlugin
{

    public function adminview()
    {
        return $this->ROW->posttype;
    }

}

?>