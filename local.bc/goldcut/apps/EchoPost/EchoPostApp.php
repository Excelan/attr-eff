<?php

class EchoPostApp extends WebApplication implements ApplicationAccessManaged
{
	function request()
	{
		$this->view = false;
		echo <<<HTML
		<script>
		ws = new WebSocket('ws://localhost:5000');
		ws.onmessage(function (msg){ console . log(msg)});
		ws.send('User271828');
		ws.send('My message!');
		</script>
HTML;



	}
}

?>