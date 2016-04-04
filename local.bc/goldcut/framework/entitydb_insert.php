<?php
/**
EntityDB_insert
*/

class EntityDB_insert extends EntityDB
{
	/**
	INSERT
	*/
	public function insert($lang, $EO, $EOi) // , $eoid can del eoid
	{

		//dprintln($EO,1,TERM_GRAY);

		$q = "INSERT INTO ".SQLQT."{$this->E->getTableName()}".SQLQT." ( ";

		if (!$EO['id'])
		{
			$newuuid = new UUID();
			$EO['id'] = $newuuid->toInt();
		}

		if ($EO['id'] > 0)
		{
			$fs[] = 'id';
			$fd[] = $EO['id'];
		}

		foreach ($this->E->statuses as $statusid)
		{
			$status = Status::ref($statusid)->name;
			$fs[] = SQLQT . $status . SQLQT;
			$fd[] = $EO[$status];
		}

		if ( $this->E->is_multy_lang() )
		{
			$fs[] = "translated_{$lang}";
			$fd[] = 1;
		}

		foreach ($this->E->general_fields() as $v => $F)
		{
			$fs[] = SQLQT.$v.SQLQT;
			if ($F->type == 'sequence') //  && $EO[$v]
			{
				//printlnd($EO[$v],1,TERM_GREEN);
				//var_dump($EO[$v]);
				$fd[] = Field::sqlwrap($F, $EO[$v]);
				$fs[] = SQLQT.$v.'_index'.SQLQT;
				$fd[] = "'[\"".join('","',$EO[$v]->getLabels())."\"]'::json";
			}
			elseif ($F->type == 'iarray')
			{
				$fd[] = Field::sqlwrap($F, $EO[$v]);
			}
			elseif ($F->type == 'tarray')
			{
				$fd[] = Field::sqlwrap($F, $EO[$v]);
			}
			elseif ($F->type == 'xml')
			{
				$fd[] = Field::sqlwrap($F, $EO[$v]);
			}
			elseif ($F->type == 'datetime')
			{
				if ($EO[$v])
					$fd[] = "'{$EO[$v]}'::timestamp";//;
				else
					$fd[] = 'NULL';
			}
			else
			{
				//printlnd($EO[$v],1,TERM_GREEN);
				$EO[$v] = Security::mysql_escape((string)$EO[$v]);
				$fd[] = Field::sqlwrap($F, $EO[$v]); // JSON is here
			}
		}

		foreach ($this->E->lang_fields() as $F)
		{
			$safe = Security::mysql_escape((string)$EOi[$F->name]);
			$EOi[$F->name] = $safe;

			if (SystemLocale::default_lang() != $lang)
				$fs[] = SQLQT.$F->name."_{$lang}".SQLQT;
			else
				$fs[] = SQLQT.$F->name.SQLQT;
			$fd[] = "'".$EOi[$F->name]."'";
		}

		foreach ($this->E->belongs_to() as $usedAs => $E)
		{
			//println("$usedAs => $E",1,TERM_VIOLET);
			$fs[] = SQLQT.$usedAs.SQLQT;//."_id";
			if ($EO[$E->name] == '')
				$fd[] = "NULL";
			else
				$fd[] = "'".$EO[$E->name]."'";
		}

		foreach ($this->E->has_one() as $usedAs => $E)
		{
			$fs[] = SQLQT.$usedAs.SQLQT;//."_id";
			if ($EO[$usedAs] == '')
				$fd[] = "NULL";
			else
				$fd[] = "'".$EO[$usedAs]."'";
		}

		foreach ($this->E->use_one() as $usedAs => $E)
		{
			$fs[] = SQLQT.$usedAs.SQLQT;//."_id";
			if ($EO[$usedAs] == '')
				$fd[] = "NULL";
			else
				$fd[] = "'".$EO[$usedAs]."'";
		}

		if (count($this->E->extendstructure))
		{
			if (count($EO['_attributes']))
			{
				$fs[] = '_attributes';
				$fd[] = "'".json_encode($EO['_attributes'])."'";
			}
		}

		$q .= join($fs, ", ");
		$q .= " ) VALUES ( ";
		$q .= join($fd, ", ");
		$q .= " )";

		$this->dblink->nquery($q);
		return $EO['id'];
	}
}
?>