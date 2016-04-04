<?php

// TODO xml logs
// TODO xmpp logs

/**
 * interface Logger
{
public function log($message, $level);
}

class DemoLogger implements Logger
{
public function log($message, $level)
{
echo "Logged message: $message with level $level", PHP_EOL;
}
}

trait Loggable // implements Logger
{
protected $logger;
public function setLogger(Logger $logger)
{
$this->logger = $logger;
}
public function log($message, $level)
{
$this->logger->log($message, $level);
}
}

class Foo implements Logger
{
use Loggable;
}


$foo = new Foo;
$foo->setLogger(new DemoLogger);
$foo->log('It works', 1);
 */

define('LOGERROR', 1);
define('LOGINFO',  2);
define('LOGDEBUG', 3);

class log
{

    public static $monitor_sql = true;
    private static $instance;
    private function __construct()
    {
    }
    private static $buffer = '';

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new Log();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init()
    {
        // external logging connect
    }

    public static function buffer($message)
    {
        // sql из предыдущего в теста в теущем?
        //self::$buffer .= $message."\n";
        self::$buffer = $message."\n";
    }

    public static function buffer_clear()
    {
        $r = self::$buffer;
        self::$buffer = '';
        return $r;
    }


    /**
    reason - error
    */
    private static function log($l, $NS, $level = LOGINFO, $showorigin=false)
    {
        if (DEBUGLOGORIGIN === true) {
            $showorigin = 2;
        }
        if (ENV !== LOG_ENV) {
            return false;
        }
        $l = anyToString($l);
        if (!$NS || SCREENLOG === true) {
            $trace = debug_backtrace();
            $callerClass = $trace[2]['class'];
            $callerFunction = $trace[2]['function'];
            if (!$NS) {
                $NS = 'trace';
            }
            $l .= "\t({$callerClass}::{$callerFunction})";
        }

        if ($level == LOGERROR) {
            $levelstr = '!';
        }
        if ($level == LOGINFO) {
            $levelstr = ' ';
        }
        if ($level == LOGDEBUG) {
            $levelstr = '.';
        }
        $date = date("d/m H:i:s");

        //  && $NS != 'mail'  &&
        if (ENV == 'DEVELOPMENT' && SCREENLOG === true && $NS != 'sql' && $NS != 'test' && $NS != 'list' && $NS != 'listdb' && $NS != 'mysql' && $NS != 'main' && $NS!= 'mqsend' && !strstr($NS, 'mail-')) {
            println('['.strtoupper($NS)."] ".$levelstr.' '.$l, 1, TERM_GRAY, $showorigin);
        } // 2
        if (ENV == 'DEVELOPMENT' && SCREENLOGSQL === true && $NS == 'sql' && OPTIONS::get('pause_DEBUG_SQL') != true) {
            println('['.strtoupper($NS)."] ".$levelstr.' '.$l, 1, TERM_GRAY, $showorigin);
        } // 2
        if (ENV == 'DEVELOPMENT' && SCREENLOGMQSEND === true && $NS == 'mqsend') {
            println('['.strtoupper($NS)."] ".$levelstr.' '.$l, 1, TERM_GRAY, $showorigin);
        } // 2

        $filename = BASE_DIR.'/log/'.HOST.'-'.$NS.'.log';
        $file = fopen($filename, 'a+');
        if (flock($file, LOCK_EX)) {
            $trace = debug_backtrace();
            $callerClass = $trace[2]['class'];
            $callerFunction = $trace[2]['function'];
            $ofile = substr($trace[1]['file'], strlen(BASE_DIR));
            $oline = $trace[1]['line'];
            $l .= "\t({$callerClass}::{$callerFunction}) {$oline}:{$ofile}";
            fwrite($file, "[{$levelstr}] $date\t".$l.PHP_EOL); // "\n"
            flock($file, LOCK_UN); // release the lock
        } else {
            println("Couldn't get the WRITE EX lock for $filename file!", 1, TERM_RED);
        }
        fclose($file);

        if ($NS != 'sql' && $NS != 'test' && $NS != 'list' && $NS != 'listdb' && $NS != 'mysql' && $NS != 'main' && $NS!= 'mqsend' && $NS!= 'benchwidget' && $NS!= 'widget' && !strstr($NS, 'mail-')) {
            $NS = 'GLOBAL';
            $filename = BASE_DIR.'/log/'.HOST.'-'.$NS.'.log';
            $file = fopen($filename, 'a+');
            if (flock($file, LOCK_EX)) {
                $trace = debug_backtrace();
                $callerClass = $trace[2]['class'];
                $callerFunction = $trace[2]['function'];
                $ofile = substr($trace[1]['file'], strlen(BASE_DIR));
                $oline = $trace[1]['line'];
                $l .= "\t({$callerClass}::{$callerFunction}) {$oline}:{$ofile}";
                fwrite($file, "[{$levelstr}] $date\t".$l.PHP_EOL); // "\n"
            flock($file, LOCK_UN); // release the lock
            } else {
                println("Couldn't get the WRITE EX lock for $filename file!", 1, TERM_RED);
            }
            fclose($file);
        }
    }

    public static function info($l, $NS)
    {
        self::log($l, $NS, LOGINFO);
    }

    public static function error($l, $NS)
    {
        self::log($l, $NS, LOGERROR);
    }

    public static function debug($l, $NS)
    {
        self::log($l, $NS, LOGDEBUG);
    }
}
