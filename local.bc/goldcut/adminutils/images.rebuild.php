<?php

if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

set_time_limit(0);
ob_implicit_flush();

gc_enable();

echo "<p>Watch for nginx to apache timeout!</p><p>Use \"options\" => array('filenaming' => 'original'\") to use file names as uri + new filename</p><p>Note: on next rebuild f:folder will be non empty, so old urls *_0.ext will be lost</p><p>Use option urichangeonrebuild:yes for migration from uri naming to uuids</p><p>Note: :watermark is a new name of old :stamp option</p>";

/*
$all = Utils::list_dir_images(BASE_DIR.'/original',true);
$used = array();
println('Total image originals in /original folder: '.count($all));
//println($all);
if (!count($all)) throw new Exception('No jpg files in /original folder');
*/

$em = Entity::each_managed_entity('Photo');
foreach($em['Photo'] as $manager)
{
    if ($manager->name == 'photo') continue;
    printH($manager->name);
    $m = new Message();
    $m->urn = (string) $manager;
    $m->action = 'load';
    //$m->last = 5;
    $ds = $m->deliver();

    foreach ($ds as $img)
    {
        gc_collect_cycles();

        try
        {
            //println($img);

            $maxsizeasoriginal = BASE_DIR.''.$img->image->uri;
            $original = BASE_DIR.'/original/'.$img->urn->uuid.'.'.$img->ext;

            $md = new Message();
            $md->action = 'delete';
            $md->urn = $img->urn;


            //continue;

            $m = new Message();
            $m->action = 'create';
            $m->urn = $img->urn->generalize();
            $m->id = $img->urn->uuid;
            $m->uri = $img->uri.'.'.$img->ext;
            if (filesize($original))
                $m->file = $original;
            elseif (filesize($maxsizeasoriginal))
                $m->file = $maxsizeasoriginal;
            else
                throw new Exception("No original and no image for reuse");
            $rebuilded = null;

            foreach ($img->urn->entity->belongs_to() as $hostEntity)
            {
                //println($hostEntity,2,TERM_VIOLET);
                $dm = $hostEntity->name;
                $dk = $hostEntity->name.'_id';
                $hostUUID = $img->$dk;
                $reassignhosturn = "urn-{$dm}-{$hostUUID}";
                //println($hostUUID,2,TERM_GREEN);
                if ($hostUUID) $m->$dm = $reassignhosturn;
            }

            try
            {
                $md->deliver();
                //println($m);
                $rebuilded = $m->deliver();
            }
            catch (Exception $e)
            {
                println($e->getMessage(),1,TERM_RED);
            }
            //println($rebuilded,1,TERM_VIOLET);

            //if ($rebuilded->original) array_push($used, $rebuilded->original);
            $imgnew = $img->urn->resolve();
            $uri = $imgnew->image->uri;
            $thumb = $imgnew->thumbnail["data"];
            println("<img src='$thumb'>$uri from {$m->file} {$imgnew->width} x {$imgnew->height}",2,TERM_GREEN);
        }
        catch (Exception $e)
        {
            println($e->getMessage(),1,TERM_RED);
        }
    }
}

/*
println($used);
println('Total image originals used: '.count($used));

$notused = array_diff($all, $used);
println('Total image originals NOT used: '.count($notused));
*/
//println($notused);
/**
foreach ($notused as $unused)
{
    //println($unused,1,TERM_RED);
    unlink($unused);
}
println("ALL UNSED ORIGINALS DELETED",1,TERM_RED);
*/
?>