<?php
require "boot.php";
if (substr($_GET['uri'],0,1) == "/") $_GET['uri'] = substr($_GET['uri'],1);
print GateRequest::dispatch($_GET['uri'], $_POST);
?>