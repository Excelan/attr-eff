<?php

$GLOBALS['WORLD']['MIGRATIONS'][2] = function () {

    println("V2", 1, TERM_BLUE);

    // processes
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Definition:Prototype:System';
    $m->isprocess = true;
    $systemprotos = $m->deliver();
    foreach ($systemprotos as $systemproto) {
        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:Definition:ProcessModel:System';
        $m->id = $systemproto->id;
        $m->indomain = $systemproto->indomain;
        $m->target = $systemproto->ofclass;
        $m->way = $systemproto->oftype;
        $m->title = $systemproto->title;
        $m->deliver();
        //println($m);
    }

    // documents
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Definition:Prototype:System';
    $m->isprocess = false;
    $systemprotos = $m->deliver();
    foreach ($systemprotos as $systemproto) {
        // preload doc class
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Definition:DocumentClass:ForPrototype';
        $m->name = $systemproto->ofclass;
        $docClass = $m->deliver();
        // create new doc proto
        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:Definition:Prototype:Document';
        $m->DefinitionPrototypeSystem = $systemproto->urn;
        if (count($docClass))
          $m->DefinitionDocumentClassForPrototype = $docClass->urn;
        $m->id = $systemproto->id;
        $m->indomain = $systemproto->indomain;
        $m->ofclass = $systemproto->ofclass;
        $m->oftype = $systemproto->oftype;
        $m->title = $systemproto->title;
        $m->unmanaged = $systemproto->unmanaged;
        $m->withhardcopy = $systemproto->withhardcopy;
        $documentProto = $m->deliver();
        // visants
        foreach ($systemproto->visants as $visant) {
            $m = new Message();
            $m->action = 'add';
            $m->urn = $visant;
            $m->to = $documentProto->urn.':visants';
            $m->deliver();
        }
        // approver
        $approver = $systemproto->approver;
        if (count($approver)) {
            $m = new Message();
            $m->action = 'update';
            $m->urn = $documentProto->urn;
            $m->approver = $systemproto->approver->urn;
            $m->deliver();
        }
    }

    // uni docs clean
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:DMS:Document:Universal';
    $uniDocs = $m->deliver();
    foreach ($uniDocs as $uniDoc) {
        $m = new Message();
        $m->action = 'delete';
        $m->urn = $uniDoc->urn;
        $m->deliver();
    }

};

$GLOBALS['WORLD']['REVERSEMIGRATIONS'][2] = function () {
  println("Reverse from 2",1,TERM_VIOLET);

  foreach (['Definition:Prototype:Document', 'Definition:ProcessModel:System'] as $proto)
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
