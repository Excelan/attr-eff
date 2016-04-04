<?php

class DataSet implements Iterator, Countable
{
	private $loaded = false;
	public $dataset = array();
	public $query;
	public $entitymeta;
	public $total;
	public $totalOnAllLevels;
	public $distinctgroups;
	public $cursor; // uuid of current datarow
	public $position = 0;
	public $preloads = array();
	public $rowsCreated = array();

	function __construct($ids, $entitymeta, $query = null, $total = null, $distinctgroups=null)
	{
		$this->dataset = $ids;
		$this->entitymeta = $entitymeta;
		$this->query = $query;
		$this->total = $total;
		$this->distinctgroups = $distinctgroups;
		if (!$entitymeta) throw new Exception("DATASET WITHOUT ENTITY NAME");

		/**
		 * TREE BEHAVE (TEMP UNUSED)
		 */
		/*
		if ($this->entitymeta->is_tree)
		{
			foreach ($this->dataset as & $node) 
			{
				if ($node['_parent'])
				{
					$k = "urn-{$this->entitymeta->name}-{$node['_parent']}";
					$node['_parent'] = $k;
					$parent = & $this->dataset[$k];
					$parent['children'][] = "urn-{$this->entitymeta->name}-{$node['id']}";
				}
			}
		}
		*/
		// leave only roots. iterate datarow for childs
		// только здесь др знает о своих детях
		// pop levels > 1 to cache. dr > cache[urn]. urn:resolve by cache, dr->load urn by cache 
	}

	public function byId($id)
	{
		$urn = "urn:{$this->entitymeta->name}:{$id}";
		$var = new DataRow($this->dataset[$urn], $this);
		return $var;
	}

	public function byURN($urn)
	{
		$urn = (string)$urn;
		$var = new DataRow($this->dataset[$urn], $this);
		return $var;
	}

	public function patch($urn, $field, $value)
	{
		if (!$this->dataset[$urn]) throw new Exception("NO $urn in DS for patch");
		$urnO = new URN($urn);
		$this->dataset[$urn][$field] = $value;
		unset($this->rowsCreated[$urnO->uuid->toInt()]);
	}

	public function merge($ds)
	{
		if (count($ds))
			$this->dataset = array_merge($this->dataset, $ds->dataset);
	}

	public function extendMergeParents()
	{
		if ($this->count() > 1) throw new Exception('extendMergeParents() is only for dataset[1]');
		if (!$this->_parent) return false;
		$p = $this->parent;
		$this->merge($p);
		while ($p = $p->parent)
			$this->merge($p);
	}

	public function getColumn($name, $nulls = false)
	{
		$column = array();
		$copydataset = $this->dataset;
		foreach ($copydataset as $urn => $data)
		{
			if ($data[$name] or $nulls)
			{
				if (is_numeric($data[$name]))
					$column[$urn] = $data[$name];
				else
				{
					Log::debug("ds->getColumn $name", 'strange');
				}
			}
		}
		return $column;
	}

	public function getColumnArray($name, $nulls = false, $uniq = false)
	{
		$column = [];
		$copydataset = $this->dataset;
		foreach ($copydataset as $urn => $data)
		{
			if ($data[$name] || $nulls)
			{
				$column[] = $data[$name];
			}
		}
		if ($uniq) $column = array_unique($column);
		return $column;
	}

	public function slice($n, $offset = 0)
	{
		$this->dataset = array_slice($this->dataset, $offset, $n, true);
	}

	public function sortby($internalSortField, $dir = 'reverse') // or forward, or 1/-1
	{
		if ($dir == 'reverse' || $dir == -1)
			$cmp = '<';
		else
			$cmp = '>';
		$sortfunc = create_function('$a,$b', 'return ($a[' . $internalSortField . '] ' . $cmp . ' $b[' . $internalSortField . ']);');
		uasort($this->dataset, $sortfunc); // return $b[$internalSortField] > $a[$internalSortField]
	}

	public function sortwith($providedSortFunc)
	{
		uasort($this->dataset, $providedSortFunc);
	}

	public function exclude($f, $val)
	{
		foreach ($this->dataset as $urn => $v)
		{
			if ($v[$f] == $val) unset($this->dataset[$urn]);
		}
	}

	public function selectAbove($f, $val)
	{
		foreach ($this->dataset as $urn => $v)
		{
			if ($v[$f] < $val) unset($this->dataset[$urn]);
		}
	}

	public function selectBelow($f, $val)
	{
		foreach ($this->dataset as $urn => $v)
		{
			if ($v[$f] > $val) unset($this->dataset[$urn]);
		}
	}

	public function excludeFuture($f = 'created')
	{
		foreach ($this->dataset as $urn => $v)
		{
			if ($v[$f] > time()) unset($this->dataset[$urn]);
		}
	}

	public function filter($f, $val)
	{
		foreach ($this->dataset as $urn => $v)
		{
			if ($v[$f] != $val) unset($this->dataset[$urn]);
		}
	}

	public function group($field_name, $internalSortField = false) // group Forward, internal sort Forward
	{
		foreach ($this as $d)
		{
			$kk = $d->$field_name;
			$dataG[$kk][] = $d;
		}
		krsort($dataG);
		foreach ($dataG as $g => $itemsInGroup)
		{
			usort($itemsInGroup, create_function('$a,$b', 'return $b->' . $internalSortField . ' > $a->' . $internalSortField . ';'));
		}
		return $dataG;
	}

	public function normalizeLevels()
	{
		$minLevel = null;
		foreach ($this->dataset as $urn => &$d)
		{
			if (!$minLevel)
			{
				$minLevel = $d['_level'];
				$diff = $minLevel - 1;
			}
			$d['_level'] = $d['_level'] - $diff;
		}
	}

	// TODO add maxLevel
	// param currentId - position in tree. will return level from function
	// on second sort to normalize levels use $firstsort = false
	public function treesort($currentId, $firstsort = true)
	{
		if (count($this->dataset) < 2) return false;
		$savedata = $this->dataset;
		$savedata2 = $this->dataset;
		$savedata3 = $this->dataset;
		$savedata4 = $this->dataset;
		$levels = array();
		foreach ($this->dataset as $urn => &$d)
		{
			if ($firstsort)
			{
				if (!$d['_parent'])
				{
					$neworder[] = $urn;
					if (!$levels[$urn]) $levels[$urn] = 1;
				} else
					continue;
			}
			foreach ($savedata as $urn2 => &$dd)
			{
				if ($dd['_parent'] == $d['id'])
				{
					$neworder[] = $urn2;
					if (!$levels[$urn2]) $levels[$urn2] = 2;
					foreach ($savedata2 as $urn3 => &$ddd)
					{
						if ($ddd['_parent'] == $dd['id'])
						{
							$neworder[] = $urn3;
							if (!$levels[$urn3]) $levels[$urn3] = 3;
							foreach ($savedata3 as $urn4 => &$dddd)
							{
								if ($dddd['_parent'] == $ddd['id'])
								{
									$neworder[] = $urn4;
									if (!$levels[$urn4]) $levels[$urn4] = 4;
									foreach ($savedata4 as $urn5 => &$ddddd)
									{
										if ($ddddd['_parent'] == $dddd['id'])
										{
											$neworder[] = $urn5;
											if (!$levels[$urn5]) $levels[$urn5] = 5;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		foreach ($neworder as $urn)
		{
			$founded = false;
			$this->dataset[$urn]['_level'] = $levels[$urn];
			$newdataset[$urn] = &$this->dataset[$urn];
			if ($currentId && !$founded)
			{
				if ($newdataset[$urn]['id'] == $currentId)
				{
					$currentIdLevel = $levels[$urn];
					$founded = true;
				}
			}
		}
		$this->dataset = $newdataset;
		return $currentIdLevel;
	}

	public function treeSelectIdsDown($cid)
	{
		$i = 0;
		$childs = array();
		foreach ($this->dataset as $urn => $d)
		{
			if ($d['id'] == $cid)
			{

				$start = true;
				$clevel = $d['_level'];
			}
			if ($start)
			{
				$i++;
				if ($i == 1)
				{
					$childs[] = $d['id'];
				} else
				{
					if ($d['_level'] > $clevel)
						$childs[] = $d['id'];
					else
						$start = false;
				}
			}
		}
		$protect = array_unique($childs);
		return $protect;
	}

	public function treeFilterIdsDown($cid)
	{
		$protect = $this->treeSelectIdsDown($cid);
		foreach ($this->dataset as $urn => &$d)
		{
			if (!in_array($d['id'], $protect))
				unset($this->dataset[$urn]);
		}
		$this->normalizeLevels();
	}

	public function treeSelectIdsUp($ids, $withNeighbors)
	{
		foreach ($this->dataset as $urn => $d)
		{
			if ($withNeighbors && $d['_parent'] == $withNeighbors) $protect[] = $d['id'];
			if (in_array($d['id'], $ids))
			{
				$protect[] = $d['id'];
				if ($d['_parent']) $hasP = true;
				while ($hasP)
				{
					$protect[] = $d['_parent'];
					$k = "urn:{$this->entitymeta->name}:{$d['_parent']}";
					$d = $this->dataset[$k];
					if ($d['_parent']) $hasP = true;
					else
					{
						$hasP = false;
						$protect[] = $d['id'];
					}
				}
			}
		}
		$protect = array_unique($protect);
		return $protect;
	}

	// $withNeighbors is id of _parent el for which we want neighbors
	public function treeFilterIdsUp($ids, $cid, $withNeighbors, $withChildren)
	{
		$protectUp = $this->treeSelectIdsUp($ids, $withNeighbors);
		if ($withChildren === true)
		{
			$protectDown = $this->treeSelectIdsDown($cid);
			$protect = array_merge($protectUp, $protectDown);
		} else
		{
			$protect = $protectUp;
		}
		foreach ($this->dataset as $urn => &$d)
		{
			if (!in_array($d['id'], $protect))
				unset($this->dataset[$urn]);
		}
	}

	// TODO filter _parent etc
	/**
	 * public function filter($field_name, $fieldValue) // group Forward, internal sort Forward
	 * {
	 * }
	 */

	public function __call($name, $arguments)
	{
		return Plugin::manager()->dataset_plugin($this, $name);
	}

	/**
	 * delegate call to internal DataRow
	 */
	public function __get($field_name)
	{
		if ($field_name == 'entitymeta')
			return $this->entitymeta;
		if ($field_name == 'entity')
			return $this->entitymeta;
		if ($this->count() == 1)
			return $this->current()->$field_name;
		elseif ($this->count() == 0)
		{
			throw new Exception("DATASET {$this->entitymeta->name} IS EMPTY. IT CANT ACT AS SINGLE DATAROW {$this->entitymeta->name}->[$field_name] {$this->query}");
		} else
		{
			Log::error("{$this->entitymeta->name}->[{$field_name}] {$this->query}", 'datasetrelationerror');
			//foreach ($this as $r)
			//	Log::debug($r, 'datasetrelationerror');
			throw new Exception("DATASET WITH ROWS > 1 (" . $this->count() . ") CANT ACT AS SINGLE DATAROW $->[$field_name] {$this->query}");
		}
	}

	public function isempty()
	{
		if (count($this->dataset) == 0) return true;
		else return false;
	}

	public function count()
	{
		return (count($this->dataset));
	}


	public function last()
	{
		$size = $this->count();
		$var = $this->dataset[$size];
		if ($var)
		{
			$var = new DataRow($var, $this);
		}
		return $var;
	}

	public function first()
	{
		$this->rewind();
		return $this->current();
	}

	// cursor methods
	public function current()
	{
		$var = current($this->dataset);
		if ($var)
		{
			$this->cursor = $var['id'];
			if ($this->rowsCreated[$this->cursor])
			{
				return $this->rowsCreated[$this->cursor];
			} else
			{
				$var = new DataRow($var, $this);
				$this->rowsCreated[$this->cursor] = $var;
				return $var;
			}
		}
		return $var;
	}

	public function rewind()
	{
		reset($this->dataset);
	}

	public function key()
	{
		$var = key($this->dataset);
		return $var;
	}

	public function next()
	{
		$var = next($this->dataset);
		$this->cursor = $var['id'];
		$this->position++;
		return $var;
	}

	public function valid()
	{
		$var = $this->current() !== false;
		return $var;
	}


	public function __toString()
	{
		//if (count($this->dataset) == 1)
		if (false) // not working after this call
		{
			return (string)$this->current();
		} else
		{
			if ($this->total) $total = ". total: {$this->total}";
			$txt = "{" . $this->entitymeta->name . "} [count: " . count($this->dataset) . $total . "]";
			if (count($this->distinctgroups)) $txt .= ' distinct groups: '.anyToString($this->distinctgroups);
			return $txt;
		}
	}

	public function asURNs()
	{
		$json = array();
		foreach ($this as $key => $cur)
			$json[] = (string)$cur->urn;
		reset($this->dataset);
		return $json;
	}

	public function asIDs()
	{
		$ids = array();
		foreach ($this as $key => $cur)
			$ids[] = $cur->urn->uuid;
		reset($this->dataset);
		return $ids;
	}

	public function hasURN($urn)
	{
		foreach ($this as $key => $cur)
			if ((string)$cur->urn == (string)$urn)
				return true;
	}

	public function toArray($o = null, $include = null)
	{
		$json = array();
		foreach ($this as $key => $cur)
			$json[] = $cur->toArray($o, $include);
		return $json;
	}

	public function toJSON($o = null, $include = null)
	{
		return json_encode($this->toArray($o, $include));
	}

}

?>