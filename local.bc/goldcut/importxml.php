<?php
require "boot.php";
set_time_limit(0);
if (count($argv))
	require "adminutils/import.xml.php";
else
	die('ACCESS ERROR');
?>