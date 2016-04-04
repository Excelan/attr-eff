<?php
/**
  храним только номера сообщений
  ack? - move mid from messages[] to ack_wait_messages[]
  rpc - точка моментальной доставки в класс или функцию
  
  когда сообщение удаляется из инкаминг? - отдавая сообщение мы его не удаляем из инкаминг (process()?)
  Очередь создается слушателем и принадлежит только ему, при этом может быть несколько одинаковых слушателей - это реализует round robin потребление
 */
class Queue // OUTGOING. m ids only
{
	public $name;	
	public $rpc; //?used??
	public $need_ack;
	public $messages = array();
	public $acks = array();

	function __construct($name, $need_ack=false)  // $durable????
	{
		//println("$name, $need_ack NEW Q+");
		$this->name = $name;
		$this->need_ack = $need_ack;
	}

	function size () 
	{ 
		return count($this->messages) - count($this->acks);
	}

	function add ($msg) 
	{ 
		$this->messages[] = $msg;
	}

	// отдаем сообщение из Incoming очереди.
	// TODO need ack!
	function popmessage () 
	{
		if ($this->need_ack)
		{
			//println("POPMESSAGE NEED ACK");
			$last = $this->messages[$i];
			$last = array_pop ( $this->messages );
			//println($this->messages);			//println($this->acks);			$i = count($this->messages)-1; //var_dump($last);
			$this->acks[] = $last;
		}
		else
		{ 
			//println("POPMESSAGE WITHOUT NEED OF ACK");
			$last = array_pop ( $this->messages );
		}
		$incoming = Exchange::instance()->getIncoming($this->exchange_name);
		return $incoming->ref($last);
	}	
}


?>