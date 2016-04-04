<?php

class Message
{

	private $message;

	function keys()
	{
		return array_keys($this->message);
	}

	function keysNonSystem()
	{
		return array_diff(array_keys($this->message),array('id','urn','action'));
	}

	function values()
	{
		return array_values($this->message);
	}

	/**
	required params check (as ? in values or split from message)
	*/
	static function named($queryname)
	{
		$m = $GLOBALS['CONFIG']['QUERIES'][$queryname];
		return $m;
	}

	public function loadExample($name = null)
	{
		$gate = $this->message['gate'];
		if (!$this->message['gate']) throw new Exception('No gate in message');
		$gateA = explode('/',$this->message['gate']);
		$gateName = array_pop($gateA);
		if (!$name) $name = $gateName;
		$f = GATES_DIR.'/'.join('/',$gateA).'/'.$name.'.json';
		if (!file_exists($f)) throw new Exception($f.' gate example not found');
		$this->message = json_decode(join('',file($f)), true);
		if (!$this->message['gate']) $this->message['gate'] = $gate;
		if ($gate != $this->message['gate']) throw new Exception("Gate in message not equal to gate in example json `{$gate}` != `{$this->message['gate']}`");
	}

	public function sendExample()
	{
		$gate = $this->message['gate'];
		if (!$this->message['gate']) throw new Exception('No gate in message');
		$f = GATES_DIR.'/'.$this->message['gate'].'.json';
		$this->message = json_decode(join('',file($f)), true);
		if (!$this->message['gate']) $this->message['gate'] = $gate;
		if ($gate != $this->message['gate']) throw new Exception('Gate in message not equal to gate in example json');
		return $this->send();
	}

	public function send()
	{
		// TODO gate rounting table
		//$r = GateExternalRequest::dispatch('Registration/Step2', array('var'=>'tets') );
		//Log::info(json_encode($this->message['gate']), 'gaterequest');

		//$result = GateRequest::dispatch($this->message['gate'], $this->message);
		if (!$this->message['gate']) throw new Exception('No gate in message');
		$result = Gate::route($this->message['gate'], $this);
		return $result;
	}

	public function sendAsTransaction()
	{
		if (!$this->message['gate']) throw new Exception('No gate in message');
		$dblink = DB::link();
		try
		{
			$dblink->begin();
			$result = Gate::route($this->message['gate'], $this);
			$dblink->commit();
		}
		catch (Exception $e)
		{
			$dblink->rollback();
			throw $e;
		}
		return $result;
	}

	public function registerGate($createPHP=true, $createXML=true, $createJson=false)
	{
		
		if (!$this->message['gate']) throw new Exception('No gate in message');
		$gateA = explode('/',$this->message['gate']);
		$gateName = array_pop($gateA);
		$gatePath = GATES_DIR.'/'.join('/',$gateA);
		mkdir($gatePath, octdec(FS_MODE_DIR), true);
		$f = $gatePath.'/'.$gateName.'.php';
		if (!file_exists($f) && $createPHP)
		{
			touch($f);
			chmod($f,octdec(FS_MODE_FILE));
			$xml = read_data_from_file(GOLDCUT_DIR.'/gates/Example/GateExample.php');
			$xml = str_replace('GateExample', $gateName, $xml);
			$xml = str_replace('Example', str_replace('/','\\',join('/',$gateA)), $xml);
			save_data_as_file($f, $xml);
		}
		$f = $gatePath.'/'.$gateName.'.xml';
		if (!file_exists($f) && $createXML)
		{
			touch($f);
			chmod($f,octdec(FS_MODE_FILE));
			$xml = read_data_from_file(GOLDCUT_DIR.'/gates/specificationExample.xml');
			$xml = str_replace('Ns/Deep', join('/',$gateA), $xml);
			$xml = str_replace('GateClass', $gateName, $xml);
			save_data_as_file($f, $xml);
		}
		$f = $gatePath.'/'.$gateName.'.json';
		if (!file_exists($f) && $createJson)
		{
			//TODO Create json based on XML specification
			touch($f);
			chmod($f,octdec(FS_MODE_FILE));
		}
		else
		{
			if (file_exists($f) && filesize($f) < 3) unlink($f); // remove blank jsons
		}

		return true;
	}

	public function deliver() // $tx
	{
		//if (DEBUG_MESSAGE === TRUE && OPTIONS::get('pause_DEBUG_SQL') !== true) println($this->message, 1, TERM_VIOLET);
		try
		{
			// GET EMANAGER CLASS
			if ($this->urn->entitymeta)
			{
				$Class = $this->urn->entitymeta->getClass();
			}
			else
			{
				throw new Exception("Cant deliver message " . json_encode($this->message));
			}
			foreach ($this->message as $k => $v)
			{
				if ($v === '?') throw new Exception("Key $k ($v) is required in {$this}");
			}
			$response = $Class->recieve($this);

			// TODO if responce error, Transaction ROLLBACK
			// TODO use warnings for errors with possibility to continue flow
			return $response;
		}
		catch (Exception $e)
		{
			// if (ENV === 'DEVELOPMENT') println($e->getMessage(), 1, TERM_RED);
			throw $e;
		}
	}

	public function __get($field)
	{
		if (!$field) return null;

		if (isset($this->message[$field]))
		{
			// sub message
			if (is_array($this->message[$field]))
				return new Message($this->message[$field]);
			else
			{
				// STRING
				if (is_string($this->message[$field]))
				{
					// URN
					if (substr($this->message[$field],0,3) == 'urn')
					{
						$urn = new URN($this->message[$field]);
						return $urn;
						/*
						if ($field == "urn")
							return $urn;
						else
							return $urn->resolve();
						 */
					}
					// TODO Delegate transform to Field
					else
						return $this->message[$field];
				}
				// OBJECT
				else
					return $this->message[$field];
			}
		}
		else
			return null;
	}

	public function __set($field, $value)
	{
		if ($value instanceof URN) $value = (string) $value;
		$this->message[$field] = $value;
	}

	public function set($field, $value)
	{
		//println($field, 1, TERM_GREEN);
		$link =& $this->message;
		$prev = null;
		$curpath = array();
		foreach (explode('.',$field) as $f)
		{
			array_push($curpath, $f);
			$prev =& $link;
			//println($f,2);
			if (is_numeric($f))
			{
				$f = (int)$f - 1;
				if (!$link[$f]) throw new Exception(join('.',$curpath)." index out of bounds");
				//println($link[0],3,TERM_GRAY);
			}
			$link =& $link[$f];
			//printlnd($link,3,TERM_BLUE);
		}
		//println($prev,3,TERM_YELLOW);
//		println($this->message, 2, TERM_GREEN);
//		println($this->message[$field], 3, TERM_GREEN);
		if ($value instanceof URN) $value = (string) $value;
		$prev[$f] = $value;
		//println($prev);
		//println("$f");
//		println($this->message[$field], 3, TERM_YELLOW);
	}

	public function getby($field, $value)
	{
		//println($field, 1, TERM_GREEN);
		$link =& $this->message;
		$prev = null;
		$curpath = array();
		foreach (explode('.',$field) as $f)
		{
			array_push($curpath, $f);
			$prev =& $link;
			//println($f,2);
			if (is_numeric($f))
			{
				$f = (int)$f - 1;
				if (!$link[$f]) throw new Exception(join('.',$curpath)." index out of bounds");
				//println($link[0],3,TERM_GRAY);
			}
			$link =& $link[$f];
			//printlnd($link,3,TERM_BLUE);
		}
		//println($prev,3,TERM_YELLOW);
//		println($this->message, 2, TERM_GREEN);
//		println($this->message[$field], 3, TERM_GREEN);
		return $prev[$f];
		//println($prev);
		//println("$f");
//		println($this->message[$field], 3, TERM_YELLOW);
	}

	function merge($m, $key=null)
	{
		if ($key)
			$m = array($key => $m);
		if (is_array($m))
			$this->message = array_merge($this->message, $m); // REPLACING BEHAVIOR
		else
			throw new Exception("Message merge with non array");
	}

	static function check($m)
	{
		if (is_string($m)) throw new Exception("expexted message is string");
		if (is_array($m)) throw new Exception("expexted message is array");
		if (get_class($m) != 'Message') throw new Exception("expexted message is object and not of Message class");
		if (!$m->urn) throw new Exception("msaage without URN".$m);
	}

	function clear($field)
	{
		unset($this->message[$field]);
	}

	function __construct($m=null)
	{
		if (!$m)
			$this->message = array();
		else
			$this->message = $m;
		if ( is_array($this->message) )
			$this->checkArray();
		elseif ( is_json($this->message) )
			$this->parseJson();
		elseif ( is_xml($this->message) )
			$this->parseXML();
		else
			throw new Exception("Message::construct() Unknown message format -> {$m}");
		$this->checkArray();
	}

	function checkArray()
	{
		foreach ($this->message as $k=>$v)
		{
			if ($v === null) unset($this->message[$k]);
		}
	}

	private function parseJson()
	{
		$tm = $this->message;
		$this->message = json_decode($this->message, true);
		if (!$this->message) throw new Exception("MESSAGE IS JSON WITH ERRORS [{$tm}]");
	}

	function get()
	{
		return $this->message;
	}

	function __toString()
	{
		$message_json = json_encode($this->message);
		return $message_json;
	}

	function toArray()
	{
		return $this->message;
	}

	public function __isset($name)
	{
		return isset($this->message[$name]);
	}

	public function exists($field)
	{
		if (isset($this->message[$field]))
			return true;
		else
			return false;
	}

	public function __unset($name)
	{
		unset($this->message[$name]);
	}
}

?>