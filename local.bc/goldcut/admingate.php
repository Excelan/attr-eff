<?php
require "boot.php";

try {
    $m = new Message($_POST);
    Log::info($m,"gatesys");
    $r = $m->deliver();
    Log::info($r,"gatesys");
    print $r->toJson();
}
catch (Exception $e)
{
    header("HTTP/1.0 500 Ajax soft error");
    $m = new Message();
    $m->status = 500;
    $m->text = json_decode($e->getMessage());
    print $m;
}

?>