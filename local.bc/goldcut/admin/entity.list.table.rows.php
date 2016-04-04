<tr>

    <th><input type='checkbox' id='setunsetall' data-state=''></th>
	<th>&nbsp;</th>
	<?php
	if ($ENTITY->has_field('ordered')) echo "<th>&nbsp;</th>";
	?>


	<?php if ($ENTITY->is_multy_lang()) echo "<th>Translate</th>"; ?>
	<?php
	foreach ($ENTITY->adminfields as $fn)
	{
		if (Field::exists($fn))
		{
			$f = Field::ref($fn);
			echo "<th>{$f->title}</th>";
		}
		else
		{
			if (Entity::exists($fn))
			{
				$e = Entity::ref($fn);
				echo "<th>{$e->title[SystemLocale::default_lang()]}</th>";
			}
			else
				echo "<th>{$fn}</th>";
		}
	}
	?>
</tr>

<?php

//if ($ds->entity->name == 'news') $uriwith = 300;
//else $uriwith = 150;

foreach ($ds as $dr)
{
	if ($i % 2 > 0) $oe = 'odd_row'; else $oe = 'even_row'; $i++;

	if ($dr->active > 0) $border = 1; else $border = 0;
	if ($dr->top > 0) { $color='green'; $border = 2; } else $color='#ccc';

	$statusClasses = '';
	foreach ($ENTITY->statuses as $statusid)
	{
		$status = Status::ref($statusid)->name;
		if ($dr->$status)
		{
			$statusClasses .= " status-{$status}-yes";
			//printlnd($dr->$status);
		}
		else
			$statusClasses .= " status-{$status}-no";
	}

	if ($ENTITY->has_field('created') && is_array($ENTITY->groupby) && in_array('date', $ENTITY->groupby))
	{
		$newsTs = $dr->created;
		$now = TimeOp::now();
		if ( ($now - $newsTs) < 0) $futureTag = true;
		else $futureTag = false;
		$futureprefix = '';
		if ($futureTag)
		{
			$statusClasses .= ' futureentity';
			//$futureprefix = 'Будущее ';
		}

		if ($dr->date != $prevdate)
		{
			$TRstatusClasses = 'trgrouphead';
			echo "<tr class='group_row $TRstatusClasses'><td class='tdgrouphead' colspan='10'>{$futureprefix}{$dr->date}</tr>";
		}
		else
		{
			$TRstatusClasses = '';
		}
		$prevdate = $dr->date;
	}


	if (in_array('year', $ENTITY->groupby))
	{
		if ($dr->year != $prev)
		{
			$TRstatusClasses = 'trgrouphead';
			echo "<tr class='group_row $TRstatusClasses'><td class='tdgrouphead' colspan='10'>{$futureprefix}{$dr->year}</tr>";
		}
		else
		{
			$TRstatusClasses = '';
		}
		$prev = $dr->year;
	}

	echo "<tr class='movable' id='{$dr->urn}' class='$oe {$statusClasses}'>"; //<td width=20><input type=checkbox id=checkme1 /></td>

	if ($ENTITY->has_field('ordered'))
		echo "<td width=20><div class=photoitem_move><div class='moveDataS'>{$dr->ordered}</div><div class=move2S></div></div></td>";

    echo "<td class='' width=20><input type='checkbox' class='multyactionurn' data-urn='{$dr->urn}'></td>";

	echo "<td class='flagtd {$statusClasses}' width=55><div class='adminflagfield {$statusClasses}' style=\"padding: 5px; border: ${border}px solid ${color}; font-size: 8pt;\">";

	$lang_code = SystemLocale::default_lang();

    //---SnP----
    $fromlang = $_GET['lang']? $_GET['lang'] : $lang;
    //---SnP----

	if ($ENTITY->is_multy_lang())
	{
		foreach ($ENTITY->lang_codes() as $lang_code)
		{
			if ($dr->is_translated($lang_code))
				echo "<a style='text-decoration: none;' href='/goldcut/admin/?urn={$dr->urn()}&action=edit&lang=$lang_code'><img src=/goldcut/assets/img/flag-${lang_code}.gif class='flag' border=0> <b>$lang_code</b></a>";
		}
		echo "</div></td>";
		echo "<td class='flagtd' width=80><div style=\"padding: 5px; border: 1px dashed #ccc; font-size: 8pt;\">";
		foreach ($ENTITY->lang_codes() as $lang_code)
		{
			if (!$dr->is_translated($lang_code))
				echo "<a style='text-decoration: none;' href='/goldcut/admin/?urn={$dr->urn()}&action=translate&lang=$lang_code&fromlang={$fromlang}'><img src=/goldcut/assets/img/flag-${lang_code}.gif class='flag' border=0> $lang_code</a> ";
		}
	}
	else
	{
		echo "<a style='display: block;' href='/goldcut/admin/?urn={$dr->urn}&action=edit&lang=$lang_code'><img src=/goldcut/assets/icons/application_edit.png class='flag' border=0></a>";
	}
	echo "</div></td>";


	foreach ($ENTITY->adminfields as $k => $fn)
	{
		try
		{
			$v = $dr->$fn;
			if (is_string($v)) $va = $v;
			else if (is_int($v)) $va = $v;
			else if (is_null($v)) $va = null;
			else
			{
				if ($v instanceof DataSet && count($v) == 0)
					$va = '&mdash;';
        else if ($v instanceof DataSet && count($v) == 1)
        {
          if ($v->title)
            $va = $v->title;
					else
            $va = $v->current()->adminview();
        }
				else if ($v instanceof DataSet && count($v) > 1)
				{
					$varr = array();
					foreach($v as $vc) $varr[] = $vc->adminview();
					$va = join(', ', $varr);
				}
				else if ($v instanceof DataRow)
					$va = $v->adminview();
				else if ($v instanceof stdClass)
					$va = json_encode($v);
				if (!strlen($va))
				{
					$va = (string) $v;
					if (ENV === 'DEVELOPMENT')
						$va .= " (no adminview() plugin for {$v->entity})";
				}
			}
		}
		catch (Exception $e)
		{
			$va = $e;
		}
		echo "<td class='admin-td-$fn'>{$va}</td>";
	}

	echo "</tr>";
}

?>