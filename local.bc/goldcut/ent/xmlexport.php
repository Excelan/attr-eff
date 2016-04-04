<?php

class XMLExport
{
	public static function exportData($snapshotNS=null, $printDebug=false)
	{
		$filter = null;
		//$dir = BASE_DIR.'/importexport';
		$dir = FIXTURES_DIR;
		if ($snapshotNS) $dir .= '/'.$snapshotNS;
		rename($dir, $dir.'_saved');
		if (!file_exists($dir)) mkdir($dir, 0700);

		foreach (Entity::each_managed_entity($filter) as $m => $es)
		{
			foreach($es as $entity)
			{
				/*
				if ($entity->is_system())
				{
					println("Skip system {$entity->name}",1,TERM_GRAY);
					continue;
				}
				*/
				//if (!in_array($entity->name, array('someentity'))) continue; // export only one BIG table
				//if (in_array($entity->name, array('online','visit'))) continue; // skip system tables full stat data
				//if (in_array($entity->name, array('user'))) continue; // skip user - proc in specialized exporter

				$m = new Message();
				$m->urn = (string)$entity;
				$m->action = "load";
				$m->page = 1;
				$m->last = 2;
				$all = $m->deliver();
				//println($all->total);
				$total = $all->total;
				unset($all);

				for ($cycle = 1; $cycle <= ceil($total/300); $cycle++)
				{
					if ($printDebug) printH("Entity: $entity Cycle: $cycle Total: $total");
					//if ($cycle > 1) break;
					$m = new Message();
					$m->urn = (string)$entity;
					$m->action = "load";
					if ($entity->is_multy_lang()) $m->lang = '*';
					$m->page = $cycle;
					$m->last = 300;
					$m->offset = 300 * ($cycle - 1);

					try {
						$data = $m->deliver();
						//if (!count($data)) break;
					} catch (Exception $e) {
						if ($printDebug) println($e->getMessage(), 1, TERM_RED);
						continue;
					}
					$datacount = count($data);
					//println("$entity ($datacount)", 1, TERM_GREEN);
					if (!$datacount) continue;

	                if ($datacount > 10000) {
	                    Log::info("IT CAN TAKE TIME (> 10K rows)", 'xmlexport');
	                }
					$i = 0;
					foreach ($data as $c) {
						$i++;
						//if ($i > 1) break;
						//println($c);
						$xml = $c->toXML();
						$filename = $c->urn . '.xml';
						//println(htmlentities($xml));
						$fullDataDir = $dir . '/' . $entity->class . '/' . $entity->name;
						//println($fullDataDir);
						if (!file_exists($fullDataDir)) mkdir($fullDataDir, 0700, true);
						save_data_as_file($fullDataDir . '/' . $filename, $xml);
						if (($i % 2000) === 0) gc_collect_cycles();
						if ($printDebug) { if (($i % 100) === 0) print($i.'..'); }
					}
					gc_collect_cycles();
				}
			}
		}
	}
}

?>