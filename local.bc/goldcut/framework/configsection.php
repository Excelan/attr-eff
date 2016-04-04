<?php
class ConfigSection extends stdClass
{
	function __construct($c)
	{
		foreach ($c as $k=>$v)
		{
			if ($k == 'section')
			{
				$sectionName = (string) $c->name;
				$sk = (string) $v->name;
				$this->$sk = new ConfigSection($v);
			}
			else
			{
				if (!$v->count())
				{
					$vv = (string) $v;
					$this->$k = $vv;
				}
				else
				{
					$type = key($v);
					$value = ValueTypes::build($type, $v->$type);
					$this->$k = $value;
				}
			}
		}
	}

	function __toString()
	{
		return json_encode($this);
	}

    /*
     * filter sections by names
     */
	function structs($filter=false)
	{
        if (!$filter) return array();
        if (!count($filter)) return array();
		// TODO return IF has struct = yes
		// TODO sort by filter order
		// $filter - (only, names, return) - core.oauth.enabled as input
		$sections = array();
		foreach ($this as $k => $s)
		{
			if ($s instanceof ConfigSection) 
			{
				if ($filter)
				{
					if (in_array($k, $filter)) $sections[$k] = $s;
				}
				else	
					$sections[$k] = $s;
			}
		}
		return $sections;
	}
}	
?>