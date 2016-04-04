<?php



//define('DEBUG_SQL',TRUE);
if ($_GET['urn']) $urn = new URN($_GET['urn']);

/**
ADD
*/
if ($_GET['action'] == 'create' )
{
	print Form::createEntity($urn, null, $_GET['lang']);
}

/**
EDIT
*/
if ($_GET['action'] == 'edit' )
{
	//$o = Entity::query($urn);
	$m = new Message('{"action": "load"}');
	$m->urn = $urn;
	$m->lang =  $_GET['lang'];
	$m->last = 1;
	//$m->nocache = true;
	$r = $m->deliver();
	if (!count($r)) throw new Exception('URN '.$urn.' NOT EXISTS IN DB');
	print Form::createEntity($urn, $r->current(), $_GET['lang']);
}

/**
TRANSLATE
*/
if ($_GET['action'] == 'translate' )
{
	$m = new Message('{"action": "load"}');
	$m->urn = $urn;
	$m->lang =  $_GET['fromlang']?$_GET['fromlang']:$lang;
	$r = $m->deliver();
	$o = $r->current();

	print Form::translateEntity($o, $_GET['lang']);
}

/**
LISTING
*/
if ($_GET['action'] == 'list' )
{
	$ENTITY = $urn->entitymeta;
	$E = $ENTITY->name;

	$m = new Message($_GET);
	$m->action = 'load';

	if ($m->search) $m->includeinner = array('urn:category');

	$perpage = GLOBAL_PER_PAGE;
	if (!$perpage) $perpage = 20;
    if ($_GET['perpage']) $perpage = $_GET['perpage'];
	$current_page = (integer) $_GET['page'];
	// только этот параметр включает рассчет общего количества записей в запросе без лимитов
	if (!$current_page)
		$m->page = 1;
	else
		$m->page = $current_page;

	if (!$ENTITY->treeview && !$ENTITY->has_field('ordered'))
	{
		if (is_numeric($_GET['page']))
		{
			$m->offset = $perpage * $current_page - $perpage;
		}
		$m->last = $perpage;
	}

	if ($ENTITY->defaultorder)
	{
		$m->order = $ENTITY->defaultorder;
	}
	/**
	TODO
	else IF FIELD ordered, title, uri
	TODO ADD SELECTABLE SORTS price, weight, READS etc
	TODO ADD DATE RANGE
	*/
	/*
	if ($ENTITY->name == 'news')
		$m->order = array("date" => "DESC", "time" => "DESC");
	else
		$m->order = array("id" => "DESC");
	*/

	if ($_GET['lang'])
	{
		$m->lang = $_GET['lang'];
	}
    //----- SnP -----
    else {
        $m->lang = $lang;
    }
    //----- SnP -----

	if ($ENTITY->adminadd)
	{
		//echo "Добавить ";
		echo $ENTITY->title[$m->lang] . ' ';

		if ($ENTITY->is_multy_lang())
		{
			foreach ($ENTITY->lang_codes() as $lang_code)
			{
				//if ($lang_code == SystemLocale::default_lang()) // TODO +
				echo "<a style='text-decoration: none;' href='/goldcut/admin/?urn={$_GET[urn]}&action=create&lang=$lang_code'><img src=/goldcut/assets/img/flag-${lang_code}.gif class='flag' border=0>+</a> "; // {$ENTITY->title[$lang_code]}
			}
		}
		else
		{
			$lang_code = SystemLocale::default_lang();
			echo "<a style='text-decoration: none;' href='/goldcut/admin/?urn={$_GET[urn]}&action=create'><img src=/goldcut/assets/icons/add.png border=0> ".$STRING_ADD[DEFAULT_LANG]."</a> ";
		}
	}

	if ($ENTITY->is_multy_lang())
	{
		echo " | Листинг ";
		foreach ($ENTITY->lang_codes() as $lang_code)
			echo "<a href='/goldcut/admin/?urn={$_GET[urn]}&action=list&lang=$lang_code'><img src=/goldcut/assets/img/flag-${lang_code}.gif class='flag' border=0></a> ";
	}


	$user_saved_statuses = json_decode($_COOKIE["{$ENTITY->name}-statuses"], true);
	if (!is_array($user_saved_statuses)) $user_saved_statuses = array();
	// active, inactive, undefines
	foreach ($user_saved_statuses as $userstatus => $value)
	{
		$value = (int) $value;
		if ($value == 1) $m->$userstatus = $value;
		if ($value == -1) $m->$userstatus = '0';
		// if ($value == 0) UNDEFINED - ANY STATUS OK
	}

	foreach ($ENTITY->has_statuses() as $status)
	{
		//$status = $ENTITY->entityStatusByName($xstatus->name);
		$classE='statusactive ';
		$classD='statusinactive ';
		$classA='statusany ';
		if ($status->default) $classE .= "status-default-enabled ";
		if ($user_saved_statuses[$status->name] == 1) //  or ($status->default and )
		{
			$classE .= 'statusselected ';
		}
		/*
		elseif ($status->default && $user_saved_statuses[$status->name] != -1)
		{
			$classE .= 'statusselected ';
		}
		*/
		elseif ($user_saved_statuses[$status->name] == -1)
		{
			$classD .= 'statusselected ';
		}
		else
		{
			$classA .= 'statusselected ';
		}

        /*
		$anytext = 'Любой';
		$rod = Text::rod($status->title);
		if ($rod=='F') $anytext = 'Любая';
		if ($rod=='S') $anytext = 'Любое';
        */
        $anytext = $STRING_ANY[DEFAULT_LANG];

		print "<a id='control-status-{$status->name}' class='chstatus chstatusE handler-status-{$status->name} $classE' data-statusname='".$status->name."' data-entity='".$ENTITY->name."' title='".$status->title."' href='#{$ENTITY->name}/{$status->name}'>".$status->title."</a>";
		print "<a id='control-status-{$status->name}' class='chstatus chstatusD handler-status-{$status->name} $classD' data-statusname='".$status->name."' data-entity='".$ENTITY->name."' title='".$status->title."' href='#{$ENTITY->name}/{$status->name}'>".$STRING_NO[DEFAULT_LANG]." ".$status->title."</a>";
		print "<a id='control-status-{$status->name}' class='chstatus chstatusA handler-status-{$status->name} $classA' data-statusname='".$status->name."' data-entity='".$ENTITY->name."' title='".$status->title."' href='#{$ENTITY->name}/{$status->name}'>{$anytext} ".$status->title."</a>";
	}

	/*
	LISTING BY ENTITY
	*/
	//print "<br style='clear: both;'><br>";
	//println($m);
	//define('DEBUG_SQL',TRUE);
	Utils::startTimer('test_run');
	try
	{
		$ds = $m->deliver();
		if ($ENTITY->treeview)
		{
			$ds->treesort();
		}
	}
	catch (Exception $e)
	{
		println($e);
	}

	try
	{
		require "entity.list.table.php";
	}
	catch (Exception $e)
	{
		println($e);
	}

	require "pager.php";

	//print " <font size=2 color=777><a href='/goldcut/admin/?plugin=import'>Импорт CSV</a></font>";
}

?>