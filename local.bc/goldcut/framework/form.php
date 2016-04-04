<?php

class form
{

    private function __construct()
    {
    }

    public static function translateEntity($o, $lang)
    {
        if (!count($o)) {
            throw new Exception('Form has nothing to translate');
        }
        $urn = $o->urn;
        $E = $urn->E();
        $h .= "
			<form enctype=\"multipart/form-data\" action=\"/goldcut/admin/processor.php\" method=\"POST\">
			<input type=hidden name=action value=translate>
			";
        $h .= "<input type=hidden name=urn value=\"".$urn."\">";
        $h .= "<input type=hidden name=lang value=\"".$lang."\">";

        $h .=  "<fieldset class=collapsible><legend>";
        $h .=  "$lang <img src=/goldcut/assets/img/flag-${lang}.gif border=0>";
        $h .=  "</legend><div style='margin: 5px;'>";

        foreach ($E->lang_field_metas as $field_uid) {
            $F = Field::id($field_uid);
            $f = $F->name;
            $dataKey = $F->name;
            $h .= self::field2form($F);
            $h .= "<div style='border: 1px dotted green; padding: 5px 10px; font-size: 80%'>{$o->$dataKey}</div>";
                // TODO pre translate with google translation
                // TODO transfer img in text with placeholders for new text
                // TODO copy from other lang fot editin in place
        }
        $h .= "</div></fieldset>
			<br><input type=submit value='TRANSLATE' class='submit button'></form>";
        return $h;
    }

    public static function createEntity($urn, $eo=null, $lang=DEFAULT_LANG)
    {
        $STRING_COPYASNEW = array('ru'=>'КОПИРОВАТЬ КАК НОВЫЙ', 'en'=>'COPY AS NEW');
        $STRING_DELETE = array('ru'=>'УДАЛИТЬ', 'en'=>'DELETE');
        $STRING_SAVE = array('ru'=>'СОХРАНИТЬ', 'en'=>'SAVE');
        $STRING_1 = array('ru'=>'и вернуться на', 'en'=>'and return to');
        $STRING_2 = array('ru'=>'эту страницу', 'en'=>'this page');
        $STRING_3 = array('ru'=>'список', 'en'=>'list');
        $STRING_4 = array('ru'=>'предыдущую страницу', 'en'=>'previous page');
        //$STRING_ = array('ru'=>'', 'en'=>'');
        //$STRING_ = array('ru'=>'', 'en'=>'');

        // DEBUG
        //$mm = json_encode($eo->toArray());
        //printlnd($mm);
        //$h .= "<script> var mm = $mm; console.log(mm);</script>";
        $h = '';
        $h .= "<form data-hosturn=\"{$urn}\" action=\"/goldcut/admin/processor.php\" method=\"POST\" class=entityform id='entityformset'>\n";
        $h .= "<input type='hidden' id='editedlang' value='$lang'>";
        if ($lang != DEFAULT_LANG) {
            $h .= "<h3 style='margin: 0; padding: 0;'>$lang</h3>";
        }

        if ($eo) {
            //$h .= self::formbody("update", $urn, $eo, $lang);
            $only = $eo->entity->allOrdered;
            $action = 'update';
            $h .= FormUniversal::formbody($action, $urn, $eo, $categoryIntentURN, $only, $exclude, $lang); // $currentEditableFieldsAndEntities
        } else {
            //$h .= self::formbody("create", $urn, null, $lang);
            $only = $urn->entity->allOrdered;
            $action = 'create';
            $h .= FormUniversal::formbody($action, $urn, null, $categoryIntentURN, $only, $exclude, $lang);
        }

        $h .= "<br><input type=submit value='".$STRING_SAVE[DEFAULT_LANG]."' class='submit button'>\n";
        $selfbackdef = '';
        $listbackdef = '';
        if (ADMIN_AFTERSAVE_RETURNTO_SELF === true) {
            $selfbackdef = 'checked';
        } else {
            $listbackdef = 'checked';
        }
        $h .= "<span style='color: #777;'>".$STRING_1[DEFAULT_LANG]." <input type='radio' name='returnto' $selfbackdef value='self'> ".$STRING_2[DEFAULT_LANG]." / <input type='radio' name='returnto' $listbackdef value='list'> ".$STRING_3[DEFAULT_LANG]." / <input type='radio' name='returnto' value='{$_SERVER['HTTP_REFERER']}'> ".$STRING_4[DEFAULT_LANG]."</span>";
        $urnt = $urn->generalize();
        //$h .= " <a href='/goldcut/admin/?urn={$urnt}&action=list&lang={$lang}'>К списку</a>";
        $h .= "</form>\n\n<br><br>";
        // ajax delete
        $u = $eo->user;
        if (count($u) == 1) {
            $byuser = "data-user='{$u->urn}'";
        }
        $h .= "<input type=button value='".$STRING_DELETE[DEFAULT_LANG]."' id='urn_delete' data-urn='$urn' {$byuser} class='submit button redbutton'>\n\n<br><br>";
        if ($eo->entity->clonable) {
            $h .= "<br><input type=submit id='urn_clone' value='".$STRING_COPYASNEW[DEFAULT_LANG]."' data-urn='$urn' {$byuser} class='submit button'>\n</form>\n\n<br><br>";
        }

        return $h;
    }

    public static function formbody($action, $urn, $eo=null, $lang)
    {
        die('DEPRECATED ADMIN FORM (BUT CUT EXTENDED STRUCTURE FROM HERE FIRST)');

        /*

        $E = $urn->entitymeta;

        $h .= "<input type=hidden name='action' value=\"{$action}\">\n";
        $h .= "<input type=hidden name='urn' value=\"{$urn}\">\n";
        $h .= "<input type=hidden name='lang' value=\"{$lang}\">\n";

        //$h .=  "<fieldset class=collapsible><legend>\n";
        //$h .=  $E->title['ru'];
        //$h .=  "</legend>\n<div style='margin: 5px;'>\n";

        // STATUSES
        $statuses = $E->has_statuses();
        if (count($statuses))
        {
            $h .= '<div>';
            foreach ($statuses as $status)
            {
                $xc1 = ''; $xc0 = '';
                $field_name = $status->name;
                if ($eo->$field_name > 0) {$xc1 = 'CHECKED';$bgc='#ddd';}
                elseif (!$eo->id && $status->default > 0) {$xc1 = 'CHECKED';$bgc='#ddd';}
                else {$xc0 ='CHECKED';$bgc='#fff';}
                    $h .= "
                    <div class='gcformfield' id='{$field_name}_field' class='entityfield' style=' padding: 2px; padding-left: 5px; border-left: 1px solid gray; width: 130px; float: left; background-color: $bgc;'>
                    <label for='$field_name'>{$status->title}</label>
                    нет<input $xc0 type=radio name=$field_name value=0>
                    да<input $xc1 type=radio name=$field_name value=1>
                    </div>\n";
            }
            $h .= '</div>';
            $h .= "<br style='clear: both;'><br>";
        }

        // FIELDS
        foreach ($E->field_metas as $field_uid)
        {
            //$h .= $field_uid;
            $groupname = null;
            $prefix = null;

            // close virtual
            if ($prevvirtg && $prevvirtg != $E->virtualgroups[$field_uid])
            {
                //$h .= ')))';
                $h .= "</fieldset>";
            }

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
                //$h .= $fieldcanonicalname . '@';
                //$h .= ($prevgroup) ? $prevgroup.' ' : 'NULLPREVG ';
                //$h .= ($groupname && !$prevgroup) ? "START FS " : 'CONT ';
                // close
                if ($prevg && $prevg != $E->groups[$fk])
                {
                    //$h .= ')))';
                    $h .= "</fieldset>";
                }
                // open
                if ($E->groups[$fk] && $prevg != $E->groups[$fk])
                //if ($groupname && !$prevgroup)
                {
                    $h .= "<fieldset>";
                    $h .= "<legend>{$E->groupnames[$groupname]}</legend>";
                    //$h .= '{{{';
                }
                $prevg = $E->groups[$fk];
                $prefix = $groupname;
            }
            else // not named grouped (simple field or virtual grouped)
            {
                // close
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
                $prevvirtg = $E->virtualgroups[$field_uid];
            }

            $F = Field::id($field_uid);
            if (is_array($field_uid))
                $f = $fk;
            else
                $f = $F->name;

            if (!$F->system)
            {
                if ($f == '_parent')
                    $h .= self::parentSelector($E, $eo->_parent, $eo->id);
                else
                    $h .= self::field2form($F, $eo->$f, $prefix);
            }

            if (($prevgroup && $groupname != $prevgroup)) //  || ($prevvirtg && !$E->virtualgroups[$field_uid])
            {
                //$h .= "FS CLOSE $groupname }}}";
                //$h .= "</fieldset>";
                $prevgroup = null;
            }
            else
                $prevgroup = $groupname;


        }

        //copy from formuniversal
        $d = $eo;
        // EXTENDED STRUCTURE
        foreach ($E->extendstructure as $ee)
        {
            if ($d)
                $extender = $d->$ee; // категория редактируемого объекта
            else
            {
                $extender = $categoryIntent;
                //if (!$categoryIntentURN) return "No categoryIntentURN";
                //if (!isURN($categoryIntentURN)) return "categoryIntentURN is not URN object";
                //$extender = $categoryIntentURN->resolve(); // category intent
            }

            if (!count($extender)) continue; // skip unexistent category

            $extender->extendMergeParents();
            $propertiesAndVariators = Entity::extenderPropertiesVariatorsHelper($extender);
            $properties = $propertiesAndVariators['properties'];
            $variators = $propertiesAndVariators['variators'];

            foreach ($properties as $property)
            {
                $pname = $property->uri;
                $F = new FieldMeta(array('name'=>$pname,'title'=>$property->title,'type'=>$property->basetype));
                $h .= Form::field2form($F, $d->$pname, $prefix);
            }

            foreach ($variators as $variator)
            {
                $pname = $variator->uri;
                if ($d->$pname)
                {
                    if ($variator->multiple)
                    {
                        foreach($d->$pname as $o)
                            $selected_urns[] = (string) $o->urn;
                    }
                    else
                    {
                        $selected_urns[] = (string) $d->$pname->urn;
                    }
                }
                $include_blank = true;
                $h .= Form::category_selectbox($variator, $variator->variation, $selected_urns, $include_blank, $variator->multiple);
            }
        }
        // END copy from formuniversal

        // HO
        foreach ($E->has_one() as $usedname => $ho)
        {
            //println("$k => $ho");
            $fn = $ho->name.'_id';
            //$ff = $ho->name;
            //$data = $eo->$ff;

            $object = $E;
            $subject = $ho;
            $reference = 'HasOne';

            $widget = FormWidget::optimal($object, $reference, $subject, $usedname);
            $widget->setParentData($eo);
            //$widget->setData($data);
            $h .= (string) $widget;

        }

        // UO
        foreach ($E->use_one() as $usedname => $ho)
        {

            $fn = $ho->name.'_id';
            $ff = $ho->name;
            //$data = $eo->$ff;

            $object = $E;
            $subject = $ho;
            $reference = 'UseOne';

            $widget = FormWidget::optimal($object, $reference, $subject, $usedname);
            $widget->setParentData($eo);
            //$widget->setData($data);
            $h .= (string) $widget;

        }

        // HM
        foreach ($E->has_many() as $hm)
        {
            $h .= "<label>Содержит набор &mdash; {$hm->title['ru']}</label>";
            $fn = $hm->name.'_id';
            $ff = $hm->name;
            //$data = $eo->$ff;

            $object = $E;
            $subject = $hm;
            $reference = 'HasMany';

            if ($eo && SystemLocale::default_lang() == $lang) // in create we dont know host urn
            {
                $widget = FormWidget::optimal($object, $reference, $subject);
                $widget->setParentData($eo);
                //$widget->setData($data);
                $widget->setHost('urn-'.$E->name.'-'.$eo->id);
                $h .= (string) $widget;
            }
        }

        // UM
        foreach ($E->use_many() as $hm)
        {
            //printlnd($hm);
            $h .= "<label>Использует набор &mdash; {$hm->title['ru']}</label>";
            $fn = $hm->name.'_id';
            $ff = $hm->name;
            //$data = $eo->$ff;

            $object = $E;
            $subject = $hm;
            $reference = 'UseMany';

            if ($eo && SystemLocale::default_lang() == $lang) // in create we dont know host urn
            {
                $widget = FormWidget::optimal($object, $reference, $subject);
                $widget->setParentData($eo);
                //$widget->setData($data);
                $widget->setHost('urn-'.$E->name.'-'.$eo->id);
                $h .= (string) $widget;
            }
        }

        // LISTS
        foreach ($E->lists() as $list)
        {
            if (!$eo) continue;
            $rel = $list['entity'];

            $fn = $rel->name.'_id';
            $ff = $rel->name;

            $object = $E;
            $subject = $rel;
            $reference = 'List';

            $widget = FormWidget::optimal($object, $reference, $subject);
            $widget->setParentData($eo);
            $widget->setMeta(array('ns'=>$list['ns'],'name'=>$list['name'],'title'=>$list['title'], 'entity'=>$list['entity']->name ));
            $widget->setTitle($list['title']);
            $h .= $widget->makeHtml();
        }

        // BT
        foreach ($E->belongs_to() as $bt)
        {
            $ff = $bt->name;
            //$data = $eo->$ff;

            $object = $E;
            $subject = $bt;
            $reference = 'BelongsTo';

            $widget = FormWidget::optimal($object, $reference, $subject);
            $widget->setParentData($eo);
            //if (count($data))
            //	$widget->setData( $data->asURNs() );
            $h .= (string) $widget;
        }
        //$h .= "</div></fieldset>\n";
        return $h;
        */
    }

    public static function field2form($F, $field_value=null, $prefix=null, $required=false)
    {
        if ($F->disabled === true) {
            return;
        }

        $h = '';
        $field_name = $F->name;
        $field_type = $F->type;
        $field_title = $F->title;

        $field_name = self::wrapfield($field_name, $prefix);

        if ($required) {
            $requiredCss = 'required';
        }

        switch ($field_type) {

            case "string":
                //$field_value_safe = htmlspecialchars($field_value);
                $field_value_safe = str_replace('"', '&quot;', $field_value);
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='".$field_name."'>$field_title</label>
				<input type='text' name='$field_name' value=\"$field_value_safe\" $dis class='text {$field_name}'>
				</div>\n";
            break;

            case "json":
                //$field_value_safe = htmlspecialchars($field_value);
                $field_value_safe = str_replace('"', '&quot;', json_encode($field_value));
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='".$field_name."'>$field_title</label>
				<input type='text' name='$field_name' value=\"$field_value_safe\" $dis class='text {$field_name}'>
				</div>\n";
                break;

            case "integer":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
				<input type=text name='".$field_name."' size=10 class='int' value='$field_value'>&nbsp;{$F->units}
				</div>\n";
            break;

            case "long":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
				<input type=text name='".$field_name."' size=10 class='int' value='$field_value'>&nbsp;{$F->units}
				</div>\n";
            break;

            case "float":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
					<div class='BLK'>
						<div class='FL'><input type=text name='".$field_name."' size=10 class='float $field_name' value='$field_value'>&nbsp;{$F->units}</div>
						<div class='plugpoint'></div>
					</div>
				</div>\n"; //
            break;

            case "money":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
					<div class='BLK'>
						<div class='FL'><input type=text name='".$field_name."' size=10 class='float $field_name' value='$field_value'>&nbsp;{$F->units}</div>
						<div class='plugpoint'></div>
					</div>
				</div>\n"; //
                break;

            case "tarray":
                        if (count($field_value)) {
                            $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
				<textarea disabled='disabled' id=content_$field_name name='".$field_name."' rows=2>";
                            foreach ($field_value as $fv) {
                                $h .= $fv."\n";
                            }
                            $h .= "
				</textarea>
				</div>\n";
                        }
            break;

            case "text":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
				<textarea id=content_$field_name name='".$field_name."' rows=2>$field_value</textarea>
				</div>\n";
            break;

            case "richtext":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'><label class='$requiredCss' for=$field_name>$field_title {$F->htmlallowed}</label> <textarea id='content_{$field_name}' name='".$field_name."' rows=2 class='richtext flext growme maxheight-400'>$field_value</textarea><br>\n";
                // $h .= "<a href=# class=posc id=posc_$field_name>get</a>";
                //$h .= "htmlallowed {$F->htmlallowed}";
                if ($F->illustrated) {
                    $extend = "urn-img"; // TODO make config
                    $h .= "<div class=\"dropbox-container\" id=\"fileui_illustrate_{$field_name}\" data-destination=\"{$extend}\" data-target=\"content_{$field_name}\"></div>";
                }
                $h .= '</div>';
            break;

            case "timestamp":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'>
				<label class='$requiredCss' for='".$field_name."'>$field_title</label>
				<input type=hidden id='{$field_name}' name=$field_name value='$field_value' $dis class='timestamp'>
				<div class='gcformfield' id='{$field_name}_cal' class='cal' data-source='{$field_name}'></div>
				</div>\n";
            break;

            case "date":
                $h .= "<div class='gcformfield {$field_name}' id='".$field_name."_field'><label class='$requiredCss' for=$field_name>$field_title</label> <input type=text name={$field_name} id='date1' class=datef value='{$field_value}'><br>\n";
            break;

            case "option":
                $NOtitle = '';
                $YEStitle = '';
                $NOvalue = '';
                $YESvalue = '';
                if ($F->values) {
                    $YESvalue = key($F->values[0]);
                    $NOvalue = key($F->values[1]);
                    $YEStitle = $F->values[0][$YESvalue];
                    $NOtitle = $F->values[1][$NOvalue];
                }
                if ($field_value !== null) {
                    if ($field_value == $YESvalue) {
                        $xc1 = 'checked="CHECKED"';
                    } else {
                        $xc0 ='checked="CHECKED"';
                    }
                }
                $h .= "
					<div class='gcformfield {$field_name}' id='{$field_name}_field'>
					<label class='$requiredCss' for='$field_name'>$field_title</label>
					<p><input $xc1 type=radio name='$field_name' value='$YESvalue'> {$YEStitle}</p>
					<p><input $xc0 type=radio name='$field_name' value='$NOvalue'> {$NOtitle}</p>
					</div>\n";
            break;

            case "set":
                $index=0;
                $h .= "
				<div class='gcformfield {$field_name}' id='{$field_name}_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>";
                foreach ($F->options as $ok => $ov) {
                    $index++;
                    $xc1 = '';
                    if ($ok == $field_value) {
                        $xc1 = 'CHECKED';
                    }
                    $h .= "<p><input $xc1 type=radio name='{$field_name}' value='$ok'> {$ov}</p>";
                }
                $h .= "</div>\n";
            break;

            case "image":
                $h .= "<div class='gcformfield' id='".$field_name."_field'>
				<label class='$requiredCss' for='$field_name'>$field_title</label>
				<input type=hidden name='".$field_name."' size=10 class='".$field_name."' value='$field_value'>
				<div data-fieldname='$field_name' class='image64 {$field_name}'></div>
				<script>
				var holder = document.querySelectorAll('div.image64.{$field_name}')[0];
				var image64Data = document.querySelectorAll('input.{$field_name}')[0].value;
				holder.style.background = 'url(' + image64Data + ') no-repeat center';
				</script>
				</div>\n";
            break;
        }
        return $h;
        //return "<div style='margin: 0px; border: 1px solid red;'>" . $h . "</div>\n";
    }
        /*
        case "time":
            if (strlen($field_value)>5)
                $field_value = substr($field_value,0,5);
            $h .= "<label for=$field_name>$field_title</label> <input type=text name={$field_name} id='date2' class=timef value='{$field_value}'><br>\n";
            break;

        case "datetime":
            $date1 = str_replace( "-","-",substr($field_value,0,10) );
            $date2 = substr($field_value,11,5);
            $h .= "<label for=$field_name>$field_title</label>
                <input type=hidden name=$field_name value='$field_value' id={$field_name}_val>
                <input type=text name={$field_name}_date id='date1' class=datef value='{$date1}'> <input type=text class=timef name={$field_name}_time id='date2' value='{$date2}'>\n";
            break;
        */


    private static function wrapfield($name, $prefix=null)
    {
        if ($prefix) {
            return "{$prefix}_{$name}";
        } else {
            return $name;
        }
    }


    public static function category_selectbox($e, $ds, $selected_urns=null, $include_blank=false, $multiple=false, $alwaysExpanded=false, $slideClosed=false)
    {
        if (is_array($e)) {
            $as = $e[1];
            $NAME = $as;
            $TITLE = $e[2];
            $e = $e[0];
        } else {
            if ($e->name) {
                $NAME = $e->name;
            } else {
                $NAME = $e->uri;
            }
            if (is_array($e->title)) {
                $TITLE = $e->title['ru'];
            } else {
                $adminview = $e->adminview;
                $TITLE = $adminview ? $adminview : $e->title;
            }
        }

        if ($multiple) {
            $mult = 'multiple';
            $mult_array_suffix = '[]';
        }
        if ($multiple) {
            $style = "style='max-height: 400px; max-width: 300px; width: 100%; height: 200px;'";
            $help = ' <span style="color: #777; font-size: 10px;">(один или несколько)</span>';
        }
        if ($alwaysExpanded) {
            $style = "style='max-width: 300px; width: 100%;'";
            if (count($ds) > 0) {
                $maxVisibleElements = (count($ds) > 7) ? 7 : count($ds);
            } else {
                $maxVisibleElements = 1;
            }
            $selectSize = "size='{$maxVisibleElements}'";
        }

        if (!$ds) {
            return "<label for='$field_name'>{$TITLE}</label><p>Не из чего выбирать</p>";
        }

        if ($slideClosed) {
            $slideClosedClass = ' slideClosed';
        }
        $h = "
		<div class='formSelector{$slideClosedClass}'>
		<label for={$NAME}>{$TITLE}{$help}</label>
		<select $selectSize $mult $style name='{$NAME}{$mult_array_suffix}' data-type='urn' data-eclass='{$NAME}'>";

        if ($include_blank !== false) {
            $blankValue = 'NULL';
            if ($include_blank === true) {
                $defaultTitle = 'без привязки';
            } else {
                $defaultTitle = $include_blank;
            }
            if (!$selected_urns) {
                $nullSelected = 'selected';
            }
            if (is_array($include_blank)) {
                // TODO rework it

                $k = key($include_blank);
                $blankValue = $k;
                $defaultTitle = $include_blank[$k];
            }
            $h .= "<option value='$blankValue' {$nullSelected}>{$defaultTitle}</option>\n";
        }

        /* YOU HAVE TO PROVIDE DATASET FOR SELECT. AUTO LOADING IS DISABLED
        */
        if (!$ds) {
            //if ($e->has_field('ordered'))
            //	$h .= load_taxonomy($e, $selected_urns, null, 0);
            throw new Exception('YOU HAVE TO PROVIDE DATASET FOR SELECT. AUTO LOADING IS DISABLED');
        } else {
            if ($ds->entitymeta->has_field('_parent')) {
                $ds->treesort();
            } // TREESORT
            $h .= dataset2htmloptions($ds, $selected_urns);
        }
        $h .= "</select></div>";
        return $h;
    }

    public static function treeSelector($E, $name, $selected, $options)
    {
        if (!$options['allowNotSelected']) {
            $options['allowNotSelected'] = false;
        }
        $h = "<select name='$name' id='$name' data-selector='$name'>";
        if ($options['allowNotSelected']) {
            $h .= "<option value=''>--</option>\n";
        }
        $m = new Message();
        $m->action = "load";
        $m->urn = (string) $E;
        $m->order = $E->defaultorder;
        $m->last = 1000; // !!!
        $ds = $m->deliver();
        $ds->treesort();
        $h .= dataset2htmloptions($ds, array("urn-{$E->name}-{$selected}"), 0);
        $h .= "</select>";
        return $h;
    }

    // _parent is field not entity!!
    public static function parentSelector($e, $parent_id, $selfId)
    {
        $h = "<label for=parent>Родитель</label>\n<select name='_parent'>";
        $h .= "<option value=''>без родителя</option>\n";
        $m = new Message();
        $m->action = "load";
        $m->urn = 'urn:'.$e->name;
        $m->order = $e->defaultorder;
        // $m->_parent = 'NULL';
        //$m->_parent = $parent_id;
        //if ($e->has_field('ordered')) $m->order = array('ordered'=>'ASC');
        $m->last = 1000; // !!!
        $ds = $m->deliver();
        $ds->treesort(); // TREESORT
        /*
        foreach ($ds as $p)
        {
            $sel = '';
            if ($parent_id == $p->id) $sel = 'SELECTED';
            $h .= "<option data-type='treeroot' $sel value='{$p->id}'>{$p->title}</option>\n";
        }
        */
        $h .= dataset2htmloptions($ds, array("urn:{$e->name}:{$parent_id}"), 0, $selfId);
        $h .= "</select>";
        return "<div style='margin: 5px; border: 0px solid red;'>" . $h . "</div>\n";
    }
} // form class end


// TODO make in in doc xml, make selected later by xpath not on generate phase (or even in js admin)
/**
includes _parent deep lists. limit recursion on 7 level
*/
function dataset2htmloptions($ds, $selected_urns, $level=0, $selfId)
{
    //println($ds);
    //println($selected_urns);
    $h = '';
    foreach ($ds as $root) {
        $prefix = '';
        for ($i=0; $i < $root->_level; $i++) {
            if ($i>=1) {
                $prefix .= "-";
                $suf = ' ';
            }
        }

        if ($selected_urns && in_array($root->urn, $selected_urns)) {
            $sel = 'SELECTED';
        } else {
            $sel = '';
        }
        $t = '';
        //if ($root->uri) $t .= "/{$root->uri}/ ";
        if ($root->adminview) {
            $t .= $root->adminview;
        } elseif ($root->title) {
            $t .= $root->title;
        } //  {$root->id} {$root->_parent}
        elseif ($root->uri) {
            $t .= $root->uri;
        } else {
            $t .= $root->urn;
        }
        $disabled = '';
        if ($selfId == $root->id) {
            $disabled = 'disabled="disabled"';
        }
        // style='background: red; color: green;'
        if (mb_strlen($t) > 40) {
            $t = mb_substr($t, 0, 30).'...'.mb_substr($t, -10);
        }
        $h .= "<option $disabled data-level='{$root->_level}' $sel value='{$root->urn}'>{$prefix}{$suf}{$t}</option>\n"; // {$root->id}/{$root->_parent}
        /**
        DISABLE RECURSIVE LOADING
        if ($ds->entity->has_field('_parent')) $h .= load_taxonomy($ds->entity, $selected_urns, $root->id, $level, $selfId);
        */
    }

    return $h;
}



function load_taxonomy($e, $selected_urns, $parent, $level, $selfId)
{
    $level++;
    if ($level > 7) {
        return '';
    }
    try {
        $m = new Message();
        $m->action = "load";
        $m->last = 1000; // !!!
        $m->urn = $e->urn;
        if ($e->has_field('_parent')) {
            if (!$parent) {
                $m->_parent = 'NULL';
            } else {
                $m->_parent = $parent;
            }
        }
        if ($e->has_field('ordered')) {
            $m->order = array('ordered'=>'ASC');
        }
        $ds = $m->deliver();
        $h = dataset2htmloptions($ds, $selected_urns, $level, $selfId);
        return $h;
    } catch (Exception $e) {
        print $e;
    }
}
