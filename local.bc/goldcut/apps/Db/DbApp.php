<?php
class DbApp extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional {

     function request()
     {
          $this->layout = false;
     }

}
?>