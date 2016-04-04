<?php

if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

set_time_limit(0);
ob_implicit_flush();

gc_enable();

$em = Entity::each_managed_entity('Photo');

foreach($em['Photo'] as $manager)
{
    gc_collect_cycles();
    if ($manager->name == 'photo') continue;
    printH($manager->name);

    $m = new Message();
    $m->urn = (string)$manager;
    $m->action = "load";
    $m->page = 1;
    $m->last = 2;
    $all = $m->deliver();
    //println($all->total);
    $total = $all->total;
    unset($all);

    for ($cycle = 1; $cycle <= ceil($total/500); $cycle++) {
        println("$cycle $entity ($total)");
        //continue;
        $m = new Message();
        $m->urn = (string)$manager;
        $m->action = "load";
        $m->page = $cycle;
        $m->last = 500;
        $m->offset = 500 * ($cycle - 1);
        $ds = $m->deliver();
        foreach ($ds as $img)
        {
            try
            {
                $m = new Message();
                $m->urn = $img->urn;
                $m->action = 'rebuildthumbnails';
                $m->deliver();
            }
            catch (Exception $e)
            {
                println($e->getMessage(),1,TERM_RED);
            }
        }
    }
}

?>