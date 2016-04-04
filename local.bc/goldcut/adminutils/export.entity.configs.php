<?php

gc_enable();

function showAllRelsLists()
{
	foreach (Entity::each_managed_entity($filter) as $m => $es)
	{
		foreach($es as $E)
		{
			$rels = array();
			$lists = array();
			foreach ($E->related() as $rel)
			{
				$v = $rel->name;
				$rels[]= $v;
			}
			foreach ($E->lists() as $list)
			{
				$rel = $list['entity'];
				$listns = $list['ns'];
				$listname = $list['name'];
				$lists[]= $listname;
			}
			if (count($rels)) println("$E RELS ".join(', ',$rels), 1, TERM_RED);
			if (count($lists)) println("$E LISTS ".join(', ',$lists), 1, TERM_GREEN);
		}
	}
}

function exportConfs()
{
	$confdir = BASE_DIR.'/config/entity';
	foreach (Entity::each_managed_entity($filter) as $m => $es)
	{
        printH($m);
		//if (in_array($m->name, array('file','user','online'))) continue; // skip system
		foreach($es as $E)
		{
			if (method_exists($E, 'is_system') && $E->is_system()) {
				println($E, 1, TERM_GRAY);
				if ($E->overlayed) {
					$xml = $E->toXMLConfig();
					println('SYSTEM ENTITY WITH OVERLAY - NOT SAVED', 1, TERM_GRAY);
					println(htmlentities($xml));
				}
				continue;
			}
            else
			    println($E,1,TERM_GREEN);
			if ($_GET['notblankonly'])
			{
				$m = new Message();
				$m->action = 'load';
				$m->urn = (string)$E;
				$m->last = 1;
				$data = $m->deliver();
				if (!count($data)) println("Warning: no data in $E", 1, TERM_YELLOW);
				continue; // skip entity config for no data in table
			}
			$xml = $E->toXMLConfig();
			println(htmlentities($xml));
			$filename = $E->name.'.xml';
            if (method_exists($E, 'is_system') && $E->is_system()) $filename = '_'.$filename;
			$fullConfDir = $confdir .'/'. strtolower($E->class);
			if (!file_exists($fullConfDir)) mkdir($fullConfDir);
			println($fullConfDir .'/'. $filename,2,TERM_GRAY);
			save_data_as_file($fullConfDir .'/'. $filename, $xml);
		}
	}
}

println("All overlays will be written over enities!!!");

showAllRelsLists();
exportConfs();

?>