<?php

$GLOBALS['WORLD']['MIGRATIONS'][1] = function () {
    println("NOOP", 1, TERM_BLUE); // Migrate to version 1
};

$GLOBALS['WORLD']['REVERSEMIGRATIONS'][1] = function () {
  println("NOOP"); //  Reverse from 1
}

?>
