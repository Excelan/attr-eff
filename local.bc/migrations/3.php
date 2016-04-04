<?php

$GLOBALS['WORLD']['MIGRATIONS'][3] = function () {

    println("V3", 1, TERM_BLUE);

    // rbac start processes
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:RBAC:ProcessStartPermission:System';
    $startperms = $m->deliver();
    foreach ($startperms as $startperm) {
        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:RBAC:Permission:Atomic';
        $m->actor = $startperm->managementrole->urn;
        $m->cando = 'ProcessStart';
        $m->withprototype = $startperm->subjectprototype->urn;
        //$m->onid = $startperm->;
        $m->deliver();
        //println($m);

        // add to doc proto starters list
    }

};

$GLOBALS['WORLD']['REVERSEMIGRATIONS'][3] = function () {
  println("Reverse from 3",1,TERM_VIOLET);

  foreach (['RBAC:Permission:Atomic'] as $proto)
  {
  $m = new Message();
  $m->action = 'load';
  $m->urn = 'urn:'.$proto;
  $x = $m->deliver();
  foreach ($x as $d) {
      $m = new Message();
      $m->action = 'delete';
      $m->urn = $d->urn;
      $m->deliver();
    }
  }
}

?>
