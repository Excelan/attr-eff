<?php

class GateException extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null)
	{
		\Log::error($code.' '.$message, 'gates');
		parent::__construct($message, $code, $previous);
	}

	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}

interface GateExceptionProcess
{
	public function process();
}

?>