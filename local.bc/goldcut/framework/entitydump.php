<?php

class EntityDump
{
	private static function plv8FunctionTemplate($fnName, $returnJson)
	{
		$plv8FunctionTemplate = 'CREATE OR REPLACE FUNCTION '.$fnName.'() RETURNS json AS $$ return '.$returnJson.' $$ LANGUAGE plv8 IMMUTABLE STRICT';
		return $plv8FunctionTemplate;
	}

	static function getPLV8Config()
	{
		// TODO check $to_sqltype = array("option"=>"smallint","set"=>"smallint","sequence"=>"integer[]","table"=>"JSON","xml"=>"XML","json"=>"JSON","status"=>"smallint","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"TEXT", "integer"=>"INT", "float"=>"REAL","date"=>"DATE","timestamp"=>"INT", "money"=>"NUMERIC(14,2)","ipv4"=>"inet","iarray"=>"integer[]");
		// TODO http://www.postgresql.org/docs/9.1/static/datatype-enum.html
		$to_sqltype = array("sequence"=>"integer[]","iarray"=>"integer[]","tarray"=>"text[]","table"=>"JSON","xml"=>"XML","json"=>"JSON","set"=>"SMALLINT","status"=>"INT","image"=>"text","string"=>"VARCHAR(255)","text"=>"TEXT","richtext"=>"TEXT", "integer"=>"INT", "float"=>"REAL","date"=>"DATE","timestamp"=>"INT", "option"=>"INT", "money"=>"NUMERIC(14,2)","ipv4"=>"inet");

		$confAll = array();
		foreach (Entity::each_entity() as $e)
		{
			$conf = array();
			foreach ($e->statuses as $statusid)
			{
				$s = Status::ref($statusid);
				$conf['status'][] = array('name' => $s->name, 'default' => $s->default ? 1 : 0);
			}

			foreach ($e->fields() as $F)
				$conf['fields'][] = array('name' => $F->name, 'type' => $to_sqltype[$F->type], 'default' => $F->sqldefault(), 'lazy' => $F->islazy());

			foreach ($e->belongs_to() as $E)
				$conf['useone'][] = array('entity' => $E->name, 'alias' => $E->name);
			foreach ($e->use_one() as $alias => $E)
				$conf['useone'][] = array('entity' => $E->name, 'alias' => $alias);
			foreach ($e->has_one() as $alias => $E)
				$conf['useone'][] = array('entity' => $E->name, 'alias' => $alias);

			foreach ($e->has_many() as $E)
				$conf['usemany'][] = array('entity' => $E->name);

			foreach ($e->lists() as $list)
			{
				$E = $list['entity'];
				$listname = $list['name'];
				$conf['lists'][] = array('entity' => $E->name, 'alias' => $listname, 'reverse' => false);
			}
			$confAll[$e->name] = $conf;
		}
		return $confAll;
	}

	static function plv8upload()
	{
		$dblink = DBPGSQL::link();

		$confAll = self::getPLV8Config();
		foreach ($confAll as $entityName => $conf)
		{
			$fn = "entity_config_{$entityName}";
			$json = json_encode($conf);//'{fields: ["title","uri","price","x"], "hasone": ["photo"]}';

			try
			{
				$dblink->raw_query("DROP FUNCTION {$fn}()");
			}
			catch (Exception $e)
			{
				println($e,1,TERM_RED);
			}

			try
			{
				$FnBody = self::plv8FunctionTemplate($fn, $json);
				println($FnBody,1,TERM_GRAY);
				$dblink->raw_query($FnBody);
			}
			catch (Exception $e)
			{
				println($e,1,TERM_RED);
			}

			try
			{
				$q = "SELECT entity_config_{$entityName}()";
				$r = $dblink->queryJson($q);
				println($r,1,TERM_GREEN);
			}
			catch (Exception $e)
			{
				println($e,1,TERM_RED);
			}
		}





	}


	static function report()
	{
		$usage = array();
		foreach (Entity::each_managed_entity(null, null) as $m => $es)
		{
			foreach($es as $e)
			{
				//if (($e->is_system() && !$_GET['withsystem']) xor $e->overlayed) continue;
				foreach ($e->belongs_to() as $er)
				{
					if (!is_array($usage[$er->name])) $usage[$er->name] = array();
					if (!in_array($e->name, $usage[$er->name])) array_push($usage[$er->name], $e->name.'(@)');
				}
				foreach ($e->has_many() as $er)
				{
					if (!is_array($usage[$er->name])) $usage[$er->name] = array();
					if (!in_array($e->name, $usage[$er->name])) array_push($usage[$er->name], $e->name.'(>>)');
				}
				foreach ($e->lists() as $list)
				{
					$rel = $list['entity'];
					$listname = $list['name'];
					$er = $rel;
					if (!is_array($usage[$er->name])) $usage[$er->name] = array();
					if (!in_array($e->name, $usage[$er->name])) array_push($usage[$er->name], $e->name."(L/{$listname})");
				}
				foreach ($e->has_one() as $usedas => $er)
				{
					if (!is_array($usage[$er->name])) $usage[$er->name] = array();
					$sufx = '(.1)';
					if ($usedas != $er->name) $sufx = "(.1/{$usedas})";
					if (!in_array($e->name, $usage[$er->name])) array_push($usage[$er->name], $e->name.$sufx);
				}
				foreach ($e->use_one() as $usedas => $er)
				{
					if (!is_array($usage[$er->name])) $usage[$er->name] = array();
					$sufx = '(~1)';
					if ($usedas != $er->name) $sufx = "(~1/{$usedas})"; 
					if (!in_array($e->name, $usage[$er->name])) array_push($usage[$er->name], $e->name.$sufx);
				}
			}
		}
		
		foreach (Entity::each_managed_entity(null, null) as $m => $es)
		{
			$ess = array();
			foreach($es as $e)
			{
				//if ($e->is_system() && $e->name != 'user') continue;
				$ess[] = $e;
			}
			if (!count($ess)) continue;
			
			printH($m.' '.count($ess));
			foreach($ess as $e)
			{
				$status = '';
				$statuses = null;
				$cby = null;
				$fields = null;
				$bt = null;
				$bt_list = null;
				$ho = null;
				$ho_list = null;
				$hm = null;
				$hm_list = null;
				$re = null;
				$re_list = null;
	
				foreach ($e->statuses as $statusid)
					$statuses[] = Status::ref($statusid)->name . " - " . Status::ref($statusid)->title;
				if (count($statuses)>0)
					$status = "+-[" . join(', ', $statuses) . "]";
	
				foreach ($e->general_fields() as $F)
					$fields[] = $F->name . " - " . $F->type; // . '#'. $F->uid
				if (count($fields)>0)
					$fields_list = join(', ', $fields);
	
				foreach ($e->lang_fields() as $F)
					$fields[] = $F->name . " - " . $F->type; // . '#'. $F->uid
				if (count($fields)>0)
					$fields_list .= join(', ', $fields);
	
	
				foreach ($e->belongs_to() as $usedas => $EBT)
					$bt[] = $EBT->name . ' as ' . $usedas . " - " . $EBT->title['ru'];
				if (count($bt)>0)
					$bt_list = "@ " . join(', ', $bt);
	
				foreach ($e->has_many() as $usedas => $EHM)
					$hm[] = $EHM->name . ' as ' . $usedas . " - " . $EHM->title['ru'];
				if (count($hm)>0)
					$hm_list = ">> " . join(', ', $hm);
				/*
				foreach ($e->related() as $RE)
					$re[] = $RE->name . " - " . $RE->title['ru']; //  . ' #' . $RE->uid
				if (count($re)>0)
					$re_list = "~~ " . join(', ', $re);
				*/
				foreach ($e->lists() as $list)
				{
					$rel = $list['entity'];
					$listname = $list['name'];
					if ($listname != $rel->name)
						$re[] = $listname . '(' . $rel->name .')'; // . " - " . $rel->title['ru'];
					else
						$re[] = $rel->name;
					$re_list = "LIST " . join(', ', $re);
				}
				
				foreach ($e->has_one() as $usedas => $EHO)
				{
					if ($usedas != $EHO->name) $usedas = $EHO->name . ' as ' . $usedas;
					$ho[] = $usedas . " - " . $EHO->title['ru'];
				}
				
				foreach ($e->use_one() as $usedas => $EHO)
				{
					if ($usedas != $EHO->name) $usedas = $EHO->name . ' as ' . $usedas;
					$ho[] = $usedas . " - " . $EHO->title['ru'];
				}
				
				if (count($ho)>0)
					$ho_list = "1 " . join(', ', $ho);

                if ($e->imagesettings)
                {
                    $moa = "IMAGE: {$e->imagesettings['mainimage']['paradigm']} {$e->imagesettings['mainimage']['size']['dim']} {$e->imagesettings['mainimage']['size']['size']}. ";
                    if ($e->imagesettings['previews']) $moa .= "Previews: {$e->imagesettings['previews']['paradigm']}";
                    $psa = array();
                    foreach ($e->imagesettings['previews']['sizes'] as $psize)
                    {
                        $psp = $psize['dim'];
                        $pss = $psize['size'];
                        $psa[]= $pss;
                    }
                    $moa .= ' '. $psp . ' ' . join(', ',$psa);
                }
                else $moa = '';

                //var_dump($e->multy_lang);
                $langs = "";
                if ($e->is_multy_lang()) $langs = " [Переводы: ".join(', ', $e->lang_codes())."] ";

				$code = '';
				if ($e->code) $code = 'code:'.$e->code;

				println(strtoupper($e)." - {$e->title['ru']}{$langs} {$moa} {$code}", 1); //  - {$e->class} // #{$e->uid}
	
				if (count($usage[$e->name])) 
				{
					sort($usage[$e->name]);
					println("used in: ".join(', ',$usage[$e->name]),1,TERM_GRAY);
				}
				
				print "\t";
				$machine = array();
				//$i=0;
				foreach ($e->general_fields() as $F)
				{
					//$i++;
					$sfx = "";
					//if ($i == 1) $sfx = "\t";
					$machine []= "'".$F->name."'";
					printColor($sfx.$F->name,TERM_VIOLET);
					print ":";
					printColor(substr($F->type,0,1),TERM_BLUE);
					print ''. ($F->usereditable ? "E" : '');
					print ", ";
				}
				//print "[".join(',',$machine)."]";
	
				if ($e->is_multy_lang())
					print "MULTYLANG ";
	
				foreach ($e->lang_fields() as $F)
				{
					printColor($F->name,TERM_VIOLET);
					print ": ";
					printColor($F->type,TERM_BLUE);
					//print '#'. $F->uid;
					print ", ";
				}
				
				if ($cby)
					println($cby,2,TERM_GREEN);
				if (count($statuses)>0)
					println($status,2,TERM_BLUE);
				if (count($bt)>0)
					println("{$bt_list}",2,TERM_GREEN);
				if (count($ho)>0)
					println("{$ho_list}",2,TERM_GREEN);
				if (count($hm)>0)
					println("{$hm_list}",2,TERM_YELLOW);
				if (count($re)>0)
					println("{$re_list}",2,TERM_YELLOW);
				
	
				//printLine();
				print "\n";
	
			}
	}
	}

}
?>