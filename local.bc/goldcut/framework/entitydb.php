<?php

/**
 * TODO if INDEX exists but not UNIQ if we add same UNIQ we will hav 2 dup indexes with uniq and non uniq
 */
class EntityDB
{

	protected $dblink;
	protected $E;

	public function __construct($E)
	{
		$this->E = $E;
		$this->dblink = DB::link();
	}

	public function exists_table()
	{
		$dbname = $this->dblink->dbname();
		if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
			$q = "SELECT f.attname AS name, pg_catalog.format_type(f.atttypid,f.atttypmod) AS type FROM pg_attribute f JOIN pg_class c ON c.oid = f.attrelid JOIN pg_type t ON t.oid = f.atttypid LEFT JOIN pg_attrdef d ON d.adrelid = c.oid AND d.adnum = f.attnum LEFT JOIN pg_namespace n ON n.oid = c.relnamespace LEFT JOIN pg_constraint p ON p.conrelid = c.oid AND f.attnum = ANY (p.conkey) LEFT JOIN pg_class AS g ON p.confrelid = g.oid WHERE c.relkind = 'r'::char AND n.nspname = 'public' AND c.relname = '{$this->E->getTableName()}'";
		else // mysql
			$q = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$this->E->getTableName}' AND table_schema = '$dbname'";
		// TODO SELECT attname FROM pg_attribute,pg_class WHERE attrelid=pg_class.oid AND relname='TableName' AND attstattarget <>0;
		$r = $this->dblink->tohashquery($q);
		return $r;
	}

	public function indexes_in_table()
	{
		$dbname = $this->dblink->dbname();
		// Table 	Non_unique 	Key_name 	Seq_in_index 	Column_name 	Collation 	Cardinality 	Sub_part 	Packed 	Null 	Index_type Comment
		if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
		{
			$q = "
			select
			    t.relname as table_name,
			    i.relname as index_name,
			    a.attname as column_name
			from
			    pg_class t,
			    pg_class i,
			    pg_index ix,
			    pg_attribute a
			where
			    t.oid = ix.indrelid
			    and i.oid = ix.indexrelid
			    and a.attrelid = t.oid
			    and a.attnum = ANY(ix.indkey)
			    and t.relkind = 'r'
			    and t.relname = '{$this->E->name}'
			order by
			    t.relname,
			    i.relname;
				";
			$r = $this->dblink->tohashquery($q);
			//println($r);
			foreach ($r as $idxe)
			{
				//println($idxe,2);
				$has_indexes[] = $idxe['column_name'];
			}
			return array('index' => $has_indexes, 'uniq' => $has_uniq);
		} else
		{
			$q = "SHOW INDEX FROM {$this->E->getTableName()}";
			$r = $this->dblink->tohashquery($q);
			foreach ($r as $idxe)
			{
				$has_indexes[] = $idxe['Column_name'];
				if ($idxe['Non_unique'] == '0') $has_uniq[] = $idxe['Column_name'];
			}
			return array('index' => $has_indexes, 'uniq' => $has_uniq);
		}
	}

	public function sync_indexes_in_table($indexes, $uniqs)
	{
		//if ($indexes) println($indexes,1,TERM_GREEN);
		//if ($uniqs) println($uniqs,1,TERM_GRAY);

		$entity = $this->E;
		$r = $this->indexes_in_table();
		//println($r,1,TERM_YELLOW);

		foreach ($r['index'] as $idxe)
		{
			$has_indexes[$entity->name][] = $idxe;
		}
		foreach ($r['uniq'] as $idxe)
		{
			$has_uniq[$entity->name][] = $idxe;
		}

		foreach ($entity->checkunique as $uniqfs)
		{
			if (!in_array($has_uniq[$entity->name], $uniqfs)) $has_uniq[$entity->name][] = $uniqfs;
		}

		// SYNC INDEXES
		foreach ($indexes as $hi => $ct) // indexes in entity
		{
			if (!in_array($hi, $has_indexes[$entity->name])) // indexes in db
			{
				//Log::info("INDEX NOT exists in db {$entity->getTableName()}.{$hi}",'db');

				if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true)
					$q = "CREATE INDEX \"{$entity->getTableName()}_{$hi}_idx\" ON " . SQLQT . "{$entity->getTableName()}" . SQLQT . " ($hi)";
				else
					$q = "ALTER TABLE {$entity->getTableName()} ADD INDEX ({$hi})";
				$dblink = DB::link();
				try
				{
					$dblink->nquery($q);
				}
				catch (Exception $e)
				{
					// TODO FIX
					//Log::error("FAIL ADD INDEX {$entity->getTableName()}.{$hi}", 'db');
					//Log::error($e->getMessage(), 'db');
				}
			}
			// else EXISTS INDEX
		}

		// SYNC UNIQS
		//printH('UNX '.$entity->name);
		/**
		 * uniq in e config. dont forget to specify f + f_lang!
		 */
		foreach ($uniqs as $ct => $hu)
			//foreach ($uniqs[$entity->name] as $hu)
		{
			if (true)
			{
				//println("- UNIQ NOT exists in db {$entity->name}.{$hu}");
				if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true)
					$q = "ALTER TABLE \"$entity->name\" ADD CONSTRAINT \"{$entity->name}_uniq\" UNIQUE (\"$hu\")";
				else
					$q = "ALTER TABLE {$entity->name} ADD UNIQUE ({$hu})";
				//println($q,1,TERM_GAY);
				$dblink = DB::link();
				try
				{
					$dblink->nquery($q);
				} catch (Exception $e)
				{ /* println("FAIL ADD UNIQUE {$entity->name}.{$hu}", 1, TERM_RED); $dblink->perror(); */
				}
			} else
			{
				//println("+ UNIQ INDEX in db {$entity->name}.{$hu}");
			}
		}
	}


	function clean_table()
	{
		$this->dblink->raw_query("DELETE FROM " . SQLQT . $this->E->getTableName() . SQLQT);
	}

	function create_table($database_charset, $database_collate) // $lang=false
	{
		//if (!$database_charset)
		//throw new Exception('NO DB CHARSET DEFINED!');

		/**
		 * TODO add per field charset creation option
		 */
		$database_charset = 'utf8';
		$database_collate = 'utf8_general_ci';
		/*
        if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
		    $to_sqltype = array("set"=>"VARCHAR(32)", "string"=>"VARCHAR(255)","image"=>"TEXT","text"=>"TEXT","richtext"=>"TEXT", "integer"=>"INT", "float"=>"REAL","date"=>"DATE","timestamp"=>"INT", "option"=>"INT");
        else // mysql
            $to_sqltype = array("set"=>"VARCHAR(32)", "string"=>"VARCHAR(255)","image"=>"TEXT","text"=>"TEXT","richtext"=>"MEDIUMTEXT", "integer"=>"INT", "float"=>"FLOAT","date"=>"DATE","timestamp"=>"INT", "option"=>"TINYINT");
		*/
		$this->dblink->nquery("DROP TABLE IF EXISTS " . SQLQT . $this->E->getTableName() . SQLQT);

		if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
		{
			$type = 'BIGINT'; // no unsigned int
		} else // mysql
		{
			if (PHP_INT_MAX > 2147483647) $platform64 = true;
			$type = ($platform64) ? 'BIGINT' : 'INT'; // TODO PGSQL 64?
			if (FORCE32BIT) $type = 'INT';
		}

		//$unsigned = 'unsigned';
		$q = "CREATE TABLE " . SQLQT . $this->E->getTableName() . SQLQT . " ( \n " . SQLQT . "id" . SQLQT . " {$type} $unsigned NOT NULL,\n"; // auto_increment
		/*
		foreach ($this->E->general_fields() as $fname => $F)
		{
			$def = 'NULL';
			if ($F->default !== null) $def = $F->default;
			if (!$fname) throw new Exception("Unexistent field in entity {$this->E}");
			if (!$to_sqltype[$F->type]) throw new Exception("Unknown sql field type. name $fname of type {$F->type}");
			$q .= "$fname " . $to_sqltype[$F->type] . " DEFAULT $def, \n";
		}
		foreach ($this->E->belongs_to() as $e) {
			$q .= "".$e->name."_id INT,\n";
		}
		foreach ($this->E->has_one() as $usedAs => $e) {
			$q .= "".$usedAs."_id INT,\n";
		}
		foreach ($this->E->use_one() as $usedAs => $e) {
			$q .= "".$usedAs."_id INT,\n";
		}
		foreach ($this->E->has_statuses() as $e) {
			$q .= "".$e->name." INT,\n";
		}
		*/
		/**
		 * if ($this->E->is_multy_lang())  // TODO check if type is string/text and not INT def
		 * {
		 * foreach ($this->E->lang_codes() as $lang)
		 * {
		 * $q .= "translated_{$lang} TINYINT, \n";
		 * foreach ($this->E->lang_fields() as $F)
		 * if ( SystemLocale::default_lang() != $lang)
		 * $q .= "{$F->name}_{$lang} " . $to_sqltype[$F->type] . ", \n";
		 * else
		 * $q .= "{$F->name} " . $to_sqltype[$F->type] . ", \n";
		 * }
		 * }
		 */
		if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
			$q .= "PRIMARY KEY (id) )";
		else
			$q .= "PRIMARY KEY (id) ) {$db_engine} DEFAULT CHARACTER SET {$database_charset} COLLATE {$database_collate};";
		// ENGINE=MyISAM
		// TODO $db_engine provide

		try
		{
			$this->dblink->nquery($q);
		} catch (Exception $e)
		{
			println('Create table error - ' . $e, 1, TERM_RED);
			print '<pre>' . $q . '</pre>';
		}
	}

	public function add_column($eParent, $eChild)
	{
		$q = "ALTER TABLE " . SQLQT . "{$eParent}" . SQLQT . " ADD " . SQLQT . "{$eChild}" . SQLQT . " INT DEFAULT NULL";
		$this->dblink->nquery($q);
	}

}

?>