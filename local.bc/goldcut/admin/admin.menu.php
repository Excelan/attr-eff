<div id="banner">

	<ul id="tabnav">

	  <?php

	$adminApps = array();
	$replaceEntityControls = array();

	foreach ($GLOBALS['CONFIG']['SITEMAP'] as $route)
	{
		if ($route['role'] == 'admin' && $route['entity'])
		{
			$replaceEntityControls[$route['entity']] = $route;
		}
		else if ($route['role'] == 'admin' && !$route['entity'])
		{
			$adminApps[] = $route;
		}
	}


		foreach (Entity::each_managed_entity($filter) as $m => $es)
		{
			foreach($es as $entity)
			{
			  if (!$entity->directmanage)
			  	continue;
                if (in_array($entity->name, Options::get('admin_filter_entities')))
                    continue;
				if ($entity->prototype->getInDomain() == 'Document') continue;
				if ($_GET['urn'])
			  	$req_urn = new URN($_GET['urn']);
				else
					$req_urn = null;

			  if ($req_urn && (string) $entity->urn == (string) $req_urn->generalize())
				$tmc = "active";
			  else
				$tmc = "";
			  $lang = SystemLocale::default_lang();
			if ($prevm != $m) $tmc .= " managedpassingtab";
			else $marg = '';
			$prevm = $m;
			  if ($replaceEntityControls[$entity->name])
					echo "<li><a href='/goldcut/admin/?app=".$replaceEntityControls[$entity->name]['uri']."&lang={$lang}' class='$tmc' title='{$m} / {$entity->title[DEFAULT_LANG]}'>".$entity->title[DEFAULT_LANG]."</a></li>";
				else
			  echo "<li><a href='/goldcut/admin/?urn=".$entity."&action=list&lang={$lang}' class='$tmc' title='{$m} / {$entity->title[DEFAULT_LANG]}'>".$entity->title[DEFAULT_LANG]."</a></li>"; // <span style='color:gray'>({$m})</span>
			}
		}

        /*
		if (ENV == 'PRODUCTION') $ht = 'Помощь';
		else  $ht = 'Разработка';
		echo "<li style='float:right;'><a href='/goldcut/admin/' class=''>{$ht}</a></li>";
        */
          if (ENV != 'PRODUCTION')
          {
            $ht = 'Разработка';
            echo "<li style='float:right;'><a href='/goldcut/admin/' class=''>{$ht}</a></li>";
          }


		foreach($adminApps as $aa)
		{
			echo "<li style='float:right;'><a href='/goldcut/admin/?app={$aa['uri']}' class=''>{$aa['title']}</a></li>";
		}



	  ?>

	</ul>

</div>
