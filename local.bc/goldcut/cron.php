<?php 
require "boot.php";

if (!isCli() && OS::getOS() != 'WIN') exit();

$m = new Message();
$m->time = time();

$param = $argv[1];

if (!$param) 
{
	print 'blank argv[1]. provide hourly, dayly etc';
	exit(1);
}

Broker::instance()->send($m, "SCHEDULE", "schedule.$param");

?>