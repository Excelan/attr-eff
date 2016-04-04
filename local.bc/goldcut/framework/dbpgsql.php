<?php

/**
TODO donw doble convert encoding. use cp1251 internal opt
 * client.query('INSERT INTO test (data) VALUES ($1)::JSON', [JSON.stringify(myArray)], function(err) {
 * https://www.compose.io/articles/is-postgresql-your-next-json-database/
 */
function safeq($v)
{
    return DBPGSQL::link()->escape($v);
}

class dbpgsql
{
    private static $instance = array();
    private $connection;
    private $dbname;
    private $env;
    private $encoding;

    public function begin()
    {
        //println('BEGIN',1,TERM_GREEN);
        //$link = self::link();
        //$this->raw_query('BEGIN');
        // TODO option SERIALIZABLE | REPEATABLE READ | READ COMMITTED | READ UNCOMMITTED
        //$txmode = ' TRANSACTION ISOLATION LEVEL SERIALIZABLE';
        $txmode = ' TRANSACTION ISOLATION LEVEL READ COMMITTED';
        //if (DEBUG_SQL === true) Log::debug("TX BEGIN $txmode", 'sql');
        $this->raw_query('BEGIN'.$txmode);
    }

    public function commit()
    {
        //$link = self::link();
        //println('COMMIT', 1, TERM_GREEN);
        //if (DEBUG_SQL === true) Log::debug('TX COMMIT', 'sql');
        $this->raw_query('COMMIT');
        Log::error(pg_last_error($this->connection), 'sql');
    }

    public function rollback()
    {
        //$link = self::link();
        //println('ROLLBACK',1,TERM_RED);
        //if (DEBUG_SQL === true) Log::debug('TX ROLLBACK', 'sql');
        $this->raw_query('ROLLBACK');
    }

    private function __construct($env, $encoding = 'utf8')
    {
        if (!defined('SQLQT')) {
            define('SQLQT', '"');
        }
        $this->encoding = $encoding;
        if ($env) {
            $this->env = $env;
        } else {
            if (TEST_ENV === true && PRODUCTION_DB_IN_TEST_ENV !== true) {
                $this->env = "TEST";
            } else {
                if (defined('ENV')) {
                    $this->env = ENV;
                } else {
                    $this->env = "PRODUCTION";
                }
            }
        }
        $this->connect();
    }

    public static function link($inst_env=null, $encoding = 'utf8')
    {
        if (!self::$instance[$inst_env][$encoding]) {
            Utils::startTimer('pgconnect');
            self::$instance[$inst_env][$encoding] = new DBPGSQL($inst_env, $encoding);
            $ctime = Utils::reportTimer('pgconnect');
            //Log::debug("@ Connected ENV:[$inst_env] ENC:[$encoding] CTIME: [{$ctime['time']}]",'pgsql');
            //self::$instance[$inst_env][$encoding]->init();
        }
        return self::$instance[$inst_env][$encoding];
    }

    private function init()
    {
        //$this->connection->query("SET NAMES '{$this->encoding}'");
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
        if (is_array($GLOBALS['CONFIG'][$this->env])) {
            $this->dbname = $GLOBALS['CONFIG'][$this->env]['DB']['DBNAME'];
            $this->connection = pg_pconnect("host={$GLOBALS['CONFIG'][$this->env]['DB']['HOST']} port=5432 dbname={$GLOBALS['CONFIG'][$this->env]['DB']['DBNAME']} user={$GLOBALS['CONFIG'][$this->env]['DB']['USER']} password={$GLOBALS['CONFIG'][$this->env]['DB']['PASSWORD']}");
        } else {
            throw new Exception("UNKNOWN [{$this->env}] ENVIRONMENT");
        }

        if (!$this->connection) {
            throw new Exception("[{$this->env}] PGSQL CONNECT TO HOST:".$GLOBALS['CONFIG'][$this->env]['DB']['HOST']." ERROR UNKNOWN");
        }
    }

    public function dbname()
    {
        return $this->dbname;
    }

    public function raw_connection()
    {
        return $this->connection;
    }

    public function nquery($q)
    {
        Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) {
            println($q, 1, TERM_YELLOW, false);
        }

        if (!pg_query($this->connection, $q)) {
            $error = pg_last_error($this->connection);
            if (stripos($error, "could not serialize access due to concurrent update") !== false) {
                sleep(2);
                if (!pg_query($this->connection, $q)) {
                    $error = pg_last_error($this->connection);
                    if (DEBUG_SQL === true) {
                        Log::error('TRY AGAIN FAILED! '.$error, 'sql');
                    }
                    throw new Exception('TRY AGAIN FAILED! ' . $error);
                }
            } else {
                if (DEBUG_SQL === true) {
                    Log::error($error, 'sql');
                }
                throw new Exception('SQL ERROR: ' . $error);
            }
        }
        return true;
    }

    public function tohashquery($q)
    {
        Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) {
            println($q, 1, TERM_BLUE, false);
        }
        if ($result = pg_query($this->connection, $q)) {
            return $this->as_simple_hash($result);
        } else {
            $error = pg_last_error($this->connection);
            if (DEBUG_SQL === true) {
                Log::error($error, 'sql');
            }
            throw new Exception('SQL ERROR: '. $error);
        }
    }

    public function queryJson($q)
    {
        Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) {
            println($q, 1, TERM_BLUE, false);
        }
        if ($result = pg_query($this->connection, $q)) {
            if ($r = pg_fetch_row($result)) {
                return json_decode($r[0], true);
            }
            pg_free_result($result);
            return null;
        } else {
            $error = pg_last_error($this->connection);
            if (DEBUG_SQL === true) {
                Log::error($error, 'sql');
            }
            throw new Exception($error);
        }
    }

    public function count_query($q)
    {
        Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) {
            println($q, 1, TERM_BLUE, false);
        }
        // TODO 0 != null on query error (table not exists)
        //$result = $this->connection->query($q);
        $result = pg_query($q);
        if ($result) {
            $r = pg_fetch_array($result, null, PGSQL_NUM);
            pg_free_result($result);
        } else {
            $r = 0;
        }
        return $r;
    }

    /**
    не возвращает результата
     */
    public function raw_query($q)
    {
        if (!$q) {
            throw new Exception("No query");
        }
        Log::debug($q, 'sql');
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) {
            println($q, 1, TERM_YELLOW, false);
        }

        $r = pg_query($this->connection, $q);
        if (!$r) {
            //println($er,1,TERM_RED);
            $error = pg_last_error($this->connection);
            if (DEBUG_SQL === true) {
                Log::error($error, 'sql');
            }
            throw new Exception('SQL ERROR: '. $error);
        }
        return true;
    }

    public function query($q, $e='')
    {
        Log::debug($q, 'sql');
        /*
        if (strpos($q, 'SELECT * FROM "ManagedProcess_Execution_Record"  WHERE id =') === 0) {
            throw new Exception('SLOW');
        }
        */
        if (DEBUG_SQL === true && OPTIONS::get('pause_DEBUG_SQL') !== true) {
            println($q, 1, TERM_BLUE, false);
        }
        if (Log::$monitor_sql) {
            Log::buffer($q);
        }
        if ($result = pg_query($this->connection, $q)) {
            return $this->dbresult2hash($result, $e);
        } else {
            //if ($this->connection->errno == 1146) throw new Exception("TABLE NOT EXISTS. MAY BE BLANK DATABASE? <a href=/goldcut/admin/db.migrate.php>migrate</a> or shell php test/sys/production.load.php");
            //if ($this->connection->errno == 1054) throw new Exception("FIELD NOT EXISTS: {$this->connection->error}");
            // 1146 table not exists
            // TODO Error message information is listed in the share/errmsg.txt file. %d and %s represent numbers and strings, respectively, that are substituted into the Message values when they are displayed.
            // http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html
            //println($q,1,TERM_RED);
            //println ($this->mysqli->errno);
            //throw new Exception( (string) $this->mysqli->error );
            $error = pg_last_error($this->connection);
            if (DEBUG_SQL === true) {
                Log::error($error, 'sql');
            }
            throw new Exception('SQL ERROR: '. $error . "\n $q");
        }
        return $res;
    }

    public function perror()
    {
        if ($this->connection->error) {
            printf("ERR: %s\n", $this->connection->error);
        }
    }


    private function dbresult2hash($result, $e='')
    {
        $res = array();
        while ($r = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $KEY = "urn:{$e}:{$r['id']}";
            $res[$KEY]=$r;
        }
        pg_free_result($result);
        return $res;
    }

    private function as_simple_hash($result)
    {
        $res = array();
        while ($r = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $res[]=$r;
        }
        pg_free_result($result);
        return $res;
    }

    public function escape($str)
    {
        return pg_escape_string($this->connection, $str);
    }

    public function __destruct()
    {
        pg_close($this->connection);
    }

    public function table_metadata($table_name)
    {
        return pg_meta_data($this->connection, $table_name);
    }
}
