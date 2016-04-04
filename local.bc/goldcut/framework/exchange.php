<?php
// Exchange - это ин-аут очереди и их биндинги, плюс ключи роутинга
// TODO define MQCACHED

class exchange
{
    private static $instance;
    private function __construct()
    {
    }
    public $incoming = array();
    public $outgoing = array();
    public $bind = array();

    public static function instance()
    {
        if (!self::$instance) {
            if (MQCACHED === true && $selfs = Cache::get('MQEXCHANGE')) {
                //printH('Cached Exchange');
                //self::$instance = unserialize($selfs);
                self::$instance = $selfs;
            } else {
                self::$instance = new Exchange();
            }
        }
        return self::$instance;
    }

    public function register_incoming($name, Incoming $incoming, $durable)
    {
        $this->incoming[$name] = $incoming;
    }

    public function register_outgoing($name, Queue $queue, $durable)
    {
        // TODO durable!
        $this->outgoing[$name] = $queue;
    }

    public function bind($exchange, $queue, $routing_key = false)
    {
        $this->bind[$exchange->name][] = $queue;
        //if ($routing_key !== false) $this->bindkeys[$exchange->name][$routing_key] = $queue; // !!!!!!!!!!!!!!!!!!
    }

    public function recieve($queue_name)
    {
        $outgoing = Exchange::instance()->getOutgoing($queue_name);
        if (!$outgoing->size()) {
            return null;
        }
        $msg = $outgoing->popmessage();
        /*
        if ($msg) {
            //print "out $outgoing->name size: ".$outgoing->size()." on GET \n";
        }
        else {
            //print "out $outgoing->name EMPTY on GET \n";
        }
        */
        return $msg;
    }

    // DIRECT FANOUT ROUTED. deliver message ID! from incomingq  not message
    public function route(Incoming $exchange, $message, $key)
    {
        if ($exchange->exchange_type == DIRECT) {
            foreach ($this->bind[$exchange->name] as $i=>$out) {
                if ($out->key == $key) {
                    $out->add($message);
                    if ($out->rpc) {
                        Log::info($out->rpc, 'mqrpc');
                        call_user_func($out->rpc, $out->popmessage($msg), $key); // аналогично считыванию самостоятельно позже
                    }
                    // TODO if out->remote
                    // send to remote sokect
                }
                //print "DIRECT route [ $message ] $exchange->name to out K$key @ $out->name $out->key \n";
            }
        }

        if ($exchange->exchange_type == ROUTED) {
            if (!$this->bind[$exchange->name]) {
                $this->bind[$exchange->name] = array();
            }
            foreach ($this->bind[$exchange->name] as $i=>$out) {

                //print "ROUTED route [ $message ] $exchange->name to out K$key @ $out->name / $out->key \n";

                if ($this->compare($out->key, $key)) {
                    $out->add($message);
                    if ($out->rpc) {
                        try {
                            Log::info($out->rpc, 'mqrpc');
                            call_user_func($out->rpc, $out->popmessage($msg), $key);
                        } catch (Exception $e) {
                            if (TEST_ENV === true) {
                                throw new Exception($e);
                            }
                            if (ENV === 'DEVELOPMENT') {
                                println($e, 1, TERM_RED);
                            } else {
                                Log::debug("Exception in mq rpc call {$out->rpc} [$e]", 'mq');
                            }
                        }
                    }
                    $ramaining = $out->size();
                    //print "out $out->name size: $ramaining in route \n"; // !!!!!!!!!
                }
            }
        }

        if ($exchange->exchange_type == FANOUT) {
            $outs = $this->bind[$exchange->name];

            //print "FANOUT route $exchange->name to outs: ";

            foreach ($outs as $i=>$out) {
                $out->add($message);
                if ($out->rpc) {
                    Log::info($out->rpc, 'mqrpc');
                    call_user_func($out->rpc, $out->popmessage($msg), $key);
                }
            }
        }
    }

    /**
    очистка прочитанных объектов из очередей, чтобы сохранить объект без уже ненужных сообщений
    */
    public function process()
    {
        print "Exchange::process()\n";
        //print memory_get_usage()."\n";
        //print memory_get_usage(true)."\n";

        $saveq = array();
        $savee = array();

        foreach ($this->outgoing as $qname => $queue) { // собираем оставшиеся номера сообщений в очередях
            //print "$qname ".$queue->size()."\n";
            if ($queue->size() > 0) {
                // print "SAVE Q\n";
                $saveq[$queue->exchange_name][$queue->name] = $queue->messages;
            }
        }

        foreach ($this->incoming as $qname => $queue) {
            print "I $qname \n";
            print_r($queue->messages);
            $x = array();
            foreach ($saveq[$queue->name] as $k => $v) {
                print "K $k \n";
                print_r($v);
                $x = array_merge($x, array_values($v));
            }
            $ids = array_values(array_unique($x));
            print "\nALL IN $qname \n ";
            print_r($ids);
            $okids = array_values(array_intersect(array_keys($queue->messages), $ids));
            $delids = array_values(array_diff(array_keys($queue->messages), $ids));
            print "\nOK IN $qname \n ";
            print_r($okids);
            for ($i=0;$i<count($okids);$i++) { // OK
                print $okids[$i] . " \t";
            }
            print "\nDEL IN $qname \n ";
            print_r($delids);
            for ($i=0;$i<count($delids);$i++) { // CLEAN
                print $delids[$i] . " \t";
                unset($queue->messages[$delids[$i]]);
            }
            // // print "\n	AFTER DELETD IN $qname \n ";
            // // print_r($queue->messages);
        }
        print_r($saveq);
        print_r($savee);
        unset($x);
        unset($saveq);
        unset($savee);
        unset($delids);
        unset($okids);
        unset($ids);

        //print memory_get_usage()."\n";
        //print memory_get_usage(true)."\n";

        $selfs =  serialize(self::$instance);
        if (MQCACHED === true) {
            Cache::put('MQEXCHANGE', $selfs);
        }
    }



    public function compare($a1, $a2)
    {
        $a1 = explode(".", $a1);
        $a2 = explode(".", $a2);
        for ($i=0;$i<count($a1);$i++) {
            if ($a1[$i] == '*') {
                return true;
            }
            if ($a1[$i] != $a2[$i]) {
                return false;
            }
        }
        return true;
    }

    public function list_incoming()
    {
        foreach ($this->incoming as $qname => $queue) {
            println("queue: $qname");
            println("count: ". count($queue->messages));
            //print_r($queue->messages);
        }
    }

    public function getIncoming($name)
    {
        if (isset($this->incoming[$name])) {
            return $this->incoming[$name];
        }
        throw new Exception("INCOMING QUEUE $name NOT EXISTS");
    }

    public function getOutgoing($name)
    {
        if (isset($this->outgoing[$name])) {
            return $this->outgoing[$name];
        }
        throw new Exception("OUTGOING QUEUE $name NOT EXISTS");
    }
}
