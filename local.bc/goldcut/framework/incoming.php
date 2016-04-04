<?php
/**
  только здесь храним само сообщение. очереди имеют только номера ссылки на них
  !отдавая сообщение мы его не удаляем
 */
class Incoming
{
	public $exchange_type;
	public $name;
	public $messages = array();

	function __construct($name, $exchange_type) 
	{ // $name, $durable
		$this->name = $name;
		$this->exchange_type = $exchange_type;		
	}

	// добавляет сообщение и возвращает количество сообщений в инкаминг очереди
	function add ($msg) 
	{
		$n = count($this->messages);
		$this->messages[] = $msg;
		return $n;
	}

	// возвращает сообщение по его номеру
	function ref ($idmsg) 
	{
		return $this->messages[$idmsg];
	}

}


?>