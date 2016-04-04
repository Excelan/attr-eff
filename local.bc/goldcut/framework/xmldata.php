<?php 
class XMLData 
{

	public static function iterateXMLfolders($ns, $onlymanagers=null, $onlyentities=null, $imported_callback_each=null, $imported_callback_before=null, $imported_callback_after=null, $printdebug=false)
	{
		if (!$ns) throw new Exception('iterateXMLfolders without ns');
        if ($printdebug) println('XML IMPORT',1,TERM_YELLOW);
        Broker::disable(); // disable MQ (after.create and other)
		//Log::debug('start', 'xmlimport');
		foreach (Entity::each_managed_entity($onlymanagers, $onlyentities) as $m => $es)
		{
			//if ($m == 'User' || $m == 'Audio' || $m == 'Video' || $m == 'Attach') continue;
			foreach($es as $entity)
			{
                // SKIP entities
				//if (in_array($entity->name, array('online','visits'))) continue;
                // ONLY
				//if (!in_array($entity->name, array('illustration'))) continue;
                $fddir = FIXTURES_DIR .'/'. $ns . '/'. $entity->class .'/'. $entity->name;
				$fullDataDir = realpath($fddir);
				if (file_exists($fullDataDir))
				{
					if ($printdebug) println($entity->name,1,TERM_GREEN);
					if ($imported_callback_before) $imported_callback_before($entity);
					$i=0; // used in gc_collect every N
					if ($handle = opendir($fullDataDir)) 
					{
						while (false !== ($entry = readdir($handle))) 
						{
							if ($entry == '.' || $entry == '..' || strpos($entry,'.')==0) continue;
							$xmlFile = $fullDataDir.'/'.$entry;
                            //$mtime = filemtime($xmlFile);
                            //$timediff = time() - $mtime;
							try
							{
								XMLData::importXMLentity($xmlFile, $imported_callback_each, $printdebug);
							}
							catch (Exception $e)
							{
								println($e->getMessage(), 1, TERM_RED);
							}
                            //unlink($xmlFile);
                            $i++;
							if (($i % 300) === 0) 
							{
								gc_collect_cycles();
								if ($printdebug) print "$i..";
							}
						}
						closedir($handle);
						if ($imported_callback_after) $imported_callback_after($entity);
						gc_collect_cycles();
					}
				}
                else
                {
                    //dprintln("Not found entity import xml dir $fullDataDir ($fddir)", 1, TERM_RED);
                }
			}
		}
        Broker::enable();
	}
	
	public static function importXMLentity($xmlFile, $imported_callback_each=null, $printdebug=false)
	{
		//Log::debug($xmlFile, 'xmlimport');
		$doc = new DOMDocument();
		$doc->load($xmlFile);
		if (!$doc->documentElement) throw new Exception("Error in xml data {$xmlFile}");
		$urn = $doc->documentElement->getAttribute('urn');
		$urn = new URN($urn);
		
		$m = new Message();
		$m->action = 'create';
		$m->urn = $urn->generalize();
		$m->id = $urn->uuid;
		
		$domx = new DOMXPath($doc);
		$entries = $domx->evaluate("//data/statuses/status");
		foreach ($entries as $n) 
		{
			$f = $n->getAttribute('name');
			$v = $n->nodeValue;
			$m->$f = ($v == 'yes') ? true : false;
		}
		$entries = $domx->evaluate("//data/belongsto");
		foreach ($entries as $n) 
		{
			//$f = $n->getAttribute('name');
			$v = $n->nodeValue;
			if (!$v) continue;
			$v = new URN($v);
			$f = $v->entity->name;
			$m->$f = $v;
		}
		$entries = $domx->evaluate("//data/hasone");
		foreach ($entries as $n) 
		{
			//$f = $n->getAttribute('name');
			$v = $n->nodeValue;
			if (!$v) continue;
			$v = new URN($v);
			$f = $v->entity->name;
			$m->$f = $v;
		}
        $entries = $domx->evaluate("//data/useone");
        foreach ($entries as $n)
        {
            //$f = $n->getAttribute('as');
            $v = $n->nodeValue;
            if (!$v) continue;
            $v = new URN($v);
            $f = $v->entity->name;
            $m->$f = $v;
        }
		$entries = $domx->evaluate("//data/field");
		foreach ($entries as $n) 
		{
			$f = $n->getAttribute('name');
			$v = $n->nodeValue;
			//if ($f == 'anons' || $f == 'text') continue;
			$f = str_replace('count_','count',$f); // LEGACY!
			$m->$f = $v;
		}
		try
		{
			if ($printdebug) dprintln($m,1,TERM_GREEN);
			$created = $m->deliver();
			$Class = $urn->entity->getClass();
			if (method_exists($Class,'fromXMLextractor'))
			{
				$Class->fromXMLextractor($doc);
			}
			if ($imported_callback_each) $imported_callback_each($created);
		}
		catch (Exception $e) 
		{
			//println($m);
			if ($printdebug) println($e->getMessage(),1,TERM_RED);
			Log::error($e,'xmlimport');
			//continue;
			return;
		}
		
		
		$entries = $domx->evaluate("//lists/list");
		foreach ($entries as $n) 
		{
			$f = $n->getAttribute('name');
			$cu = $created->urn;
			$cu->set_list($f);
			foreach ($n->childNodes as $li)
			{
				if ($li->nodeType == XML_TEXT_NODE) continue; 
				$liurn = $li->nodeValue;
				$m = new Message();
				$m->action = 'add';
				$m->urn = $liurn;
				$m->to = (string) $cu;
				try
				{
					if ($printdebug) dprintln($m,1,TERM_VIOLET);
					$m->deliver();
				}
				catch (Exception $e) 
				{
					if ($printdebug) println($e->getMessage(),1,TERM_RED);
					Log::error($e,'xmlimport');
				}
			}
		}

        $entries = $domx->evaluate("//translations/translation");
        foreach ($entries as $n)
        {
            $lang = $n->getAttribute('lang');

            $m = new Message();
            $m->action = 'translate';
            $m->lang = $lang;
            $m->urn = $created->urn;

            foreach ($n->childNodes as $li)
            {
                if ($li->nodeType == XML_TEXT_NODE) continue;
                $fname = $li->getAttribute('name');
                $fdata = $li->nodeValue;
                $m->$fname = $fdata;
            }

            try
            {
	            if ($printdebug) dprintln($m,1,TERM_VIOLET);
                $m->deliver();
            }
            catch (Exception $e)
            {
	            if ($printdebug) println($e->getMessage(),1,TERM_RED);
	            Log::error($e,'xmlimport');
            }
        }

        // LEGACY
		$entries = $domx->evaluate("//related/rel");
		foreach ($entries as $n) 
		{
			$f = $n->getAttribute('name');
			$cu = $created->urn;
			$cu->set_list($f);
			foreach ($n->childNodes as $li)
			{
				if ($li->nodeType == XML_TEXT_NODE) continue; 
				$liurn = $li->nodeValue;
				$m = new Message();
				$m->action = 'add';
				$m->urn = $liurn;
				$m->to = (string) $cu;
				try
				{
					//println($m,1,TERM_VIOLET);
					$m->deliver();
				}
				catch (Exception $e) 
				{
					if ($printdebug) println($e->getMessage(),1,TERM_RED);
				}
			}
		}
	}
	
}
?>