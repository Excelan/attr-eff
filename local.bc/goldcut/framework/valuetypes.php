<?php 

/**
plus value Classes in tail
*/
class ValueTypes 
{
	/**
	unused
	*/
	public static function prim($typedData)
	{
		$type = key($typedData);
		switch ($type) 
		{
			case 'range':
				throw new Exception('Cant cast Range to primitive');
				break;
			case 'url':
				throw new Exception('Cant cast URL to primitive');
				break;
			case 'email':
				throw new Exception('Cant cast Email to primitive');
				break;	
			default:
				return $typedData->$type;
				break;
		}
	}

	public static function build($type, $value)
	{
		switch ($type) 
		{
			case 'range':
				$intype = key($value);
				if ($intype == 'int')
				{
					foreach ($value->$intype as $c)
						$vals[] = (int) $c;
					$from = $vals[0];
					$to = $vals[1];
				}
				if ($intype == 'float')
				{
					foreach ($value->$intype as $c)
						$vals[] = (float) $c;
					$from = $vals[0];
					$to = $vals[1];
				}
				return new Range($from, $to);
				break;
			case 'integer':
				return new String((integer)$value);
				break;
			case 'float':
				return new String((float)$value);
				break;	
			case 'string':
				return new String((string)$value);
				break;
			case 'cdata':
				return new CData((string)$value);
				break;	
			case 'char':
				return (string) $value;
				//return new Char((string)$value);
				break;	
			case 'option':
				if ((string)$value == 'yes')
					$b = true;
				else
					$b = false;
				return new Option($b);
				break;
			case 'status':
				if ((string)$value == 'yes')
					$b = true;
				else
					$b = false;
				return new Option($b);
				break;	
			case 'url':
				return new URL((string)$value);
				break;
			case 'email':
				return new Email((string)$value);
				break;
			case 'chosen':
				$chosen = (string) $value->names->char;
				return $chosen;
				break;	
			case 'selected':
				$selected = array();
				foreach ($value->names->char as $name)
					$selected[] = (string) $name;
				return $selected;
				break;
			case 'array':
				$items = array();
				foreach ($value->item as $name) 
				{
					$items[] = (string) $name->char;
				}
				return $items;
				break;
            case 'dict':
                $items = array();
                foreach ($value->item as $item)
                {
                    $k = (string) $item->key;
                    $v = (string) $item->value;
                    $items[$k] = $v;
                }
                return $items;
                break;
			default:
                //println("$type, $value",1,TERM_RED);
				//var_dump($type, $value);
				//return (string) $value;
                // TODO on developemtn debug if <type> not found
                return null;
				break;
		}
	}
}

class Range
{
	public $range;
	public $to;
	function __construct($from, $to)
	{
		$this->range = $from;
		$this->to = $to;
	}
}
class String
{
	public $string;
//	public $language;
	function __construct($string) //, $language='en'
	{
		$this->string = $string;
//		$this->language = $language;
	}
    function __toString()
    {
        return (string) $this->string;
    }
}
class CData
{
	public $cdata;
	function __construct($str)
	{
		$this->cdata = $str;
	}
}
class Char
{
	public $char;
	function __construct($string)
	{
		$this->char = $string;
	}
}
class Email
{
	public $email;
	function __construct($email)
	{
		$this->email = $email;
	}
}
class Integer
{
	public $integer;
	function __construct($int)
	{
		$this->integer = $int;
	}
}
class Integer64
{
	public $integer64;
	function __construct($int)
	{
		$this->integer64 = $int;
	}
}
class Float
{
	public $float;
	function __construct($float)
	{
		$this->float = $float;
	}
}
class Float64
{
	public $float64;
	function __construct($float)
	{
		$this->float64 = $float;
	}
}
class Option
{
	public $option;
	function __construct($is)
	{
		$this->option = $is;
	}
}

?>