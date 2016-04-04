<?php
require "boot.php";
set_time_limit(0);
if (count($argv))
    exportData();
else
    die('ACCESS ERROR');
?>