<?php
require dirname(__FILE__).'/../boot.php';
require_once('streamer.inc.php');

//Log::debug(json_encode($_COOKIE), 'upload');
//Log::debug(json_encode($_SERVER), 'upload');

$us = new Message('{"urn": "urn-user", "action": "session"}');
$sess = $us->deliver();
if ($sess->warning)
{
	//header("HTTP/1.0 503 Server Error");
	//print $sess->warning;
	//exit();
	Log::info($sess->warning, 'upload');
}
else
{
	Log::info($sess->user, 'upload');
}

$ft = new File_Streamer();
$ft->setDestination(BASE_DIR.'/original/');
$path = $ft->receive();

$destination = $_SERVER['HTTP_X_DESTINATION'];
if (!$destination) throw new Exception("No destination urn provided");

Log::info($_SERVER['HTTP_X_DESTINATION'], 'upload');
Log::info($_SERVER['HTTP_X_CONTAINER'], 'upload');

$m = new Message();
$m->action = 'create';
$m->urn = $destination;
if ($_SERVER['HTTP_X_CONTAINER'])
{
    $host = new URN($_SERVER['HTTP_X_CONTAINER']);
    $hostename = $host->entity->name;
    $m->$hostename = $host;
}
if ($sess->user) $m->user = $sess->user;
$m->file = $path;
$m->uri = basename($path);
$m->facecount = $_SERVER['HTTP_X_FACECOUNT'];
$m->facelist = $_SERVER['HTTP_X_FACELIST'];
$r = $m->deliver();
print $r;