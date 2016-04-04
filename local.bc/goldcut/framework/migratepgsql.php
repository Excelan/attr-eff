<?php

/*
 * INDEX
 * http://stackoverflow.com/questions/2204058/show-which-columns-an-index-is-on-in-postgresql
 * http://stackoverflow.com/questions/109325/postgresql-describe-table
 * http://robert-reiz.com/2012/01/17/showing-indexes-in-postgresql/
 * http://www.niwi.be/2013/02/17/postgresql-database-table-indexes-size/
 */

class MigratePGSQL {

    public static $mapping = array("datetime"=>"timestamp with time zone","hirestimestamp"=>"timestamp","boolean"=>"boolean","option"=>"smallint","set"=>"smallint",
    "sequence"=>"integer[]","table"=>"JSON","xml"=>"XML","json"=>"JSON","status"=>"smallint","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"TEXT",
    "integer"=>"INT", "long"=>"bigint", "float"=>"REAL","date"=>"DATE","timestamp"=>"INT", "money"=>"NUMERIC(14,2)","ipv4"=>"inet","iarray"=>"integer[]","tarray"=>"varchar(255)[]");

    public static function full()
    {
        $db = DB::link();

        // http://www.postgresonline.com/journal/archives/141-Lowercasing-table-and-column-names.html
        if (USE_SQL_MAPPINGS_TABLE === true)
        {
            $describeTable = $db->table_metadata('mappings');
            //$db->raw_query("DROP TABLE ".SQLQT."mappings".SQLQT);
            if ($describeTable === false) {
                $db->raw_query("CREATE TABLE " . SQLQT . "mappings" . SQLQT . " (
                    entity1 int  NOT NULL,
                    id1 int  NOT NULL,
                    entity2 int  NOT NULL,
                    id2 int  NOT NULL,
                    ns INT DEFAULT NULL,
                    weight INT DEFAULT NULL,
                    relation varchar(255) DEFAULT NULL,
                    created INT DEFAULT NULL,
                    UNIQUE (  entity1 ,  id1 ,  entity2 ,  id2,  ns )
    	        )");

                $db->raw_query("CREATE INDEX " . SQLQT . "entity1" . SQLQT . " ON " . SQLQT . "mappings" . SQLQT . " (" . SQLQT . "entity1" . SQLQT . ")");
                $db->raw_query("CREATE INDEX " . SQLQT . "entity2" . SQLQT . " ON " . SQLQT . "mappings" . SQLQT . " (" . SQLQT . "entity2" . SQLQT . ")");
                $db->raw_query("CREATE INDEX " . SQLQT . "id1" . SQLQT . " ON " . SQLQT . "mappings" . SQLQT . " (" . SQLQT . "id1" . SQLQT . ")");
                $db->raw_query("CREATE INDEX " . SQLQT . "id2" . SQLQT . " ON " . SQLQT . "mappings" . SQLQT . " (" . SQLQT . "id2" . SQLQT . ")");
            }
        }
        // CONSTRAINT production UNIQUE(date_prod)
        // CONSTRAINT con1 CHECK (did > 100 AND name <> '')
        // CREATE INDEX index_name ON table_name (column_name);


        foreach (Entity::each_entity() as $entity)
        {
            $DBSTORE = new EntityDB($entity);
            $sync[$entity->name]['id'] = 'integer';

            $uniqs = $entity->checkunique;
            //if ($uniqs) println($uniqs,1,TERM_RED);

            if (count($entity->extendstructure))
            {
//                $sync[$entity->name]['_properties'] = 'text';
//                $sync[$entity->name]['_variators'] = 'text';
                $sync[$entity->name]['_attributes'] = 'json';
            }

            if ($entity->is_multy_lang())
            {
                foreach ($entity->lang_codes() as $lang)
                {
                    //if ( SystemLocale::default_lang() != $lang)
                    $sync[$entity->name]['translated_'.$lang] = 'integer';
                    $indexes[$entity->name]['translated_'.$lang] = 'integer';
                }
            }

            foreach ($entity->statuses as $status)
            {
                //println($status);
                $sync[$entity->name][Status::ref($status)->name] = 'status';
                $indexes[$entity->name][Status::ref($status)->name] = 'integer';
            }

            foreach ($entity->belongs_to() as $usedAs => $E)
            {
                $sync[$entity->name][$usedAs] = 'long';
                $indexes[$entity->name][$usedAs] = 'long';
            }

            foreach ($entity->has_one() as $usedAs => $E)
            {
                $sync[$entity->name][$usedAs] = 'long';
            }

            foreach ($entity->use_one() as $usedAs => $E)
            {
                $sync[$entity->name][$usedAs] = 'long';
            }

            foreach ($entity->lists() as $list)
            {
                $rel = $list['entity'];
    			$listns = $list['ns'];
    			$listname = $list['name'];
                $sync[$entity->name][$listname.'_'.$rel->getAlias()] = 'sequence';
            }

            if ($entity->is_multy_lang())
            {
                foreach ($entity->lang_fields() as $usedName => $F)
                {
                    $sync[$entity->name][$usedName] = $F->type;
                    foreach ($entity->lang_codes() as $lang)
                    {
                        if ( SystemLocale::default_lang() != $lang)
                        {
                            $sync[$entity->name][$F->name.'_'.$lang] = $F->type;
                        }
                    }
                }
                foreach ($entity->general_fields() as $usedName => $F)
                {
                    $sync[$entity->name][$usedName] = $F->type; // TODO if not added in lang fields
                    if (AUTOINDEXSETOPTION === true && ($F->type == 'set' || $F->type == 'option'))
                        $indexes[$entity->name][$F->name] = 'integer';
                }
            }
            else
            {
                foreach ($entity->fields() as $usedName => $F)
                {
                    $sync[$entity->name][$usedName] = $F->type;
                    if ($F->type == 'date') $indexes[$entity->name][$usedName] = $F->type;
                    else if ($F->type == 'sequence')
                    {
                        $sync[$entity->name][$usedName."_index"] = 'json';
                    }
                    else if (AUTOINDEXSETOPTION === true && ($F->type == 'set' || $F->type == 'option'))
                        $indexes[$entity->name][$F->name] = 'integer';
                }
            }

            //println($entity->name);
            //println($indexes[$entity->name]);

            // merge indexes from entity config with general - statuses, belongs to, has one
            if (is_numeric(key($entity->index))) // vs {"active":"integer", "0":"created"}
            {
                $kit = array();
                foreach ($entity->index as $ki) $kit[$ki] = 'unknown';
                $entity->index = $kit;
            }
            $indexes[$entity->name] = array_merge($indexes[$entity->name], $entity->index);

            //println($indexes[$entity->name],2);

            /*
            // columns
            println($entity->name.' '.json_encode($sync[$entity->name]));
            // indexes
            if ($indexes[$entity->name]) println($entity->name.' indexes '.json_encode(array_keys($indexes[$entity->name])),2,TERM_GRAY);
            */


            // TABLE CREATE
            $exst = $DBSTORE->exists_table();
            //println($exst,1,TERM_YELLOW);
            if (empty($exst))
            {
                // TODO create table
                $database_charset = $db->charset();
                $database_collate = $db->collate();
                $DBSTORE->create_table($database_charset, $database_collate);
                printH("CREATED ENTITY TABLE {$entity->name}");
                continue;
            }
            else
            {
                foreach ($exst as $field )
                {
                    if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) {
                        if (!in_array($field['name'],array('tableoid','cmax','xmax','cmin','xmin','ctid')))
                            $columns[$entity->name][] = $field['name']; // pgsql
                    }
                    else
                        $columns[$entity->name][] = $field['COLUMN_NAME']; // mysql
                }
                /*
                println($columns[$entity->name], 3, TERM_GRAY);
                */
            }
            //continue;

            // SYNC COLUMNS WITH ENTITY FIELDS
            foreach ($sync[$entity->name] as $c => $ftype) // columns in entity
            {
                // @ $c = strtolower($c);
                // @ $columns[$entity->name] = array_map('strtolower', $columns[$entity->name]);
                if (!in_array($c, $columns[$entity->name])) // columns in db
                {
                    $type = "INT";
                    $def = "NULL";
                    if ($ftype == 'status') $def = '0';

                    $ce = explode('_',$c); // field name widthout _lang suffix
                    if (count($ce)>2) throw new Exception("Dont use _ in field names {$entity->name}/{$c}");
                    $cc = $ce[0];
                    /*
                    if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
                        $to_sqltype = array("xml"=>"XML","json"=>"JSON","set"=>"VARCHAR(32)","status"=>"INT","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"TEXT", "integer"=>"INT", "float"=>"REAL","date"=>"DATE","timestamp"=>"INT", "option"=>"INT", "money"=>"NUMERIC(14,2)"); // , "image"=>"VARCHAR(48)"
                    else // mysql
                        $to_sqltype = array("xml"=>"TEXT","json"=>"TEXT","set"=>"VARCHAR(32)","status"=>"TINYINT","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"MEDIUMTEXT", "integer"=>"INT", "float"=>"FLOAT","date"=>"DATE","timestamp"=>"INT", "option"=>"TINYINT", "money"=>"FLOAT"); // , "image"=>"VARCHAR(48)"
                    */
                    //"set"=>"VARCHAR(32)"
                    $to_sqltype = self::$mapping;
                    $type = $to_sqltype[$ftype];
                    if (!$type) throw new Exception("Unknown sql field type. name $c of type $ftype - {$type}");
                    //if ($F->default !== null) $def = $F->default;
                    $q = "ALTER TABLE ".SQLQT."{$entity->getTableName()}".SQLQT." ADD ".SQLQT."{$c}".SQLQT." {$type} DEFAULT $def";

                    // cut

                    $dblink = DB::link();
                    try
                    {
                        /*
                        if ($ftype == 'sequence')
                        {
                            $q2 = "ALTER TABLE ".SQLQT."{$entity->name}".SQLQT." ADD ".SQLQT."{$c}_index".SQLQT." json DEFAULT $def";
                            $dblink->nquery($q2);
                            println($q2,2,TERM_RED);
                        }
                        */
                        println("ADD NEW FIELD TO DB {$entity->getTableName()}.{$c} (NOT IN ".json_encode($columns[$entity->name]).")",1,TERM_GREEN);
                        println($q,1,TERM_RED);
                        $dblink->nquery($q);
                        if ($addq) {
                            println($addq,1,TERM_VIOLET);
                            $dblink->raw_query($addq);
                        }
                    }
                    catch (Exception $e)
                    {
                        println($e,1,TERM_RED);
                    }
                }
            }

            // DELETE STALE COLUMNS NOT EXISTS IN ENTITY FIELDS
            $xc = array_keys($sync[$entity->name]);
            //$columns[$entity->name] = array_map('strtolower', $columns[$entity->name]);
            // @ $xc = array_map('strtolower', $xc);
            sort($xc);
            sort($columns[$entity->name]);
//            println($columns[$entity->name],1,TERM_YELLOW);
//            println($xc,1,TERM_YELLOW);
            foreach ($columns[$entity->name] as $c)
            {
                // @ $c = strtolower($c);
                if (!in_array($c, $xc))
                {
                    println("DELETE OBSOLETE FIELD $c IN DB {$entity->name}.{$c} (NOT IN SYNC ".json_encode($sync[$entity->name]).")",1,TERM_RED);
                    $q = "ALTER TABLE ".SQLQT."{$entity->getTableName()}".SQLQT." DROP ".SQLQT."{$c}".SQLQT;
                    $dblink = DB::link();
                    try
                    {
                        println($q,1,TERM_RED);
                        $dblink->nquery($q);
                    }
                    catch (Exception $e)
                    {
                        println($e,1,TERM_RED);
                    }
                }
            }

            $DBSTORE->sync_indexes_in_table($indexes[$entity->name], $uniqs);
        }
        // INDEXES IN DB
        //println($indexes);
    }

}

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

?>
