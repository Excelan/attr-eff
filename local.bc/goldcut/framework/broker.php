<?php

/**

* Broker

Incoming - in. List of subscribers. list of messages. indexed UUID, Timestamps.
Exchange - processor in to out + invoke local slots. purge ttl expired. wHO INVOKE REMOTE RPC-XML?
Queue - out. ID of last delivered mess. list of not acked.
Message - immediate, durable, TTL
Acki. Reply-to?

Однозначность
Директ - по каждому ключу в инкам-эксчайндже может быть только одна исходящая очередь??? ВСЕ с таким ключом получат
Фанаут - связывание без ключа. инкам передает всем связанным аот очередям
Роутед - по маске ключа получают все совпадающие очереди

Время жизни сообщения
таймауты для ак, для хранения в очереди по получения из последней исходящей очереди

* @Producer
Broker::incoming_declare (durable, exchange_type=direct|fanout|routed)
Broker::send (message, to_incoming, with_routing_key, options=durable,10min)

* @Exchange
Exchange::getSubscribers(IncomingName) D - all, F - all, R - filter by key
deliver to incoming 1 deliver to consumers, 2 store durable if there more then connected & local consumers + save if not acked
deliver to consumers 1 local code, 2 connected sockets, 3 store durable
check immediate delivery or return

Deliver is adding messID to Queue

* @Consumer
Broker::queue_declare (durable, acked)
Broker::queue_bind (exchange, queue, routing_key, wait)
Broker->recieve ()

Broker::queue_unbind (exchange, queue)
*/

define("DIRECT", "DIRECT"); // ROUTE BY KEY TO ONE. NEED FIND OUTQ FROM ALL CONNECTED TO EXCHANGE INCOME. Point to Point. KEY IS DESTINATION.
define("FANOUT", "FANOUT"); // ROUTE TO ALL. GET ALL OUTQ CONNECTED TO EXCHANGE INCOME. One to Many. NO KEYS!
define("ROUTED", "ROUTED"); // ROUTE BY KEY* PATTERN TO SOME. NEED FIND OUTQ FROM ALL CONNECTED TO EXCHANGE INCOME. One to Selected Many. PubSub. KEY IS TOPIC. 
// Tree listeners - from topic.sub1.sub2.sub3 > topic.sub1.sub2.#, topic.sub1.*, topic.*
define("DURABLE", true);
define("NON_DURABLE", false);
define("NEED_ACK", true);
define("NO_ACK", false);

// TODO получать routed знает полный.путь если слушает полный.*

class Broker 
{

	private static $instance;
	private static $disabled;
	private function __construct() {}
	
	public static function instance() { if (!self::$instance) { self::$instance = new Broker(); } return self::$instance; }

    public static function disable()
    {
        self::$disabled = true;
    }

    public static function enable()
    {
        self::$disabled = false;
    }

	public static function list_incoming () 
	{
		Exchange::instance()->list_incoming();
	}

	public static function exchange_declare ($name, $durable = true, $exchange_type = DIRECT) 
	{
		$incoming = new Incoming($name, $exchange_type);
		Exchange::instance()->register_incoming($name, $incoming, $durable);
	}

	public static function queue_declare ($name, $durable = true, $need_ack = false) 
	{ // new queue
		$queue = new Queue($name, $need_ack);
		Exchange::instance()->register_outgoing($name, $queue, $durable);		
	}

	public static function bind ($exchange_name, $queue_name, $routing_key = false) 
	{ // add queue to exchange
		$incoming = Exchange::instance()->getIncoming($exchange_name);
		$outgoing = Exchange::instance()->getOutgoing($queue_name);
		$outgoing->key = $routing_key;
		$outgoing->exchange_name = $exchange_name;
		//print "bind $exchange_name to $queue_name\n";
		Exchange::instance()->bind($incoming, $outgoing, $routing_key);
	}

	public static function bind_rpc ($queue_name, $methodcall) 
	{ // add queue to exchange
		$outgoing = Exchange::instance()->getOutgoing($queue_name);
		$outgoing->rpc = $methodcall;
	}

	public static function unbind_rpc ($queue_name) 
	{ // add queue to exchange
		$outgoing = Exchange::instance()->getOutgoing($queue_name);
		$outgoing->rpc = false;
	}

	// m > incoming[], exchange->route
	public static function send ($message, $to_incoming, $with_routing_key = false, $options = false) 
	{ // add mess to incoming
        if (self::$disabled === true) return false;
		$incoming = Exchange::instance()->getIncoming($to_incoming);
		$qid = $incoming->add($message); //? what is qid?
		Exchange::instance()->route($incoming, $qid, $with_routing_key);
		Log::debug("$to_incoming $with_routing_key ".anyToString($message), 'mqsend');
		//return $error_code;
	}

	public static function recieve ($queue_name) 
	{ // no wait.  если durable очередь пуста - мы храним в кеше false positive. иначе надо залезать в бд. кеш может некоторое время держать копии в пямяти
		return Exchange::instance()->recieve($queue_name);
	}

	public static function recieve_queue_size ($queue_name) 
	{ 
		return count( Exchange::instance()->getOutgoing($queue_name)->messages );
	}


}

?>