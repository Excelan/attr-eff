<?php
if (ADMIN_AREA !== true) require dirname(__FILE__) . '/../boot.php';

$m = new Message();
$m->time = TimeOp::now();

Broker::instance()->send($m, "SCHEDULE", "schedule.{$_GET['schedule']}");

?>