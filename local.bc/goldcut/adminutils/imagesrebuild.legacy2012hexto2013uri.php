<?php 
/**
media/illustration/4A43E6F3_1.jpg	>> media/illustration/Lyjnyy-zaychik.jpg
REMOVE OLD FILES!

USE old target files as source if no originals

TODO!!! 
	remove /preview on remove originals
	remove db file, photo objects
*/

echo "<p>Watch for nginx to apache timeout!</p><p>Use \"options\" => array('filenaming' => 'original'\") to use file names as uri + new filename</p><p>Note: on next rebuild f:folder will be non empty, so old urls *_0.ext will be lost</p><p>Use option urichangeonrebuild:yes for migration from uri naming to uuids</p><p>Note: :watermark is a new name of old :stamp option</p>";

$all = Utils::list_dir_images(BASE_DIR.'/original',true);
$used = array();
println('Total image originals in /original folder: '.count($all));
//println($all);

if (!count($all)) throw new Exception('No jpg files in /original folder');

rename(BASE_DIR.'/thumb',BASE_DIR.'/thumb'.time());
rename(BASE_DIR.'/media',BASE_DIR.'/media'.time());

$em = Entity::each_managed_entity('Photo');
foreach($em['Photo'] as $manager)
{
	if ($manager->name == 'photo') continue;
	printH($manager->name);
	$m = new Message();
	$m->urn = (string) $manager;
	$m->action = 'load';
	$ds = $m->deliver();
	
	foreach ($ds as $img)
	{ 
		//println($img);
		if ($img->folder) // folder будет установлен после первого же прохода
		{
			$legacyUri = $img->image->uri;
		}
		else
		{
			$ext = ($img->ext) ? $img->ext : 'jpg';
			if (MEDIASERVERS > 0)
				$legacyUri = "/media/{$manager->name}/{$img->uri}." . $ext;
			else
				$legacyUri = "/media/{$manager->name}/{$img->id}_0." . $ext;
		}
		println('was '.$legacyUri,1,TERM_GREY);
		$m = new Message();
		$m->action = 'rebuild';
		$m->urn = $img->urn;
		$rebuilded = null;
		try
		{
			$rebuilded = $m->deliver();
		}
		catch (Exception $e)
		{
			println($e->getMessage(),1,TERM_RED);
		}
		//println($rebuilded,1,TERM_VIOLET);
		if ($rebuilded->original) array_push($used, $rebuilded->original);
		println('now '.$img->urn->resolve()->image->uri,2,TERM_GREEN);
	}
}

println($used);
println('Total image originals used: '.count($used));

$notused = array_diff($all, $used);
println('Total image originals NOT used: '.count($notused));
//println($notused);
foreach ($notused as $unused)
{
	//println($unused,1,TERM_RED);
	unlink($unused);
}
println("ALL UNSED ORIGINALS DELETED",1,TERM_RED);
?>