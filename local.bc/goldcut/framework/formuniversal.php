<?php

class FormUniversal
{
	/**
	$urn - for create general urn (urn-entity), for edit concrete urn (urn-entity-uuid)
	$eo - DataRow data object to edit
	$action - form uri to submit (/app/method)
	$categoryIntentURN - used in extended structure. urn-anystructuredefiner-uuid
	$submitTitle=null - redefine title of submit button
	$redirectNext - pass urn-entity to controller
	$only=null - array of fields to edit instead of defined in entity xml config as usereditable=yes
	$moreactions - provide more submit buttons that override standart m.action (create or update)
	*/
	public static function build($urn, $eo, $action, $categoryIntentURN, $submitTitle=null, $redirectNext, $only=null, $moreactions, $exclude=array())
	{
		if ($eo)
		{
			$h .= self::formbody("update", $urn, $eo, null, $only, $exclude);
			if ($submitTitle === null) $submitTitle = 'Обновить данные';
		}
		else
		{
			$h .= self::formbody("create", $urn, null, $categoryIntentURN, $only, $exclude);
			if ($submitTitle === null) $submitTitle = 'Сохранить';
		}

		$h = self::wrapHtml($h, $urn, $action, $submitTitle, $redirectNext, $moreactions);

		return $h;
	}

	public static function wrapHtml($body, $urn, $action, $submitTitle, $redirectNext, $moreactions)
	{
		$h .= "<form data-hosturn=\"{$urn}\" action=\"$action\" method=\"POST\" class=\"entityform gcform\">\n";
		if ($redirectNext) $h .= "<input type=hidden name='_redirectNext' value=\"{$redirectNext}\">\n";

		$h .= $body;

		$h .= "<div class='formcontrols'>\n
			<input type=\"submit\" value=\"{$submitTitle}\" class=\"gcsubmit FL leftfloat\">\n"; // data-action='{$action}'

			foreach ($moreactions as $mactName => $mactTitle)
				$h .= "<input type=\"submit\" data-action='$mactName' value=\"{$mactTitle}\" class=\"gcsubmit FL leftfloat\">\n";

			$h .= "\n<div class=\"leftfloat leftpad1 controlinfo\"></div>
			<div class=\"rightfloat controlstatus\"></div>
			<br style=\"clear: both;\">
		</div>\n
		</form>
		";
		return $h;
	}

	public static function formbody($action, $urn, $eo, $categoryIntentURN, $editableFieldsAndEntities, $exclude, $lang) // , $editableFieldsAndEntities
	{
		foreach($editableFieldsAndEntities as $fieldNameOrdered)
		{
			$htmlkey = $fieldNameOrdered;
			if (is_array($fieldNameOrdered)) $htmlkey = key($fieldNameOrdered);
			$editableFieldsAndEntitiesNormalized []= $htmlkey;
		}

		//printlnd($editableFieldsAndEntities,1,TERM_GREEN);
		if (is_string($urn))
		{
			$ue = new URN($urn);
			$E = $ue->entitymeta;
		}
		else
		{
			$E = $urn->entitymeta;
		}

		if (!$E) return "No urn->entitymeta from urn $urn";
		if (!$editableFieldsAndEntities) return "No currentEditableFieldsAndEntities";

		//println($editableFieldsAndEntities,1,TERM_GREEN);

		//println($E->groupnames,1,TERM_GREEN);
		//println($E->virtualgroups,1,TERM_GREEN);

		$h .= "<input type=hidden name='action' value=\"{$action}\">\n";
		$h .= "<input type=hidden name='urn' value=\"{$urn}\">\n";
		$h .= "<input type=hidden name='lang' value=\"{$lang}\">\n";

		//if ($redirectNext) $h .= "<input type=hidden name='_redirectNext' value=\"{$redirectNext}\">\n";

		//$h .= "<fieldset class=collapsible><legend>\n";
		//$h .= $E->title['ru'];
		//$h .= "</legend>\n<div style='margin: 5px;'>\n";

		if (isURN($categoryIntentURN))
		{
			$categoryIntent = $categoryIntentURN->resolve(); // category intent
		}

		// STATUSES
		/**
		CHECK IT!
		*/
		{
			$statuses = $E->has_statuses();
			if (count($statuses))
			{
				$h .= '<div>';
				foreach ($statuses as $status)
				{
					//println($xstatus);
					//$status = $E->entityStatusByName($xstatus->name);
					//$status = $xstatus;
					//println($status);
					$xc1 = ''; $xc0 = '';
					$field_name = $status->name;

					if ($exclude && in_array($field_name, $exclude)) continue;
					if ($editableFieldsAndEntities && !in_array($field_name, $editableFieldsAndEntities)) continue;

                    $STRING_NO = array('ru'=>'Нет', 'en'=>'No');
                    $STRING_NOT = array('ru'=>'Нет', 'en'=>'Not');
                    $STRING_YES = array('ru'=>'Да', 'en'=>'Yes');

					if ($eo->$field_name > 0) {$xc1 = 'CHECKED';$bgc='#ddd';}
					elseif (!$eo->id && $status->default > 0) {$xc1 = 'CHECKED';$bgc='#ddd';}
					else {$xc0 ='CHECKED';$bgc='#fff';}
						$h .= "
						<div class='gcformfield' id='{$field_name}_field' class='entityfield' style=' padding: 2px; padding-left: 5px; border-left: 1px solid gray; width: 130px; float: left; background-color: $bgc;'>
						<label for=id>{$status->title}</label>
						".$STRING_NO[DEFAULT_LANG]." <input $xc0 type=radio name=$field_name value=0>
						".$STRING_YES[DEFAULT_LANG]." <input $xc1 type=radio name=$field_name value=1>
						</div>\n";
				}
				$h .= '</div>';
				$h .= "<br style='clear: both;'><br>";
			}
			$htmlNeeded = $h;
		}
		/*
		{
			$statuses = $E->has_statuses();
			foreach ($statuses as $status)
			{
				$xc1 = ''; $xc0 = '';
				$f = $status->name;
				if (!in_array($f, $editableFieldsAndEntities)) continue;
				if ($eo->$f > 0) $sv = 1;
				else $sv=0;
				$h .= "<input type=hidden name='$f' value='$sv'>";
			}
		}
		*/


		// FIELDS
		/*
		foreach ($E->field_metas as $field_uid)
		{
			$F = Field::id($field_uid);
			$f = $F->name;
			if (!in_array($f, $editableFieldsAndEntities)) continue;
			if ($only && !in_array($f, $only)) continue;
			if (!$F->system)
			{
				if ($f == '_parent')
					$html[$f] = Form::parentSelector($E, $eo->_parent);
				else
					$html[$f] = Form::field2form($F, $eo->$f, $prefix);
			}
		}
		*/
		// FIELDS
		foreach ($E->field_metas as $field_uid)
		{
			$h = '';
			//$h .= $field_uid;
			//println($field_uid);
			$groupname = null;
			$prefix = null;

			// close virtual
			/*
			if ($prevvirtg && $prevvirtg != $E->virtualgroups[$field_uid])
			{
				//$h .= ')))';
				$h .= "</fieldset>";
			}
			*/

			if (is_array($field_uid))
			{
				//println($field_uid);
				//println($E->groupnames);
				//println($E->groups);
				$prevvirtg = null;
				$f = $field_uid[key($field_uid)];
				$fk = key($field_uid);
				$fa = explode('_',$fk);
				$groupname = $fa[0];
				$fieldcanonicalname = $fa[1];

				// SKIP FIELDS & ENTITIES
				if (!in_array($fk, $editableFieldsAndEntities)) continue;
				if ($exclude && in_array($fk, $exclude)) continue;

				//$h .= $fieldcanonicalname . '@';
				//$h .= ($prevgroup) ? $prevgroup.' ' : 'NULLPREVG ';
				//$h .= ($groupname && !$prevgroup) ? "START FS " : 'CONT ';
				// close
				/*
				if ($prevg && $prevg != $E->groups[$fk])
				{
					//$h .= ')))';
					$h .= "</fieldset>";
				}
				*/
				// open
				/*
				if ($E->groups[$fk] && $prevg != $E->groups[$fk])
				{
					$h .= "<fieldset>";
					$h .= "<legend>{$E->groupnames[$groupname]}</legend>";
					//$h .= '{{{';
				}
				*/
				$prevg = $E->groups[$fk];
				$prefix = $groupname;
			}
			else // not named grouped (simple field or virtual grouped)
			{
				// close
				/*
				if ($prevg)
				{
					//$h .= ')))';
					$h .= "</fieldset>";
					$prevg = null;
				}
				// open virtual
				if ($E->virtualgroups[$field_uid] && $prevvirtg != $E->virtualgroups[$field_uid])
				{
					$h .= "<fieldset>";
					$h .= "<legend>{$E->virtualgroups[$field_uid]}</legend>";
					//$h .= '(((';
				}
				*/
				$prevvirtg = $E->virtualgroups[$field_uid];
			}

			$F = Field::id($field_uid);
			$F = $E->entityFieldByName($F->name); // NEW
			if (is_array($field_uid))
				$f = $fk;
			else
				$f = $F->name;
			// SKIP FIELDS & ENTITIES
			if (!in_array($f, $editableFieldsAndEntities)) continue;
			if ($exclude && in_array($f, $exclude)) continue;

			if (!$F->system)
			{
				if ($f == '_parent')
					$h .= Form::parentSelector($E, $eo->_parent, $eo->id);
				else
					$h .= Form::field2form($F, $eo->$f, $prefix, in_array($f, $E->required) );
			}



			if (($prevgroup && $groupname != $prevgroup)) //  || ($prevvirtg && !$E->virtualgroups[$field_uid])
			{
				//$h .= "FS CLOSE $groupname }}}";
				//$h .= "</fieldset>";
				$prevgroup = null;
			}
			else
				$prevgroup = $groupname;
			$html[$f] = $h;
		}
		$h = '';

		// BT
		foreach ($E->belongs_to() as $usedname => $bt)
		{
				$ff = $bt->name;
				if ($categoryIntent)
				{
					if ($usedname == $categoryIntent->entity->name)
					{
						$htmlNeeded = "<input type='hidden' name='{$categoryIntent->entity->name}' value='{$categoryIntent->urn}'>";
						$html[$usedname] .= "<p>Размещение в категории {$categoryIntent->title}</p>";
						continue;
					}
				}
				else
				{
					//if (!in_array($ff, $editableFieldsAndEntities)) continue;
					// SKIP FIELDS & ENTITIES
					if (!in_array($usedname, $editableFieldsAndEntities)) continue;
					if ($exclude && in_array($usedname, $exclude)) continue;

					//$data = $eo->$ff;
					$object = $E;
					$subject = $bt;
					$reference = 'BelongsTo';
					$widget = FormWidget::optimal($object, $reference, $subject, $usedname);
					$widget->setParentData($eo);
					$widget->withFieldset(false);
					$html[$usedname] .= (string) $widget;
				}
		}

		// HO
		foreach ($E->has_one() as $usedname => $ho)
		{
			$fn = $ho->name;
			//$data = $eo->$ff;
			// SKIP FIELDS & ENTITIES
			//println($editableFieldsAndEntities,2);
			if (!in_array($usedname, $editableFieldsAndEntitiesNormalized)) continue;
			if ($exclude && in_array($usedname, $exclude)) continue;

			$object = $E;
			$subject = $ho;
			$reference = 'HasOne';

			$widget = FormWidget::optimal($object, $reference, $subject, $usedname);
			$widget->setParentData($eo);
			//$widget->setData($data);
			//$widget->withFieldset(false);
			$html[$usedname] .= (string) $widget;
		}

		// UO
		foreach ($E->use_one() as $usedname => $ho)
		{
			$fn = $ho->name;
			// SKIP FIELDS & ENTITIES
			if (!in_array($usedname, $editableFieldsAndEntitiesNormalized)) continue;
			if ($exclude && in_array($usedname, $exclude)) continue;

			$object = $E;
			$subject = $ho;
			$reference = 'UseOne';  // !!!!
			$widget = FormWidget::optimal($object, $reference, $subject, $usedname);
			$widget->setParentData($eo);
			$html[$usedname] .= (string) $widget;
		}

		// HM
		foreach ($E->has_many() as $hm)
		{
			$fn = $hm->name;
			$ff = $hm->name;
			// SKIP FIELDS & ENTITIES
			if (!in_array($ff, $editableFieldsAndEntities)) continue;
			if ($exclude && in_array($ff, $exclude)) continue;

			if ($eo) $html[$ff] .= "<label>{$hm->title['ru']}</label>";
			//$data = $eo->$ff;
			$object = $E;
			$subject = $hm;
			$reference = 'HasMany';
			if ($eo)
			{
				$widget = FormWidget::optimal($object, $reference, $subject);
				$widget->setParentData($eo);
				$widget->setHost('urn:'.$E->name.'-'.$eo->id);
				//$widget->withFieldset(false);
				$html[$ff] .= (string) $widget;
			}
		}

		// UM
		foreach ($E->use_many() as $hm)
		{
			$fn = $hm->name;
			$ff = $hm->name;
			// SKIP FIELDS & ENTITIES
			if (!in_array($ff, $editableFieldsAndEntities)) continue;
			if ($exclude && in_array($ff, $exclude)) continue;

			if ($eo) $html[$ff] .= "<label>{$hm->title['ru']}</label>";
			//$data = $eo->$ff;
			$object = $E;
			$subject = $hm;
			$reference = 'HasMany'; // !!!!
			if ($eo)
			{
				$widget = FormWidget::optimal($object, $reference, $subject);
				$widget->setParentData($eo);
				$widget->setHost('urn:'.$E->name.'-'.$eo->id);
				//$widget->withFieldset(false);
				$html[$ff] .= (string) $widget;
			}
		}

		// EXTENDED STRUCTURE
		$d = $eo;

		foreach ($E->extendstructure as $ee)
		{

			// extended fields security
			if ($editableFieldsAndEntities && !in_array('extended', $editableFieldsAndEntities)) continue; // 'exteded' have to be in list of editable fields

			if ($d)
			{
				$extender = $d->$ee; // категория редактируемого объекта
			}
			else
			{
				$extender = $categoryIntent;
				/*
				if (!$categoryIntentURN) return "No categoryIntentURN";
				if (!isURN($categoryIntentURN)) return "categoryIntentURN is not URN object";
				$extender = $categoryIntentURN->resolve(); // category intent
				*/
			}

			if ($extender)
				$extender->extendMergeParents();
			else
				break;

			$propertiesAndVariators = Entity::extenderPropertiesVariatorsHelper($extender);

			$properties = $propertiesAndVariators['properties'];
			$variators = $propertiesAndVariators['variators'];

			foreach ($properties as $property)
			{
				$pname = $property->uri;
				$F = new FieldMeta(array('name'=>$pname,'title'=>$property->title,'type'=>$property->basetype,'units'=>$property->units));
				$html['extended'] .= Form::field2form($F, $d->$pname, $prefix);
			}

			foreach ($variators as $variator)
			{
				$pname = $variator->uri;
				if ($d->$pname)
				{

					if ($variator->multiple)
					{
						//println('mult');
						foreach($d->$pname as $o)
							$selected_urns[] = (string) $o->urn;
					}
					else
					{
						//println('single');
						//println($pname);
						//var_dump($d->$pname);
						$selected_urns[] = (string) $d->$pname->urn;
					}
					//printlnd($d->color);
					//printlnd($selected_urns);
				}
				$include_blank = true;
				$html['extended'] .= Form::category_selectbox($variator, $variator->variation, $selected_urns, $include_blank, $variator->multiple);
			}
		}




		// REL
		/**
		foreach ($E->related() as $rel)
		{
			if ($E->hasListOverRelation($rel->name)) continue;
			$fn = $rel->name.'_id';
			$ff = $rel->name;
			//$data = $eo->$ff;
			if (!in_array($ff, $editableFieldsAndEntities)) continue;
			if ($only && !in_array($ff, $only)) continue;

			$object = $E;
			$subject = $rel;
			$reference = 'Related';

			$widget = FormWidget::optimal($object, $reference, $subject);
			$widget->setParentData($eo);
			$widget->withFieldset(false);
			//$widget->setData($data);
			$widget->setHost('urn-'.$E->name.'-'.$eo->id);
			$html[$ff] .= (string) $widget;
		}
		*/

		// LISTS
		foreach ($E->lists() as $list)
		{
			if (!$eo) continue;
			$rel = $list['entity'];

			// SKIP FIELDS & ENTITIES
			$listName = $list['name'];
			//if ($exclude && in_array($listName, $exclude)) continue;
			//if ($only && !in_array($listName, $only)) continue;

			$fn = $rel->name;//.'_id';
			$ff = $rel->name;

			$object = $E;
			$subject = $rel;
			$reference = 'List';

			$widget = FormWidget::optimal($object, $reference, $subject);
			$widget->setParentData($eo);
			$widget->setMeta(array('ns'=>$list['ns'],'name'=>$list['name'],'title'=>$list['title'], 'entity'=>$list['entity']->name ));
			$widget->setTitle($list['title']);
			$html[$listName] .= $widget->makeHtml();
		}
		/**
		foreach ($E->lists() as $list)
		{
			if (!$eo) continue;
			$rel = $list['entity'];
			$listname = $list['name'];

			$fn = $rel->name.'_id';
			$ff = $rel->name;

			$object = $E;
			$subject = $rel;
			$reference = 'List';

			$widget = FormWidget::optimal($object, $reference, $subject);
			$widget->setParentData($eo);
			$widget->setMeta(array('ns'=>$list['ns'],'name'=>$list['name'],'title'=>$list['title'], 'entity'=>$list['entity']->name ));
			$widget->setTitle($list['title']);
			$widget->withFieldset(false);
			$listName = $list['name'];
			$html[$listName] .= $widget->makeHtml();
		}
		*/


		if (!$only)
		{
			$only = array_keys($html);
		}
		//println(array_keys($E->groups),1,TERM_RED);
		//println(array_keys($E->virtualgroups),1,TERM_RED);
		$allgroups = array_merge((array)$E->groups, (array)$E->virtualgroups);
		//println(array_keys($allgroups),1,TERM_RED);
		//println($allgroups);
		//printlnd(array_keys($html));
		$h = $htmlNeeded;
		foreach($editableFieldsAndEntities as $fieldNameOrdered)
		{
            //println($editableFieldsAndEntities);
			//println("$fieldNameOrdered {$allgroups[$fieldNameOrdered]}");
			if ($prevvirtg && $prevvirtg != $allgroups[$fieldNameOrdered])
			{
				$h .= "</fieldset>";
				$fs_state = 'closed';
			}
			if ($allgroups[$fieldNameOrdered] && $prevvirtg != $allgroups[$fieldNameOrdered])
			{
				$h .= "<fieldset>";
				$h .= "<legend>{$allgroups[$fieldNameOrdered]}</legend>";
				$fs_state = 'opened';
			}
			$prevvirtg = $allgroups[$fieldNameOrdered];
			$htmlkey = $fieldNameOrdered;
			if (is_array($fieldNameOrdered)) $htmlkey = key($fieldNameOrdered);
			$h .= $html[$htmlkey];
		}
		if ($fs_state == 'opened') $h .= "</fieldset>";
		return $h;
	}
}

?>