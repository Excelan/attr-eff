<?php

class EchoPostControl extends AjaxApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
	function request()
	{
		Log::info($this->message, 'echo');
		if (!$this->message) Log::debug(json_encode($_POST), 'echo');

		return $this->message->json;

        //$d = new Message();
        //$d->name = json_decode($this->message->json)->name;
        //$d->name = "iPad 4";

		//return $d;
	}
}

?>