<?php

namespace SystemBus\SystemEvent;

class Emit {

	function __constructor($env)
	{

	}

	function gate($event)
	{
		$ev = $event->get();

		\Log::debug(json_encode($ev), 'emit');

		if (!$ev['name'])
			throw new \Exception("No event name in emit ".anyToString($ev));

		$m = new \Message();
		$m->action = 'load';
		$m->urn = 'urn:systemevent';
		$m->name = $ev['name'];
		$masterEvent = $m->deliver()->current();
		if (!count($masterEvent))
			throw new \Exception("No master event for name".$ev['name']);
		//println($masterEvent,1,TERM_GREEN);

		// origin gate or some named code block
		$origin = $ev['origin'];
		// event type controlled on master event level
		$ev['eventtype'] = $masterEvent->type;
		$ev['date'] = $timestamp = \TimeOp::now();

		// FORWARD OPTIONS
		// $masterEvent->showInFeed

		// check context, resource(opt) for equal to urn defined master event level
		if (!$masterEvent->context) throw new \Exception("No context in master systemevent");
		$contextMaster = new \URN($masterEvent->context);
		$context = new \URN($ev['context'], $contextMaster->entity->name);
		if ($masterEvent->resource) {
			$resourceMaster = new \URN($masterEvent->resource);
			$resource = new \URN($ev['resource'], $resourceMaster->entity->name);
		}

		// create feed record
		$m = new \Message();
		$m->action = 'create';
		$m->urn = 'urn:systemeventrecord';
		$m->systemevent = $masterEvent->urn;
		$m->origin = $origin;
		$m->context = (string) $context;
		$m->resource = $resource;
		$m->details = $ev['details'];
		$record = $m->deliver();

		// DELEGATE TO GATES
		// gatesList = explode(',', $masterEvent->delegate);

		// NOTIFICATIONS SERVICE. Send push to mobile apps, transaction emails
		// /notifications/inbound/route
		/*
		 * notifyShortT
		 * notifyLongT
		 */
		// if ($masterEvent->notify?)
		// /notifications/route/named
		// /notifications/transport/push
		// /notifications/transport/email
		// context -> extract USER(id, pushDevices, extNotify(fb, xmpp), email, user notify settings )
		// {load push, mail T + $ev BUILDER}

		if ($masterEvent->notify)
		{
			$m = new \Message();
			$m->masterevent = $masterEvent->urn;
			$m->systemeventrecord = $record->urn;
			$m->gate = 'Notifications/Router/DeliverNamed';
			$m->send();
		}

		/*
		// SEND MAIL
		$mailt = $masterEvent->mailplain;
		//println($mailt->current(),1,TERM_VIOLET);
		if (count($mailt))
		{
			//$context = array('user' => $createduser);
			// TODO
			// context user - .user, (urn-project-1).allUser, , (urn-project-1).techUser
			$contextUser = \URN::object_by('urn-user-300')->current(); // !
			//println($contextUser);
			$mailLayout = 'mailmaintemplate'; // !
			//$strings = new stdClass();
			//$strings->positive = 'POSITIVE';
			$merged = array();
//        $merged['eventDetailsVisitor'] = $ev['details']['visitor'];
			$merged['eventDetailsVisitor'] = $ev['details']['visitor'];
//        $merged['eventDetailsVisitor'] = null;
			$mailTextContext = array('user'=>$contextUser, 'backupunit' => $resource, 'string' => $strings, 'merged' => $merged); // !
			\Mail::sendUserTemplatesContext($contextUser, $mailLayout, $mailt->uri, $mailTextContext);
		}
		*/
		// notify MQ systemfeed.eventName
		\Broker::instance()->send($ev, "EVENTS", "systemfeed.{$ev['name']}");

		// use $data
		//throw new \Exception('Some gate err');
		return array('urn' => (string) $record->urn);
	}

}
?>