<?php

class NotFoundControl extends AjaxApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
    function request()
    {
        Log::error(join('/',$this->uriComponents()), 'notfound');
    }
}

?>