<?php

$GLOBALS['WORLD']['MIGRATIONS'][6] = function () {

    println("V6 - Uniq", 1, TERM_BLUE);

    $q = [
      'CREATE UNIQUE INDEX sysproto_unquness ON "Definition_Prototype_System" (indomain, ofclass, oftype)',
      'CREATE UNIQUE INDEX docproto_unquness ON "Definition_Prototype_Document" (indomain, ofclass, oftype)',
      'CREATE UNIQUE INDEX processmodel_unquness ON "Definition_ProcessModel_System" (indomain, target, way)',
      'CREATE UNIQUE INDEX actor_stage_unquness ON "RBAC_DocumentPrototypeResponsible_System" (managementrole, processmodelprototype, documentprototype, stage)'
    ];

    $rdb = DB::link();

    foreach ($q as $sql) {
        try {
            println($sql);
            $rdb->nquery($sql);
        } catch (Exception $e) {
            println($e, 1, TERM_RED);
        }
    }

};
