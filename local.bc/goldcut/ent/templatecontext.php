<?php
/**
add(name, Cat c)
switchto() named query result - it can be a category, dataset, datarow
deep - list in list
up - return to parent list
TODO Share page.title context between all context. GLOBAL CONTEXT!
*/
class TemplateContext 
{
  private $names = array();
  private $voffest = 0;
	private $current;
	
	/**

	*/
  function __construct()
  {
  }

	/**

	*/
  function add($name, $data)
  {
    $this->names[$name] = $data;
		$this->current = $name;
  }

	/**
	if not found - and if not in sandbox - $this->context->add('performance', Entity::query( array('e'=>'performance') );
	*/
  function get($name)
  {
    if ( isset($this->names[$name]) )
			return $this->names[$name];
		else
		 return '';
			//throw new Exception ("TemplateContext named [$name] not found");
  }

  function defined($name)
  {
    if ( isset($this->names[$name]) )
			return true;
		else 
			return false;
  }

	/**
	текущий элемент - последний добавленный
	*/
	function current()
	{
		return $this->get( $this->current );
	}

	
}
?>