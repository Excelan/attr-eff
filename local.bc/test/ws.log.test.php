<?php
require dirname(__FILE__) . '/../goldcut/boot.php';
use WebSocket\Client;

define('DEBUG_SQL', true);

class WSLogTest implements TestCase
{
    public function logws()
    {

      //$client = new Client("ws://echo.websocket.org/");
      $client = new Client("ws://localhost:8010/"); // logger service port
      $client->send("Hello Goldcut!");
        $client->send("Hello Goldcut! 2");

      // echo $client->receive();
      // echo $client->receive();
      // echo $client->receive();
    }
}
