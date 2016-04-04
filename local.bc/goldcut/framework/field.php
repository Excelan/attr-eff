<?php

class Field
{
	private $list;
	private $listt;
	private static $instance;

	private function __construct() {}

	private function init()
	{
		$this->list = $GLOBALS['CONFIG']['FIELD'];
		foreach ($this->list as $uid => $f) {
			$this->listt[$f->name] = $f;
		}
	}

	public function reinit()
	{
		$this->init();
	}

	public static function manager()
	{
		if (!self::$instance) { self::$instance = new Field(); self::$instance->init(); } return self::$instance;
	}

	public static function exists($uid) 
	{
		$f = Field::ref($uid, true);
		if ($f) return true;
		else return false;
	}
	
	public static function ref($uid, $nothrow = false)
	{
		if (is_numeric($uid))
		{
			$f = Field::manager()->list[$uid];
		}
		else if (is_array($uid))
		{
			$usedAs = key($uid);
			$baseName = $uid[$usedAs];
			$f = Field::manager()->listt[$baseName];
		}
		else
		{
			$f = Field::manager()->listt[$uid];
		}
		if ($f)
		{
			return $f;
		}
		else 
		{
			if ($nothrow)
				return false;
			else
			{
				throw new Exception("Field $uid not exists");
			}
		}
	}

	public static function id($uid, $nothrow = false)
	{
		return Field::manager()->ref($uid, $nothrow);
	}

	static function each()
	{
		return Field::manager()->list;
	}
	
	public static function sqlwrap($F, $value)
	{
		//println("{$F->name} {$F->type} ");
		if ($value === '' or $value === 'NULL' or is_null($value))
		{
			$wrapped = "NULL";
		}
		else
		{
			if ($F->type == 'integer' or $F->type == 'timestamp')
			{
				$wrapped = (integer) $value;
			}
			elseif ($F->type == 'float' || $F->type == 'money')
			{
				if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
				{
					$value = str_replace(',', '.', $value);
					$precision = $F->precision ? $F->precision : 2;
					$wrapped = number_format(floatval($value), $precision, '.', '');
					//$wrapped = $value;
				}
				else
				{
					$value = str_replace(',', '.', $value);
					$precision = $F->precision ? $F->precision : 2;
					$wrapped = number_format(floatval($value), $precision, '.', '');
				}
			}
			elseif ($F->type == 'json' && USEPOSTGRESQL === true)
			{
				$wrapped = "'".html_entity_decode($value)."'::json";
			}
			elseif ($F->type == 'xml' && USEPOSTGRESQL === true)
			{
				$wrapped = "'".$value."'::xml";
			}
			elseif ($F->type == 'iarray' && USEPOSTGRESQL === true)
			{
				if ($value instanceof Message)
					$wrapped = "ARRAY".json_encode($value->toArray());
				else
					throw new Exception("Non array value {$F->name}={$value}");
			}
			elseif ($F->type == 'sequence' && USEPOSTGRESQL === true)
			{
				//printlnd($value,1,TERM_RED);
				//printlnd($value->__toString(),1,TERM_RED);
				$wrapped = "'".(string) $value . "'";
				//printlnd($wrapped,2,TERM_RED);
			}
			elseif ($F->type == 'tarray' && USEPOSTGRESQL === true)
			{
				if ($value instanceof Message) {
					$va = $value->toArray();
					$vf = array_map("safeq", $va);
					$v = "['".join("', '", $vf)."']";
					$wrapped = "ARRAY" . $v;
				}
				else
					throw new Exception("Non array value {$F->name}={$value}");
			}
			else
			{
				//$wrapped = "'".Security::mysql_escape((string)$value)."'";
				$safe = $value;				
				$wrapped = "'".$safe."'";
			}
		}
		return $wrapped;
	}
	
	public static function formatValue($F, $value)
	{
		if ($F->type == 'integer')
			return (integer) $value;
		if ($F->type == 'float' || $F->type == 'money')
		{
			$value = str_replace(',', '.', $value);
			$precision = $F->precision ? $F->precision : 2;
			$wrapped = number_format(floatval($value), $precision, '.', '');
			return (float) $wrapped;
		}
		// TODO
		if ($F->type == 'string')
			return (string) $value;			
		if ($F->type == 'text')
			return (string) $value;			
		if ($F->type == 'set')
			return (string) $value;			
		if ($F->type == 'richtext')
			return (string) $value;			
		if ($F->type == 'option')
		{
			if ($value === 1 or $value === "1" or $value === true or $value === 'Y')
				return 'Да';
			else
				return 'Нет';
		}
	}

}

?>