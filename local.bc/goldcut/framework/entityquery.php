<?php
/**
TODO !USE PAGE = N INSTEAD OF LIMIT 100 offset 200 to preload count total
$m->category = null; - любая категория *
$m->category = false; - только без присвоенной категории (sql is null)
*/
class EntityQuery
{
	public static function query($query)
	{
		//if ($query->urn == 'urn-')
		//dprintln($query,1,TERM_GRAY);
		$load_urn = $query->urn;
		$e = $load_urn->entitymeta();
		//printlnd($load_urn);
		//printlnd($e);
		$E = $load_urn->entitymeta;//Entity::ref($e);
		$estore = new EntityDB_select($E);

		if ($uuid = $load_urn->uuid())
		{
			$query->merge(array('id' => $uuid->toInt()));
		}

		$search = array();
		$searchOR = array();

		if ($E->is_multy_lang())
		{
			if (!isset($query->lang))
            {
                $query->lang = 'ru';//SystemLocale::default_lang();
                $langFilter = array($query->lang => 1);
            }
            else
            {
                $lp = explode('-', $query->lang);
                if (count($lp) > 1)
                {
                    if ($lp[0] == 'nt')
                    {
                        $query->lang = SystemLocale::default_lang();
                        $langFilter = array($lp[1] => 0);
                    }
                }
                else
                    $langFilter = array($query->lang => 1);
            }
            // разделеяем выборку полей на определенном языке и фильтр записей имеющий или нет определенный перевод
            // при мультиязыковой выборке загружаем все языковые вариации каждой записи и уже на уровне datarow формируем general поля и текущий язык этой строки
            // $lang = array('ru, en, ua') // любые перечисленные языки
            // $lang = 'ex-en'; // любые языки, кроме английского (default и все переводы)
            // $lang = 'nt-en'; // еще не переведен на англ, на default языке
		}
		else
		{
			$query->lang = false;
            $langFilter = false;
		}
        //println($query->lang,1,TERM_VIOLET);
        //println($langFilter,1,TERM_VIOLET);

		foreach ($E->belongs_to() as $usedas => $F) $bt[] = $usedas;
		//foreach ($E->related() as $F) $rel[] = $F->name;
		$listrelname = array();
		foreach ($E->lists() as $L)
		{
			$listnames[] = $L['name']; //$em[] = array( 'entity'=> $listentity, 'ns'=> $list['ns'], 'name'=> $list['name'], 'title'=> $list['title'] );
			$lists[$L['name']] = $L;
			//$rele[$el['entity']] = Entity::ref($L['entity']);
			$listrelname[] = $L['entity']->name;
		}
		foreach ($E->has_one() as $usedas => $F) $ho[] = $usedas;
		foreach ($E->use_one() as $usedas => $F) $uo[] = $usedas;
		/**
		RATED (by num of comments, views, voutes)
		$q = "SELECT {$query['e']}_id, COUNT(id) AS sum_comments FROM comment GROUP BY {$query['e']}_id HAVING sum_comments > 1 ORDER BY sum_comments DESC LIMIT {$query['last']}";
		*/

		/**
		ONE ID or IN [IDS..]
		*/
		if ($query->exists('id') && $query->id)
		{
			if ( is_numeric($query->id) ) // add more security checks
			{
				$query->id = (int) $query->id;
				$cacheOne = true;
			}
			else
			{
				// array ids in queries from datarow->preload
				// $query->id = (array) $query->id->get(); // $query->id is Message
				// now its delegated to entitydb_select where message casted to array of ids
			}
			$ids = $query->id;
		}

		if ($query->exists('in') && $query->in)
		{
			//printlnd($query->in);
			if (!$query->in->count())
				$ids = -1;
			else
				$ids = $query->in->ids;
		}

		/**
		BY BELONGS TO. QUERY BY PARENT ID - если в запросе есть поля являющиеся belongs_to
		*/
		else if (is_array($bt) && $belongs_to = array_intersect($query->keys(), $bt))
		{
			$btindex = array_values($belongs_to);
			foreach ($btindex as $btcur)
			{
				if ($query->$btcur instanceof URN)
				{
					$btuuid = $query->$btcur->uuid()->toInt();
					$urnGeneral = (string)$query->$btcur->entity->urn;
					if ($query->includeinner && in_array($urnGeneral, $query->includeinner->toArray()))
					{
						$btuuid = Entity::childIds($urnGeneral, $query->$btcur->uuid->toInt());
					}
				}
				else if ($query->$btcur instanceof Message)
				{
					// TODO includeinner realize
					$btar = $query->$btcur->toArray();
					$btuuid = $btar;
				}
				else
				{
					if ($query->$btcur === false or $query->$btcur === 'NULL')
						$btuuid = 'NULL';
					else
						$btuuid = $query->$btcur;
				}
				if ($btuuid)
				{
					$byParent[] = array($btcur => $btuuid);
				}
			}
		}


		/**
		by Statuses
		*/
		foreach ($E->statuses as $statusid)
		{

			$status = Status::ref($statusid)->name;
			if (isset($query->$status))
			{
				if ($query->$status === true)
					$query->$status = 1;
				else
					$query->$status = (int) $query->$status;
				if ($query->$status == -1)
					$query->$status = 0;

				if ($query->$status === 1) $search[] = array('field' => $status, 'value' => 1);
				if ($query->$status === 0) $search[] = array('field' => $status, 'value' => 0);
				//else $search[] = array('field' => $status, 'value' => 0);
			}
			else  // TOD DEF STATUSES
			{
				// значение статуса по умолчанию из конфига
				//println($status);
				//println(Status::ref($statusid)->default);
				//$search[] = array('field' => $status, 'value' => Status::ref($statusid)->default);
			}
		}

		/**
		BY HAS_ONE ids in conditions
		*/
		foreach ($E->has_one() as $usedas => $F)
		{
			$v = $usedas;
			if (isset($query->$v))
			{
				if ($query->$v instanceof URN)
					$houuid = (integer) $query->$v->uuid()->toInt();
				elseif(is_numeric($query->$v))
					$houuid = $query->$v; // (integer)
                else
                    continue;
				$search[] = array('field' => "{$usedas}", 'value' => $houuid);
			}
		}

		foreach ($E->use_one() as $usedas => $F)
		{
			//println("$usedas => $F",1,TERM_VIOLET);
			$v = $usedas;
			if (isset($query->$v))
			{
				if ($query->$v instanceof URN)
				{
					if ($query->$v->is_general()) throw new Exception("$query->$v for $v is general urn");
					$houuid = (integer)$query->$v->uuid()->toInt();
				}
        elseif(is_numeric($query->$v))
          $houuid = $query->$v; // (integer)
        else
          continue;
				$search[] = array('field' => "{$usedas}", 'value' => $houuid);
				//printlnd($search,1,TERM_VIOLET);
			}
			//else printlnd("NO IN Q ".$query,1,TERM_VIOLET);
		}


		/**
		OR
		*/
		if ($query->exists('or'))
		{
			foreach ($query->or->toArray() as $f => $v)
				$searchOR[] = "$f = '$v'";
		}

		/**
		BY CONDITIONS GENERAL
		*/
		foreach($E->general_fields() as $fname => $F)
		{
			if ($query->exists($fname)) // was && $query->$fname
			{
				// DATES
				if ($F->type == 'date')
				{
					$search[] = array('field' => $fname, 'value' => $query->$fname);
				}
				// TIMESTAMPS
				// (from, to), (date => ymd), ('greater/after', time)
				else if ($F->type == 'timestamp')
				{
					if (!is_numeric($query->$fname))
					{
						$a = $query->$fname->toArray();
						// printlnd($query->$fname);
						//printlnd($a);
						if ($query->$fname->date)
						{
							$datestart = strtotime($query->$fname->date);
							$query->$fname = array($datestart, $datestart + 86400 - 1);
						}
						else if (count($a) == 2 && (is_string($a[0]) && is_string($a[1])))
						{
							// printlnd($btw);
							$query->$fname = array(strtotime($a[0]), strtotime($a[1]));
						}
						$search[] = array('field' => $fname, 'value' => $query->$fname);
					}
					else
						$search[] = array('field' => $fname, 'value' => $query->$fname);
				}
				// string or int field
				else
				{
					/*
					if (is_array($query->$fname) or $query->$fname instanceof Message)
					else
					*/
					$search[] = array('field' => $fname, 'value' => $query->$fname);
				}
			}
		}
		/**
		BY LANG GENERAL FIELDS
		*/
		foreach($E->lang_fields() as $fname => $F)
		{
			if ( $E->is_multy_lang() && $query->lang != SystemLocale::default_lang() )
			{
				$sfx = '_'.$query->lang;
			}
			else
			{
				$sfx = '';
			}
			if ($query->exists($fname) && $query->$fname)
			{
				$search[] = array('field' => "{$fname}{$sfx}", 'value' => $query->$fname);
			}
		}

		/**
		LIKE search
		TODO SOLR client
		TODO search first then filter ids from listdb filters
		TODO search in field:only. "title: some" > "->searchonly = array('title')"
		TODO universal virtual fields with real f mappings - title, intro, text, metadata
		*/
		if ($query->exists('search') && $query->search)
		{
			//if (!$query->exists('order')) $query->order = 'date DESC, time DESC';
			if (strpos($query->search,'~')===0)
			{
				$exactsearch = false;
				$query->search = substr($query->search, 1);
			}
			else
			{
				$exactsearch = true;
			}

			$query->search = Security::mysql_escape($query->search);

			if (!$exactsearch)
			{
				$words = explode(' ', $query->search);
				$stemmer = new Stemming();
				$stemmedwords = array();
				foreach ($words as $word)
				{
					$stemmed = $stemmer->stem($word);
					$stemmedwords[] = $stemmed;
				}
			}

			foreach ($E->belongs_to() as $usedas => $BT)
			{
				if ($E->adminsearchtextin && !in_array($usedas, $E->adminsearchtextin)) continue;
				if (is_numeric($query->search) && $query->search < PHP_INT_MAX) $searchOR[] = "{$E->name}.{$usedas} = {$query->search}";
			}
            foreach ($E->use_one() as $usedas => $F)
            {
                if ($E->adminsearchtextin && !in_array($usedas, $E->adminsearchtextin)) continue;
                if (is_numeric($query->search) && $query->search < PHP_INT_MAX) $searchOR[] = "{$E->name}.{$usedas} = {$query->search}";
            }
            foreach ($E->has_one() as $usedas => $F)
            {
                if ($E->adminsearchtextin && !in_array($usedas, $E->adminsearchtextin)) continue;
                if (is_numeric($query->search) && $query->search < PHP_INT_MAX) $searchOR[] = "{$E->name}.{$usedas} = {$query->search}";
            }

			foreach($E->lang_fields() as $fname => $F)
			{
				if ($query->exists('scope')) // scope = admin
				{
					if ($E->adminsearchtextin && !in_array($F->name, $E->adminsearchtextin)) continue;
					if (!$E->adminsearchtextin && $E->searchtextin && !in_array($F->name, $E->searchtextin)) continue;
				}
				else
				{
					if ($E->searchtextin && !in_array($F->name, $E->searchtextin)) continue;
				}
				if ( SystemLocale::default_lang() != $query->lang)
				{
					// TODO ADD SERCH BY STEMMED WORDS
					$searchOR[] = "{$fname}_{$query->lang} LIKE '%{$query->search}%'";
				}
				else
				{
					// TODO ADD SERCH BY STEMMED WORDS
                    if (in_array($F->type,array('string','text','richtext'))) $searchOR[] = "{$fname} LIKE '%{$query->search}%'";
				}
			}

			foreach($E->general_fields() as $fname => $F)
			{
                //Log::info($F->name.'--','sql');
                //Log::info($F->type.'--','sql');

                if ($F->type != 'string' and $F->type != 'text' and $F->type != 'richtext' and $F->type != 'json' and $F->type != 'iarray') continue;

				if ($query->exists('scope')) // scope = admin
				{
					if ($E->adminsearchtextin && !in_array($F->name, $E->adminsearchtextin)) continue;
					if (!$E->adminsearchtextin && $E->searchtextin && !in_array($F->name, $E->searchtextin)) continue;
				}
				else
				{
					if ($E->searchtextin && !in_array($F->name, $E->searchtextin)) continue;
				}

				if (!$exactsearch)
				{
					$fr = array();
					foreach ($stemmedwords as $sw)
					{
						$fr[] = "{$fname} LIKE '%{$sw}%'";
					}
					$searchOR[] = "( ".join(' AND ', $fr)." )";
				}
				else
				{
                    if (in_array($F->type,array('string','text','richtext')))
                    {
                        $searchOR[] = "{$fname} LIKE '%{$query->search}%'";
//                        Log::info($F->name,'sql');
//                        Log::info($F->type,'sql');
//                        Log::info(json_encode($searchOR),'sql');
                    }
					elseif ($F->type == 'json')
					{
						$searchOR[] = "{$fname}::text LIKE '%{$query->search}%'"; // normal ::text
					}
					elseif ($F->type == 'iarray' && is_numeric($query->search))
					{
						$searchOR[] = "ARRAY[{$query->search}] <@ {$fname}";
					}
					elseif ($F->type == 'tarray')
					{
						$searchOR[] = "ARRAY[{$query->search}] <@ {$fname}";
					}
                    //else Log::info($F->name."NO",'sql');
				}
			}
            //if (is_numeric($query->search)) $searchOR[] = "id LIKE '%{$query->search}%'";
            if (is_numeric($query->search)) $searchOR[] = "id = {$query->search}";
		}

		/**
		NOT (FILTER, logical substruct) - TODO not is a Message like a general load but it works like select A -minus result of select Not
		in ajax admin we can not go for sql if we have all results on one page - so we can filter it locally by js
		for now not is ids or urns
		*/
		if ($query->exists('not') && $query->not)
		{
			$not = $query->not;
		}

		/**
		ORDER BY
		*/
		// TODO error if field to sort not exists
		$orderby = null;
		if ($query->order)
		{
			if (!is_string($query->order))
			{
				foreach($query->order->toArray() as $field => $direction)
				{
					//printlnd('sprt by '.$field);
					if ($E->has_field($field) or $field == 'relation')
						$orderbya[] = "$field $direction";
				}
				$orderby = join(', ', $orderbya);
			}
			else
				$orderby = $query->order;
		}

		if ($query->offset)
		{
			$offset = $query->offset;
		}

		if ($query->nofuture)
			$nofuture = true;

		if ($query->page)
			$options['page'] = (int) $query->page;

		/**
		select only fields
		for example $select_fields = 'id, max('.$query->field.') as max';
		*/
		$select_fields  = null;
		if ($query->field)
		{
			$select_fields = $query->field;
		}

		$group_fields  = null;
		if ($query->groupby)
		{
			$group_fields = $query->groupby;
		}

		if ($query->load_distinct)
		{
			$options['load_distinct'] = $query->load_distinct->toArray();
		}

		/**
		TODO В ключе нужно учитывать запрос или запрос на 1 сущность не должен использовать where параметры
		TODO DEP ON: INVALIDATE CACHE - not only on delete, update but on related deleted/assigned/unassigned
		TODO Cache IF - dont cache old news for example or TTL they fast
		*/
		$key = $E->name.':'.$query->id.':'.$query->lang;
		if (ENABLE_QUERY_CACHE === true && $cacheOne === true && !$query->nocache && $res = Cache::get($key))
		{
			$dataset = $res;
		}
		else
		{

			foreach ($E->extendstructure as $ee)
			{
				//println($ee);
				if ($query->$ee)
				{
					$extender = $query->$ee->resolve();//->current();

					$extender->extendMergeParents();
					$propertiesAndVariators = Entity::extenderPropertiesVariatorsHelper($extender);
					$properties = $propertiesAndVariators['properties'];
					$variators = $propertiesAndVariators['variators'];

					foreach ($variators as $variator)
					{
						//println($variator);
						$pname = $variator->uri;
						if ($query->$pname)
						{
							if ($query->$pname instanceof Message)
							{
								$vsa = $query->$pname->toArray();
								foreach ($vsa as $vsa)
								{
									if ($vsa != 'NULL')
									{
										$vsa = new URN($vsa);
										$evariators[$pname][] = $vsa->uuid()->toInt();
									}
								}
							}
							else
							{
								if ($query->$pname != 'NULL')
									$evariators[$pname][] = $query->$pname->uuid()->toInt();
							}
						}
					}
				}
				if ($evariators)
				{
					foreach ($evariators as $variatorName => $variationsIds)
					{
						//println($variatorName);
						foreach ($variationsIds as $varid)
						{
							//println($varid);
							$ms = ListDatabase::setAll(array('variator', $varid, $E->name));
							//println($ms);
							$vq[] = array('variator', $varid, $E->name);
						}
					}
					// /// Log::debug(print_r($vq, true),'listdb');
					$is = ListDatabase::setsIntersection($vq);
					if (count($is))
						$ids = new Message($is); // TODO merge with ids current
					else
						$ids = new Message(array(0));
				}
			}

			//if ($query->ns > 0) $byParent = null;

			//$ll = "byParent ".json_encode($byParent).", ids ".json_encode($ids).", joins ".json_encode($joins).", search ".json_encode($search).", order ".json_encode($orderby);
			//Log::info($ll,'sql');
			//println($ll,1,TERM_GREEN);
			$load = $estore->select( array($query->lang, $langFilter), $byParent, ($query->exists('last') ? $query->last : null), $search, $ids, $joins, $searchOR, $orderby, $offset, $select_fields, $group_fields, $nofuture, $not, $options );
			//build DataSet from sql result
			$dataset = new DataSet($load['set'], $E, $query, $load['total'], $load['distinctgroups']);

			// dataset with 1 datarow, cache if created > TimeOp::now() - 3600*24*30;
			if ($cacheOne === true && !$query->nocache && count($dataset) == 1)
			{
				if ($dataset->created && defined('DATAHISTORYTOCACHE'))
				{
					if ($dataset->created < TimeOp::now() - DATAHISTORYTOCACHE)
						$allowCache = false;
					else
						$allowCache = true;
				}
				else
				{
					$allowCache = true;
				}
				if ($allowCache && ENABLE_QUERY_CACHE === true) Cache::put($key, $dataset);
			}
		}
		return $dataset;
	}
}

?>
