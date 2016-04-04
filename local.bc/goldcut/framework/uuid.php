<?php

class UUID
{

	protected $uuid; // INT32/64

	function __construct($uuid = null)
	{
		if (!$uuid)
			$this->generate();
		else
		{
			if (is_int($uuid))
				$this->uuid = $uuid;
			elseif (is_string($uuid))
				$this->uuid = $this->fromString($uuid);
		}
	}

	private function fromString($uuid)
	{
		//return hexdec($uuid);
		return (integer) $uuid;
	}

	protected function generate()
	{
		$id = mt_rand(1000, (FORCE32BIT) ? 2147483647 : 9223372036854775807); //PHP_INT_MAX
        if (PHP_INT_MAX > 2147483647 && (defined('FORCEBIGINTS') && FORCEBIGINTS === true && FORCE32BIT !== true) ) $id = mt_rand(2147483647, PHP_INT_MAX);
		$this->uuid = $id;
	}

	public function toInt()
	{
		return $this->uuid;
	}
	
	public function toHex()
	{
		return strtoupper( dechex($this->uuid) );
	}

	public function __toString()
	{
		//return $this->toHex();
		return (string) $this->uuid;
	}

}

?>