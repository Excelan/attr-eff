<?php

namespace SystemBus\SystemEvent;

class TestEmitByExample {

	function gate($d)
	{
		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:systemevent';
		$m->name = $d->name;
		$se = $m->deliver();
//		printlnd($se->exampleeventdata,1,TERM_RED);
//		printlnd($se->exampleeventdata->details,2,TERM_RED);
//		printlnd($se->exampleeventdata->details->visitor,2,TERM_RED);
		$m = new \Message();
		$m->name = $se->name;
		$m->context = $d->context;
		$m->resource = $d->resource;
		$m->details = (array)$se->exampleeventdata->details;
		$m->origin = $se->exampleeventdata->origin;
		$m->gate = 'SystemBus/SystemEvent/Emit';
		$r = $m->send();
		return array('urn' => (string) $r->urn);
	}

}
?>