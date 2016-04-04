<?php

if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

set_time_limit(0);
ob_implicit_flush();

$dir = realpath(__DIR__."/../../BATCH");
$imgsh = Utils::list_batch_images($dir);

//println($imgsh);

foreach ($imgsh as $id => $imgs)
{
    printH("# ".$id);
    foreach ($imgs as $img)
    {

        $fe = Utils::filename_extension($img);

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:product-'.$id;
        $p = $m->deliver();
        if (!count($p))
        {
            println("Не найден товар с номером $id для фото $img", 1, TERM_RED);
            continue;
        }

        $m = new Message();
        $m->action = 'load';
        $m->uri = $fe['name'];
        $m->urn = 'urn:productphoto';
        $m->product = 'urn:product-'.$id;
        $m->last = 1;
        $r = $m->deliver();
        if (!count($r))
        {
            //println($img);
            $m = new Message();
            $m->action = 'create';
            $m->file = $img;
            $m->uri = $fe['name'].'.'.$fe['extension'];
            $m->urn = 'urn:productphoto';
            $m->product = 'urn:product-'.$id;
            try {
                $r = $m->deliver();
                println("OK ".$img, 1, TERM_GREEN);
            }
            catch (Exception $e) {
                println("ERROR: Не могу создать фото ".$img, 1, TERM_RED);
                println($m);
                print $e;
            }
            //println($r);
        }
        else
        {
            println("Photo {$fe['name']} exists",1,TERM_VIOLET);
        }
    }
}

?>