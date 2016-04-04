<?php
/**
update table set created = unix_timestamp(concat(cast(date as char),' ',cast(time as char)))
*/
class migrate
{

    private function __construct()
    {
    }

    private static function createDBifnotexists()
    {
        try {
            $db = DB::link();
        } catch (Exception $e) {
            println("Can't connect to DB in current env ".ENV.". Will try to create it", 1, TERM_RED);
            println($e);
            try {
                /*
                 * MYSQL
                $db = DB::link('TEST', 'utf8');
                $user = $GLOBALS['CONFIG'][ENV]['DB']['USER'];
                $password = $GLOBALS['CONFIG'][ENV]['DB']['PASSWORD'];
                $dbname = $GLOBALS['CONFIG'][ENV]['DB']['DBNAME'];
                $db->raw_query("CREATE USER '{$user}'@'%' IDENTIFIED BY  '{$password}';");
                $db->raw_query("GRANT USAGE ON *.* TO  '{$user}'@'%' IDENTIFIED BY  '{$password}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;");
                $db->raw_query("CREATE DATABASE IF NOT EXISTS  {$dbname} ;");
                $db->raw_query("GRANT ALL PRIVILEGES ON  {$dbname} . * TO  '{$user}'@'%';");
                */
                // TODO PGSQL
                if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) {
                    // pgsql

                    $env = ENV;
                    $connection = pg_pconnect("host={$GLOBALS['CONFIG'][$env]['DB']['HOST']} port=5432 dbname=template0 user=postgres password=");
                    pg_query($connection, "CREATE ROLE \"{$GLOBALS['CONFIG'][$env]['DB']['USER']}\" CREATEDB NOINHERIT LOGIN PASSWORD '{$GLOBALS['CONFIG'][$env]['DB']['PASSWORD']}'");
                    pg_query($connection, "CREATE DATABASE \"{$GLOBALS['CONFIG'][$env]['DB']['DBNAME']}\" WITH OWNER = \"{$GLOBALS['CONFIG'][$env]['DB']['USER']}\" ENCODING = 'UTF8' TABLESPACE = \"pg_default\" TEMPLATE = \"template0\"");
                    println("POSTGRESQL DB CREATED");
                }
            } catch (Exception $e) {
                println("Cant connect to TEST ENV DB or create DB in .".ENV, 1, TERM_RED);
                println($e);
            }
        }
    }

    public static function clear($arrayOfE)
    {
        foreach ($arrayOfE as $Ename) {
            $DBSTORE = new EntityDB(Entity::ref($Ename));
            $DBSTORE->clean_table();
        }
    }

    public static function clearAll()
    {
        println("CLEAR ALL");
        ListDatabase::allKeysDel();

        $db = DB::link();
        try {
            $db->raw_query("DELETE FROM ".SQLQT."mappings".SQLQT); // clear mappings table
        } catch (Exception $e) {
        }
        foreach (Entity::each_entity() as $entity) { // clear all another tables
            Migrate::clear(array($entity->name));
        }
    }

    public static function full()
    {
        self::createDBifnotexists();

        if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) {
            return MigratePGSQL::full(); // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }
        // PGSQL



        // MYSQL

        $db = DB::link();
//        var_dump($db);
        /**
        TODO! BIGINT 64 on 64
        */
        $db->raw_query("CREATE TABLE IF NOT EXISTS mappings (
			entity1 int  NOT NULL,
			id1 int  NOT NULL,
			entity2 int  NOT NULL,
			id2 int  NOT NULL,
			ns INT DEFAULT NULL,
			weight INT DEFAULT NULL,
			relation varchar(255) DEFAULT NULL,
			created INT DEFAULT NULL
	)"); // ,UNIQUE  uniq5 (  entity1 ,  id1 ,  entity2 ,  id2,  ns )
        /* TODO INDEXES!
         * ,
            KEY entity1 (entity1),
            KEY entity2 (entity2),
            KEY id1 (id1),
            KEY id2 (id2),
            KEY ns (ns),
            KEY created (created)
         */

        foreach (Entity::each_entity() as $entity) {
            $DBSTORE = new EntityDB($entity);
            $sync[$entity->name]['id'] = 'integer';

            $uniqs = $entity->checkunique;

            if (count($entity->extendstructure)) {
                //				$sync[$entity->name]['_properties'] = 'text';
//				$sync[$entity->name]['_variators'] = 'text';
                $sync[$entity->name]['_attributes'] = 'text';
            }

            if ($entity->is_multy_lang()) {
                foreach ($entity->lang_codes() as $lang) {
                    //if ( SystemLocale::default_lang() != $lang)
                    $sync[$entity->name]['translated_'.$lang] = 'integer';
                    $indexes[$entity->name]['translated_'.$lang] = 'integer';
                }
            }

            foreach ($entity->statuses as $status) {
                //println($status);
                $sync[$entity->name][Status::ref($status)->name] = 'status';
                $indexes[$entity->name][Status::ref($status)->name] = 'integer';
            }

            foreach ($entity->belongs_to() as $E) {
                $sync[$entity->name][$E->name.'_id'] = 'integer';
                $indexes[$entity->name][$E->name.'_id'] = 'integer';
            }

            foreach ($entity->has_one() as $usedAs => $E) {
                $sync[$entity->name][$usedAs.'_id'] = 'integer';
            }

            foreach ($entity->use_one() as $usedAs => $E) {
                $sync[$entity->name][$usedAs.'_id'] = 'integer';
            }

            if ($entity->is_multy_lang()) {
                foreach ($entity->lang_fields() as $usedName => $F) {
                    $sync[$entity->name][$usedName] = $F->type;
                    foreach ($entity->lang_codes() as $lang) {
                        if (SystemLocale::default_lang() != $lang) {
                            $sync[$entity->name][$F->name.'_'.$lang] = $F->type;
                        }
                    }
                }
                foreach ($entity->general_fields() as $usedName => $F) {
                    $sync[$entity->name][$usedName] = $F->type; // TODO if not added in lang fields
                }
            } else {
                foreach ($entity->fields() as $usedName => $F) {
                    $sync[$entity->name][$usedName] = $F->type;
                    if ($F->type == 'date') {
                        $indexes[$entity->name][$usedName] = $F->type;
                    }
                }
            }

            // merge indexes from entity config with general - statuses, belongs to, has one
            if (is_numeric(key($entity->index))) {
                // vs {"active":"integer", "0":"created"}

                $kit = array();
                foreach ($entity->index as $ki) {
                    $kit[$ki] = 'unknown';
                }
                $entity->index = $kit;
            }
            $indexes[$entity->name] = array_merge($indexes[$entity->name], $entity->index);
            /*
            if ($indexes[$entity->name])
            {
                println($entity->name,1,TERM_VIOLET);
                println($indexes[$entity->name]);
                //if (count($entity->index)) println($entity->index);
            }
            */

            // TABLE CREATE
            $exst = $DBSTORE->exists_table();
            if (empty($exst)) {
                $database_charset = $db->charset();
                $database_collate = $db->collate();
                $DBSTORE->create_table($database_charset, $database_collate);
                // TODO $DBSTORE->sync_indexes_in_table($indexes, $uniqs);
                printH("CREATED ENTITY TABLE {$entity->name}");
                continue;
                // STOP HERE!
            } else {
                foreach ($exst as $field) {
                    $columns[$entity->name][] = $field['COLUMN_NAME'];
                }
            }


            /**
            TODO type mismatch
            TODO lang fields string not int
            */
            // SYNC COLUMNS WITH ENTITY FIELDS
            foreach ($sync[$entity->name] as $c => $ftype) {
                // columns in entity

                if (!in_array($c, $columns[$entity->name])) {
                    // columns in db

                    println("ADD NEW FIELD TO DB {$entity->name}.{$c}", 1, TERM_GREEN);
                    $type = "INT";
                    $def = "NULL";
                    if ($ftype == 'status') {
                        $def = '0';
                    }

                    $ce = explode('_', $c); // field name widthout _lang suffix
                    if (count($ce)>2) {
                        throw new Exception("Dont use _ in field names {$entity->name}/{$c}");
                    }
                    $cc = $ce[0];

                    //if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
                        //$to_sqltype = array("xml"=>"xml","json"=>"JSON","set"=>"VARCHAR(32)","status"=>"INT","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"TEXT", "integer"=>"INT", "float"=>"REAL","date"=>"DATE","timestamp"=>"INT", "option"=>"INT", "money"=>"NUMERIC(14,2)"); // , "image"=>"VARCHAR(48)"
                    //else // mysql
                    $to_sqltype = array("xml"=>"TEXT","json"=>"TEXT","set"=>"VARCHAR(32)","status"=>"TINYINT","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"MEDIUMTEXT", "integer"=>"INT", "float"=>"FLOAT","date"=>"DATE","timestamp"=>"INT", "option"=>"TINYINT", "money"=>"FLOAT", "ipv4"=>"INT","iarray"=>"varchar(255)"); // , "image"=>"VARCHAR(48)"
                    $type = $to_sqltype[$ftype];
                    if (!$type) {
                        throw new Exception("Unknown sql field type. name $c of type $ftype - {$type}");
                    }
                    //if ($F->default !== null) $def = $F->default;
                    $q = "ALTER TABLE {$entity->name} ADD {$c} {$type} DEFAULT $def";

                    /**
                    if (Field::exists($cc))
                    {
                        $F = Field::ref($cc);
                        $to_sqltype = array("set"=>"VARCHAR(32)", "image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"MEDIUMTEXT", "integer"=>"INT", "float"=>"FLOAT","date"=>"DATE","timestamp"=>"INT", "option"=>"TINYINT"); // , "image"=>"VARCHAR(48)"
                        $type = $to_sqltype[$F->type];
                        if (!$to_sqltype[$F->type]) throw new Exception("Unknown sql field type. name $F->name of type {$F->type}");
                        if ($F->default !== null) $def = $F->default;
                    }
                    else
                    {
                        if ($c == 'id')
                        {
                            if (PHP_INT_MAX > 2147483647) $platform64 = true;
                            $type = ($platform64) ? 'BIGINT' : 'INT';
                            if (FORCE32BIT) $type = 'INT';
                        }
                        else if ($c == '_properties' or $c == '_variators')
                            $type = 'TEXT';
                        else
                            $type = 'INT';
                        if (strpos($c,'translated_') !== false)
                        {
                            $addq = false;
                            if ($c == "translated_".SystemLocale::$DEFAULT_LANG)
                                $addq = "UPDATE {$entity->name} SET $c = 1";
                        }
                    }
                    $q = "ALTER TABLE {$entity->name} ADD {$c} {$type} DEFAULT $def";
                    */

                    $dblink = DB::link();
                    try {
                        println($q, 1, TERM_RED);
                        $dblink->nquery($q);
                        if ($addq) {
                            println($addq, 1, TERM_RED);
                            $dblink->raw_query($addq);
                        }
                    } catch (Exception $e) {
                        println($e, 1, TERM_RED);
                    }
                }
            }

            // INDEXES IN DB
            //println($indexes);
            // TODO $DBSTORE->sync_indexes_in_table($indexes, $uniqs);

            // DELETE STALE COLUMNS NOT EXISTS IN ENTITY FIELDS
            foreach ($columns[$entity->name] as $c) {
                if (!in_array($c, array_keys($sync[$entity->name]))) {
                    println("DELETE OBSOLETE FIELD IN DB {$entity->name}.{$c}", 1, TERM_RED);
                    $q = "ALTER TABLE {$entity->name} DROP {$c}";
                    $dblink = DB::link();
                    try {
                        println($q, 1, TERM_RED);
                        $dblink->nquery($q);
                    } catch (Exception $e) {
                        println($e, 1, TERM_RED);
                    }
                }
            }
        }
        //echo "Migrated\n";
    }
}
