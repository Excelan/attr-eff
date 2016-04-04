<?php

function onScheduleDayly1($s)
{
	Log::info('System maintenance daily '.$s->time, 'cron');
}

function onScheduleHourly1($s)
{
	Log::info('System maintenance hourly '.$s->time, 'cron');
}


$broker = Broker::instance();

$broker->queue_declare ("SCHEDULEDAILY", DURABLE, NO_ACK);
$broker->bind("SCHEDULE", "SCHEDULEDAILY", "schedule.daily");
$broker->bind_rpc ("SCHEDULEDAILY", "onScheduleDayly1");

$broker->queue_declare ("SCHEDULEDAILY", DURABLE, NO_ACK);
$broker->bind("SCHEDULE", "SCHEDULEDAILY", "schedule.hourly");
$broker->bind_rpc ("SCHEDULEDAILY", "onScheduleHourly1");

?>