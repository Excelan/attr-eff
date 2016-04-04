<?php

class UniversalLoadApp extends WebApplication implements ApplicationAccessManaged
{
    function exclusive($path)
    {
        Log::error($path, 'uniload');
    }
}

?>