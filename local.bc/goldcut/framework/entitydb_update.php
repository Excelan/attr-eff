<?php
/**
class
*/
class EntityDB_Update extends EntityDB
{
	/**
	UPDATE
	 */
	function update($lang, $id, $data_array)
	{
		$q = "UPDATE ".SQLQT."".$this->E->getTableName()."".SQLQT." SET ";

		foreach ($this->E->general_fields() as $v => $F)
		{
			if ($data_array[$v] !== null)
			{
				if (($F->type == 'integer' || $F->type == 'float' || $F->type == 'money') && ($data_array[$v] instanceof Message || is_array($data_array[$v])))
				{
					$a = $data_array[$v]->toArray();
					$k = (key($a));
					if ($F->type == 'integer') $v = (integer) $a[$k];
					else if ($F->type == 'float') $v = (float) $a[$k];
					else if ($F->type == 'money') $v = (float) $a[$k];
					$value = Field::sqlwrap($F, $v);
					if ($k == 'increment')
						$qf[] = SQLQT.$F->name.SQLQT.' = '.SQLQT.$F->name.SQLQT.' + '.$value;
					else if ($k == 'decrement')
						$qf[] = SQLQT.$F->name.SQLQT.' = '.SQLQT.$F->name.SQLQT.' - '.$value;
					else
						throw new Exception('Unknown action '.$k);
				}
				else if ($F->type == 'iarray' && USEPOSTGRESQL === true) // PG ONLY!
				{
					$direction = 'forward';
					if ($data_array[$v] instanceof Message)
					{
						if ($data_array[$v]->append)
						{
							if (is_int($data_array[$v]->append))
							{
								$a = $data_array[$v]->append;
								$cmd = 'array_append';
							}
							else
							{
								$a = $data_array[$v]->append->toArray();
								$a = 'ARRAY'.anyToString($a);
								$cmd = 'array_cat';
							}
						}
						elseif ($data_array[$v]->prepend)
						{
							$cmd = 'array_prepend';
							$direction = 'backward';
							if (is_int($data_array[$v]->prepend))
								$a = $data_array[$v]->prepend;
							else
								throw new Exception("Non int value `$data_array[$v]` for update prepend $this->E $v");
						}
						else if ($data_array[$v]->remove)
						{
							if (is_int($data_array[$v]->remove))
								$a = $data_array[$v]->remove;
							else
								$a = $data_array[$v]->remove->toArray();
							$cmd = 'array_remove';
						}
						else // simple for f=[i,i..]
							$a = $data_array[$v]->toArray();
					}
					else throw new Exception("Non array value `$data_array[$v]` for update $this->E $v"); //else $a = $data_array[$v];
					//if (is_int($a))
					if ($direction == 'forward')
						$qf[] = SQLQT.$F->name.SQLQT." = $cmd(".SQLQT.$F->name.SQLQT.', '.$a . ')' ;
					else
						$qf[] = SQLQT.$F->name.SQLQT." = $cmd(".$a.', '.SQLQT.$F->name.SQLQT.')' ;
					//else
						//$qf[] = SQLQT.$F->name.SQLQT.' = array_cat('.SQLQT.$F->name.SQLQT.', ARRAY' . anyToString($a) . ')' ;
				}
				else if ($F->type == 'tarray' && USEPOSTGRESQL === true) // PG ONLY!
				{
					$direction = 'forward';
					if ($data_array[$v] instanceof Message)
					{
						if ($data_array[$v]->append)
						{
							$a = "'".$data_array[$v]->append."'";
							$cmd = 'array_append';
						}
						elseif ($data_array[$v]->prepend)
						{
							$cmd = 'array_prepend';
							$direction = 'backward';
							$a = "'".$data_array[$v]->prepend."'";
						}
						else if ($data_array[$v]->remove)
						{
							if (is_array($data_array[$v]->remove))
								$a = $data_array[$v]->remove->toArray(); // TODO
							else
								$a = "'".$data_array[$v]->remove."'";
							$cmd = 'array_remove';
						}
						else // simple for f=[i,i..]
						{
							$simple = 1;
							$a = $data_array[$v]->toArray();
						}
						if (!$simple) {
							if ($direction == 'forward')
								$qf[] = SQLQT . $F->name . SQLQT . " = $cmd(" . SQLQT . $F->name . SQLQT . ', ' . $a . ')';
							else
								$qf[] = SQLQT . $F->name . SQLQT . " = $cmd(" . $a . ', ' . SQLQT . $F->name . SQLQT . ')';
						}
						else
						{
							if (count($a) == 0)
								$qf[] = SQLQT . $F->name . SQLQT . " = NULL"; //'{}'
							else {
                                //throw new Exception("Unimplemented tarray = [a,..]");
                                $qf[] = SQLQT . $F->name . SQLQT . " = " . "'{" . join(',',$a) . "}'::varchar[]"; // SQLARRAY
                            }
						}
					}
					else throw new Exception("Non array value `$data_array[$v]` for update $this->E $v"); //else $a = $data_array[$v];
				}
				else
				{
					$safe = Security::mysql_escape((string)$data_array[$v]);
					$data_array[$v] = $safe;
					$value = Field::sqlwrap($F, $data_array[$v]);
					$qf[] = SQLQT.$v.SQLQT.' = '.$value;
				}
			}
		}

		foreach ($this->E->lang_fields() as $F)
		{
			/**
			TODO sqlwrap as upper
			*/
			$safe = Security::mysql_escape((string)$data_array[$F->name]);
			$data_array[$F->name] = $safe;

			if (SystemLocale::default_lang() != $lang)
			{
				if ($data_array[$F->name])
					$qf[] = ''.$F->name."_$lang = '" . $data_array[$F->name] . "'";
			}
			else
			{
				if ($data_array[$F->name])
					$qf[] = ''.$F->name." = '" . $data_array[$F->name] . "'";
			}
		}

		foreach ($this->E->has_one() as $usedAs => $F)
		{
			if (isset($data_array[$usedAs]))
			{
				if ($data_array[$usedAs] === 0) $data_array[$usedAs] = 'NULL';
				$qf[] = SQLQT.$usedAs.SQLQT." = " . $data_array[$usedAs];
			}
		}

		foreach ($this->E->use_one() as $usedAs => $F)
		{
			if (isset($data_array[$usedAs]))
			{
				if ($data_array[$usedAs] === 0) $data_array[$usedAs] = 'NULL';
				$qf[] = SQLQT.$usedAs.SQLQT." = " . $data_array[$usedAs];
			}
		}

		foreach ($this->E->belongs_to() as $usedAs => $F)
		{
			if (isset($data_array[$F->name]))
			{
				if ($data_array[$F->name] === 0) $data_array[$F->name] = 'NULL';
				$qf[] = SQLQT.$usedAs.SQLQT." = " . $data_array[$F->name];
			}
		}

		foreach ($this->E->statuses as $statusid)
		{
			$status = Status::ref($statusid)->name;
			if ($data_array[$status] !== null) $qf[] = SQLQT.$status.SQLQT." = '" . $data_array[$status] . "'";
		}

		// optimized
		if (count($this->E->extendstructure))
		{
			$EO = $data_array;
			if (count($EO['_attributes']))
			{
				$qf[] = '_attributes = ' . "'".UnicodeOp::decodeUnicodeString(json_encode($EO['_attributes']))."'";
			}
			else if (isset($EO['_attributes'])) {
				$qf[] = '_attributes = NULL';
			}
		}

		// выполнять запрос только если есть мимнимум одно поле для обновления
		if (count($qf))
		{
			$q .= join($qf, ", ");
			if (is_numeric($id))
				$q .= " WHERE id = ".$id;
			$this->dblink->nquery($q);
		}
	}

}
?>
