<?php

/**
TODO donw doble convert encoding. use cp1251 internal opt
 */

class DBMYSQL
{
    private static $instance = array();
    private $mysqli;
    private $dbname;
    private $env;
    private $encoding; // mysql = pg sql = mssql in UTF8/UTF-8 naming?

    public function begin() {
        //$link = self::link();
        $this->raw_query('BEGIN');
    }

    public function commit() {
        //$link = self::link();
        $this->raw_query('COMMIT');
    }

    public function rollback() {
        //$link = self::link();
        $this->raw_query('ROLLBACK');
    }

    private function __construct($env, $encoding = 'utf8')
    {
        if (!defined('SQLQT')) define('SQLQT','`');
        $this->encoding = $encoding;
        if ($env)
        {
            $this->env = $env;
        }
        else
        {
            if (TEST_ENV === true && PRODUCTION_DB_IN_TEST_ENV !== true)
                $this->env = "TEST";
            else
            {
                if (defined('ENV'))
                    $this->env = ENV;
                else
                    $this->env = "PRODUCTION";
            }
        }
        $this->connect();
    }

    public static function link($inst_env=null, $encoding = 'utf8')
    {
        if (!self::$instance[$inst_env][$encoding])
        {
            Utils::startTimer('mysqlconnect');
            self::$instance[$inst_env][$encoding] = new DBMYSQL($inst_env, $encoding);
            $ctime = Utils::reportTimer('mysqlconnect');
            Log::info("@ Connected ENV:[$inst_env] ENC:[$encoding] CTIME: [{$ctime['time']}]",'mysql');
            self::$instance[$inst_env][$encoding]->init();
        }
        return self::$instance[$inst_env][$encoding];
    }

    private function init()
    {
        $this->mysqli->query("SET NAMES '{$this->encoding}'");
    }

    public function charset()
    {
        return $GLOBALS['CONFIG'][$this->env]['DB']['CHARSET'];
    }

    public function collate()
    {
        return $GLOBALS['CONFIG'][$this->env]['DB']['COLLATE'];
    }

    private function connect()
    {
        if (is_array($GLOBALS['CONFIG'][$this->env]))
        {
//            $this->dbname = 'lotsof';//$GLOBALS['CONFIG'][$this->env]['DB']['DBNAME'];
            $this->dbname = $GLOBALS['CONFIG'][$this->env]['DB']['DBNAME'];
            if (MYSQL_PERSISTENT === true) $persistent = 'p:';
            // http://blog.ulf-wendel.de/2009/php-53-persistent-connections-with-extmysqli/
            // You can get the faster but less idiot-proof ext/mysql style persistent connections if you define MYSQLI_NO_CHANGE_USER_ON_PCONNECT when compiling PHP
            $this->mysqli = new mysqli($persistent.$GLOBALS['CONFIG'][$this->env]['DB']['HOST'], $GLOBALS['CONFIG'][$this->env]['DB']['USER'], $GLOBALS['CONFIG'][$this->env]['DB']['PASSWORD'], $this->dbname);
        }
        else
        {
            throw new Exception("UNKNOWN [{$this->env}] ENVIRONMENT");
        }

        if (mysqli_connect_errno())
        {
            throw new Exception("[{$this->env}] MySQL CONNECT TO ".$GLOBALS['CONFIG'][$this->env]['DB']['HOST']." ERROR: ". mysqli_connect_error() );
        }
    }

    function dbname()
    {
        return $this->dbname;
    }

    function raw_mysqli()
    {
        return $this->mysqli;
    }

    function nquery($q)
    {
        if (DEBUG_SQL === true) Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true ) println($q, 1, TERM_YELLOW);

        if (!$this->mysqli->query($q))
        {
            //println($q,1,TERM_RED);
            //println ($this->mysqli->errno);
            /**
            TODO parse error string for duplicate field/fields and
            DUPLICATE ENTRY 'VRO-2012-U-FOKUS-POLSKIH-ZM' FOR KEY 'URI'
             */
            if ($this->mysqli->errno == 1062) throw new DatabaseDuplicateException((string) $this->mysqli->error);
            else throw new Exception( (string) $this->mysqli->error );
        }
        /**
        if id is 'autoinc' - returned id, but if id just plain int - return true
        //if ( $id = $this->mysqli->insert_id ) return $id;
         */
        return true;
    }

    function tohashquery($q)
    {
        if (DEBUG_SQL === true) Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true ) println($q, 1, TERM_BLUE);
        if ($result = $this->mysqli->query($q))
            return $this->as_simple_hash($result);
        else
            throw new Exception( (string) $this->mysqli->error );
    }

    function count_query($q)
    {
        if (DEBUG_SQL === true) Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true ) println($q, 1, TERM_BLUE);
        // TODO 0 != null on query error (table not exists)
        $result = $this->mysqli->query($q);
        if ($result)
        {
            $r = $result->fetch_array(); //MYSQL_NUM
            $result->close();
        }
        else $r = 0;
        return $r;
    }

    /**
    не возвращает результата
     */
    function raw_query($q)
    {
        if (DEBUG_SQL === true) Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true ) println($q, 1, TERM_YELLOW);

        if (!$this->mysqli->query($q)) {
            println($q,1,TERM_RED);
            throw new Exception( (string) $this->mysqli->error );
        }
        return true;
    }

    function query($q, $e='')
    {
        if (DEBUG_SQL === true) Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) println($q, 1, TERM_BLUE);
        if (Log::$monitor_sql) Log::buffer($q);
        if ($result = $this->mysqli->query($q))
            return $this->dbresult2hash($result, $e);
        else
        {
            if ($this->mysqli->errno == 1146) throw new Exception("TABLE NOT EXISTS ($q). MAY BE BLANK DATABASE? <a href=/goldcut/admin/db.migrate.php>migrate</a> or shell php test/sys/production.load.php");
            if ($this->mysqli->errno == 1054) throw new Exception("FIELD NOT EXISTS: {$this->mysqli->error}");
            // 1146 table not exists
            // TODO Error message information is listed in the share/errmsg.txt file. %d and %s represent numbers and strings, respectively, that are substituted into the Message values when they are displayed.
            // http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html
            //println($q,1,TERM_RED);
            //println ($this->mysqli->errno);
            //throw new Exception( (string) $this->mysqli->error );
            throw new Exception( 'SQL ERROR: '. $q );
        }
        return $res;
    }

    function perror()
    {
        if ($this->mysqli->error) printf("ERR: %s\n", $this->mysqli->error);
    }


    private function dbresult2hash($result, $e='')
    {
        //
        $res = array();
        while( $r = $result->fetch_assoc() )
        {
            $KEY = "urn-{$e}-{$r['id']}";
            $res[$KEY]=$r;
        }
        $result->close();
        return $res;
    }

    private function as_simple_hash($result) {
        $res = array();
        while( $r = $result->fetch_assoc() ) {
            $res[]=$r;
        }
        $result->close();
        return $res;
    }

    private function as_simple_hash_new($result) {
        $res = array();
        while( $r = $result->fetch_assoc(MYSQL_ASSOC) ) // BUGGY
        {
            $res[]=$r;
        }
        $result->close();
        return $res;
    }

    function __destruct() {
        $this->mysqli->close();
    }
}

?>