<?php
namespace Datamodel;

// https://github.com/lstrojny/functional-php
// TODO OPTIONALS!

interface Value {
	function get();
	function __toString();
}
interface ValueUnits {
	function getUnits();
}


interface MathOp {
	function add($value);
	//function mult($value);
}

// ???
trait DecimalMathOverFloat
{
	function addExtractedScalarReturnFloat($value)
	{
		return $this->_value + $value->get();
	}
}



class Integer implements Value, MathOp
{
	//use DecimalMathOverFloat;

	protected $_value;

	public function __construct($value)
	{
		$this->_value = (int) $value;
	}

	function get()
	{
		return $this->_value;
	}

	function __toString()
	{
		return number_format($this->_value, 0, '', '');
	}

	function add($value)
	{
		$newValue = $this->_value + $value->get();
		return new Integer($newValue);
	}

}

class Decimal2 implements Value, MathOp
{
	//use DecimalMathOverFloat;

	protected $_value;

	public function __construct($value)
	{
		$this->_value = round($value, 2, PHP_ROUND_HALF_UP);
	}

	function get()
	{
		return $this->_value;
	}

	function __toString()
	{
		return number_format($this->_value, 2, '.', '');
	}

	function add($value)
	{
		$newValue = $this->_value + $value->get();
		return new Decimal2($newValue);
	}

}

class Money implements Value, ValueUnits, MathOp
{
	//use MathOpOverFloat;

	protected $_value;
	protected $_units;

	public function __construct($value, $units)
	{
		$this->_value = round($value, 2, PHP_ROUND_HALF_UP);
		$this->_units = $units;
	}

	function getUnits()
	{
		return $this->_units;
	}

	function get()
	{
		return $this->_value;
	}

	function __toString()
	{
		return number_format($this->_value, 2, '.', '').' '.$this->_units;
	}

	function add($value)
	{
		$newValue = $this->_value + $value->get();
		return new Money($newValue, $this->getUnits());
	}
}

// config
class Struct implements \ArrayAccess
{
	private $_metaname;
	protected $_signature; // config of key: value[T]
	protected $_data;
	protected $readonly = false;

	//

	public function __construct($metaname, array $data)
	{
		$this->_signature = $GLOBALS['STRUCTMETA'][$metaname];
		$this->_metaname = $metaname;
		if (!$this->_signature) throw new Exception("No struct meta for $metaname");
		$i=0;
		foreach ($this->_signature as $key => $valueType)
		{
			$value = null;
			switch($valueType)
			{
				case 'String':
					$value = 'S'.$data[$i];
					break;
				case 'Decimal2':
					$value = new Decimal2($data[$i]);
					break;
				case 'Integer':
					$value = new Integer($data[$i]);
					break;
				default:
					$value = $data[$i];
			}
			$this->_data[$key] = $value;
			$i++;
		}
	}

	function get()
	{
		return $this->_data;
	}

	function __toString()
	{
		$s = '';
		foreach ($this->_data as $key => $value)
		{
			$s .= $key."\t->\t".$value."\n";
		}
		return $s;
	}

	public function offsetSet($offset, $value) {
		if ($this->readonly === true) throw new Exception("Struct is readonly");
		if (is_null($offset)) {
			$this->_data[] = $value;
		} else {
			$this->_data[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->_data[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->_data[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
	}

}

/*
 * get column from table
 * array of values in interval
 */
class Sequence implements \Iterator, \Countable {

	protected $seq;
	protected $labels=null;

	public function __construct(array $data, array $labels=null)
	{
		$this->seq = $data;
		if ($labels) $this->labels = $labels;
	}

	public function getIterator()
	{
		return $this->seq;
	}

	public function getData()
	{
		return $this->seq;
	}

	public function getLabels()
	{
		return $this->labels;
	}

	public function setLabels($labels)
	{
		$this->labels = $labels;
	}

	function __toString()
	{
		$s = '{';
		$s .= join(',', $this->seq);
		$s .= '}';
		return $s;
	}

	function toJSON()
	{
		if (!$this->labels)
		{
			$s = '[';
			$s .= join(',', $this->seq);
			$s .= ']';
		}
		else
		{
			$zipped = array_combine($this->labels, $this->seq);
			$s = json_encode($zipped);
		}
		return $s;
	}

	public function isempty()
	{
		if ( count($this->seq) == 0) return true;
		else return false;
	}

	public function count()
	{
		return ( count($this->seq) );
	}


	public function last()
	{
		$size = $this->count();
		$var = $this->seq[$size];
		return $var;
	}

	public function first()
	{
		$this->rewind();
		return $this->current();
	}

	public function current()
	{
		$var = current($this->seq);
		return $var;
	}

	public function rewind()
	{
		reset($this->seq);
	}

	public function key()
	{
		$var = key($this->seq);
		return $var;//$this->labels[$var];
	}

	public function next()
	{
		$var = next($this->seq);
		return $var;
	}

	public function valid()
	{
		$var = $this->current() !== false;
		return $var;
	}

}

?>