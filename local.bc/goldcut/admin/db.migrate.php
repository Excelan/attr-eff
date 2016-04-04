<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require dirname(__FILE__).'/../../goldcut/boot.php';

$root_login = 'root';
$root_password = ROOT_PASS;
if ($_COOKIE['login']) {
    if (md5($root_login.$root_password) == $_COOKIE['login']) {
        $username = 'root';
    } else {
        die("You have sent a bad cookie.");
    }
} else {
    header('Location: /goldcut/admin/aauth.php');
    exit(0);
}

$dblink = DB::link();

try {
    Migrate::full();
    println("Structure migrate: done", 1, TERM_GREEN);
} catch (Exception $e) {
    $error = GCException::ghostBuster($e, 'MIGRATE');
    println($e, 1, TERM_RED);
}

try {
    $dblink->begin();

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Settings:Option:System';
    $m->key = 'worldversion';
    $worldversion = $m->deliver();

    if (!count($worldversion)) {
        $m->action = 'create';
        $m->value = 1;
        $worldversion = $m->deliver();
    }

    $GLOBALS['WORLD']['DBVERSION'] = (int) $worldversion->value;

    if (!$GLOBALS['WORLD']['CODEVERSION']) {
        throw new Exception("No world codeversion");
    }

    if ($GLOBALS['WORLD']['CODEVERSION'] > $GLOBALS['WORLD']['DBVERSION']) {
        for ($v = $GLOBALS['WORLD']['DBVERSION']+1; $v <= $GLOBALS['WORLD']['CODEVERSION']; $v++) {
            println("Migrate to world level $v", 1, TERM_GRAY);
            $GLOBALS['WORLD']['MIGRATIONS'][$v]->__invoke();
        }
        // save changes
        $m = new Message();
        $m->action = 'update';
        $m->urn = $worldversion->urn;
        $m->key = 'worldversion';
        $m->value = $GLOBALS['WORLD']['CODEVERSION'];
        $m->deliver();
        // commit
        $dblink->commit();
        println("World version migrate to {$GLOBALS['WORLD']['CODEVERSION']}: done", 1, TERM_GREEN);
    } elseif ($GLOBALS['WORLD']['CODEVERSION'] < $GLOBALS['WORLD']['DBVERSION']) {
        for ($v = $GLOBALS['WORLD']['DBVERSION']; $v > $GLOBALS['WORLD']['CODEVERSION']; $v--) {
            println("Reverse migrate to world level $v", 1, TERM_GRAY);
            $GLOBALS['WORLD']['REVERSEMIGRATIONS'][$v]->__invoke();
        }
        // save changes
        $m = new Message();
        $m->action = 'update';
        $m->urn = $worldversion->urn;
        $m->key = 'worldversion';
        $m->value = $GLOBALS['WORLD']['CODEVERSION'];
        $m->deliver();
        // commit
        $dblink->commit();
    } else {
        println("World versions equals {$GLOBALS['WORLD']['CODEVERSION']} = {$GLOBALS['WORLD']['DBVERSION']}");
    }
} catch (Exception $e) {
    $dblink->rollback();
    $error = GCException::ghostBuster($e, 'MIGRATE');
    println($e, 1, TERM_RED);
}
