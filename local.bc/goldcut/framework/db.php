<?php

class DatabaseDuplicateException extends Exception {}

class DB
{
    public static function link($inst_env = null, $encoding = 'utf8')
    {
        if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
            return DBPGSQL::link($inst_env, $encoding);
        else // mysql
            return DBMYSQL::link($inst_env, $encoding);
    }
}

?>