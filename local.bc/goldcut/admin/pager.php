<hr size=1 color="#cccccc" style="margin-bottom: 10px;">

<?php

/**
TODO parse uri and save search params in pager
*/

	$news = $ds;
	
	$params = array();
	foreach ($_GET as $getp => $getv)
	{
		if ($getv)
		{
			if ($getp != 'page') $params []= "{$getp}={$getv}"; 
		}
	}
	$allparams = join('&',$params);
	
	$baseuri = "/goldcut/admin/?".$allparams; //$_GET['urn']."&action=".$_GET['action'];
	
	if ($ds->total)
	{
		
		if (!$current_page) $current_page = 1;
			
		$maxp = 20;
		$startp = 1;
		if ($current_page >= $maxp) 
		{
			$maxp = $current_page + $maxp;
			$startp = $current_page;	
		}
		
		if ($current_page >= $maxp)
		echo "<a href='{$baseuri}&page=1' id=\"previous\" class='admpager'>1</a>";
		
		for ($i=$startp; $i < ($ds->total/$perpage+1) && $i <= $maxp; $i++)
		{
			{
				if ($i == $current_page)
					echo "<a class=\"admpager currentpagelink\" href='{$baseuri}&page={$i}' style='color: green;'>{$i}</a>";
				else
					echo "<a class='admpager' href='{$baseuri}&page={$i}'>{$i}</a>";
			}
		}
		
	}
	else
    {
        if ($ENTITY->treeview) $notpagedtree = "No paging in treeview";
		print "<p style='color: #ddd'>{$notpagedtree}</p>"; // Not paged without ds->total
    }
	
?>

<?php

if ($ds->total) echo "<br><br>";
	
$admintime = Utils::reportTimer('test_run');
print "<font size=2 color=777>".$STRING_LOADTIME[DEFAULT_LANG]." <font size=2 color='{$admintime['color']}'>".$admintime['time'].'</font>. ';
$onpage = count($ds);
print $STRING_FOUND[DEFAULT_LANG]." &mdash; {$onpage}";
if ($ds->total > 0 && $ds->total != $onpage) print ", ".$STRING_TOTALWITHFILTERS[DEFAULT_LANG]." &mdash; {$ds->total}";
if ($ENTITY->treeview) print ". <a href='{$baseuri}&page=1'>".$STRING_ENABLEPAGING[DEFAULT_LANG]."</a>";
if ($ds->total > 0 && $ds->total != $onpage) print ". <a href='{$baseuri}&perpage={$ds->total}'>".$STRING_SHOWALL[DEFAULT_LANG]."</a>";
print "</font>";
?>