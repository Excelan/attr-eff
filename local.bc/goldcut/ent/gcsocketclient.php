<?php 
class GcSocketClient
{
	private $connection;
	private $authed = null;
	private $cmddm = "\0";
	private $format = '';

	function __construct($host, $port, $cmddm = null, $format = 'json')
	{
		if ($cmddm !== null) $this->cmddm = $cmddm;
		$this->format = $format;
		Log::debug("SOCKET OPEN {$host}:{$port}", 'net');
		$ip = gethostbyname($host);
		//Log::debug("DNS RESOLVED IP: {$ip}", 'net');
		$this->connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->connection === false) 
		{
			throw new Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
		}
		$timeout = array('sec' => 2,'usec' => 500);
		socket_set_option($this->connection, SOL_SOCKET, SO_RCVTIMEO, $timeout);
		$result = socket_connect($this->connection, $ip, $port);
		if ($result === false) 
		{
			Log::debug("SOCKET TIMEOUT", 'net');
			throw new Exception("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($this->connection)));
		}
	}
	
	function auth($credentials)
	{
		$out = (int) $this->sendAndRecieve($credentials);
        var_dump($out);
		if ($out == 200)
		{
			$this->authed = true;
		}
		else if ($out == 401)
		{
			$this->authed = false;
			throw new Exception("Auth incorrect");
		}
		else
		{
			throw new Exception("Auth request in incorrect format ($out)");
		}
	}
	
	function sendAndRecieve($in, $maxbytes=2048, $silent=false)
	{
		//if (is_string($in))
		if ($this->authed === false) throw new Exception('Auth first');
		Log::debug('TCP>> '.$in, 'net');
        $in .= $this->cmddm;
		$byteswritten = socket_write($this->connection, $in, strlen($in));
        if ($byteswritten === false) {
            if ($silent === false) println("SOWRITE " . socket_strerror(socket_last_error()), 1, TERM_RED);
        }
		$out = socket_read($this->connection, $maxbytes); //  возвращает данные в виде строки в случае успеха, или FALSE в случае ошибки (включая случай, когда удалённый хост закрыл соединение).
        if ($out === false) {
            if ($silent === false) println("SOREAD " . socket_strerror(socket_last_error()), 1, TERM_RED);
        }
		else
        {
            Log::debug('TCP<< '.$out, 'net');
			if ($this->cmddm)
			{
				$dataarray = explode($this->cmddm, $out);
				$data = $dataarray[0];
			}
			else
			{
				$data = $out;
			}
			if ($this->format == 'json')
			{
				$obj = json_decode($data, true);
				check_json_decode_result($data);
			}
			else
			{
				$obj = $data;
			}
            return $obj;
        }
        return false;
	}

    function close()
    {
        Log::debug('CLOSE SOCKET MANUAL', 'net');
        socket_close($this->connection);
    }
	
	function __destruct()
	{
        Log::debug('CLOSE SOCKET AUTO', 'net');
		socket_close($this->connection);
    }
}	
?>