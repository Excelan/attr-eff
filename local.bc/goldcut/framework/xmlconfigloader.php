<?php
function groupsfeed(&$n, &$groups, &$virtualgroups, &$groupnames)
{
	$pname = null;
	if (($pn = $n->parentNode) && $pn->tagName == 'group')
	{
		$fname = $n->getAttribute('name');
		if (!$fname) // not field, entity
		{
			$enamebase = $n->getAttribute('entity');
			$enameas = $n->getAttribute('as');
			if ($enameas) $fname = $enameas;
			else $fname = $enamebase;
		}
		$pname = $n->parentNode->getAttribute('name');
		$ptitle = $n->parentNode->getAttribute('title');
		if (!$fbasename) $fbasename = $fname;
		if ($pname)
		{
			$fname = $pname.'_'.$fname;
			$groupnames[$pname] = $ptitle; // group name > group title (a[groupname])
			$groups[$fname] = $ptitle; // (a[field_name])
		}
		else // if ($fbasename && !$pname)
		{
			$virtualgroups[$fbasename] = $ptitle; // field name > is in virtual (no name_ prefix) group title
		}
	}
	return $pname;
}


function parseStatusFromDom(DOMElement $n)
{
	$f = array();
	$f['uid'] = SerialID::next();
	$f['name'] = $n->getAttribute('name');
	$f['title'] = $n->getAttribute('title');
	$f['default'] = txt2boolean($n->getAttribute('default'));
	//println($f);
	return $f;
}

function parseFieldFromDom(DOMElement $n)
{
	$f = array();
	$f['uid'] = SerialID::next();
	$f['name'] = trim(preg_replace('/[^\x20-\x7E]/', '', $n->getAttribute('name')));
	if ($n->getAttribute('name') != $f['name']) throw new Exception("Illegal chars in xml field [".$n->getAttribute('name').']. Cleared: ['.$f['name'].']');
	$f['role'] = $n->getAttribute('role');
	$f['title'] = $n->getAttribute('title');
	$f['type'] = $n->getAttribute('type');
	$f['default'] = $n->getAttribute('default');
	$f['raw'] = txt2boolean($n->getAttribute('raw'));
	$f['system'] = txt2boolean($n->getAttribute('system'));
	$f['virtual'] = txt2boolean($n->getAttribute('virtual'));
	$f['noneditable'] = txt2boolean($n->getAttribute('noneditable'));
	$f['disabled'] = txt2boolean($n->getAttribute('disabled'));
	$f['usereditable'] = txt2boolean($n->getAttribute('usereditable')); // TODO load E.fields by ids not names (overlap)
	if (in_array($f['type'], array('integer','float','money','string')))
	{
		$f['units'] = $n->getAttribute('units');
	}
	if ($f['type'] == 'timestamp')
	{
		$f['createDefault'] = $n->getAttribute('createDefault');
		$f['updateDefault'] = $n->getAttribute('updateDefault');
	}
	if ($f['type'] == 'richtext')
	{
		$f['illustrated'] = txt2boolean($n->getAttribute('illustrated'));
		$f['autoparagraph'] = txt2boolean($n->getAttribute('autoparagraph'));
		$f['nofollow'] = txt2boolean($n->getAttribute('nofollow'));
		$f['htmlallowed'] = $n->getAttribute('htmlallowed');
		if (!$f['htmlallowed'])
		{
			$f['htmlallowed'] = str_replace('/','|', $GLOBALS['CONFIG']['HTML']['ALLOWED']);
		}
	}
	if ($f['type'] == 'option') {
		$nvs = $n->getElementsByTagName('value');
		$tx = array();
		$valiter = 0;
		foreach ($nvs as $nv) {
			$vname = $nv->getAttribute('name');
			$vtitle = $nv->getAttribute('title');
			$tx[$valiter++] = array($vname => $vtitle);
			if ($valiter > 2) throw new Exception("Option Field can have only 2 values");
		}
		$f['values'] = $tx;
	}
	if ($f['type'] == 'set') {
		$nvs = $n->getElementsByTagName('value');
		$tx = array();
		$valiter = 0;
		foreach ($nvs as $nv) {
			$vname = $nv->getAttribute('name');
			$vtitle = $nv->getAttribute('title');
			$tx[$vname] = $vtitle;
			if ($valiter++ > 200) throw new Exception("Set Field can have only 200 values");
		}
		$f['options'] = $tx;
	}
	//println($f,1,TERM_RED);
	return $f;
}

class XMLConfigLoader
{
	private static $filepath;

	public static function load($filepath, $type, $system=false)
	{
		self::$filepath = $filepath;
		$doc = new DOMDocument();
		$doc->load($filepath);
		if ($type == 'entity') self::loadEntity($doc, $system);
	}

	private static function loadEntity($doc, $system=false)
	{
		if (!$doc->documentElement) throw new Exception("Error in config file ".self::$filepath);
		$entitynameLegacy = $doc->documentElement->getAttribute('name');
        if ($entitynameLegacy) throw new Exception("Legacy name used in ".self::$filepath);
        $entityCode = $doc->documentElement->getAttribute('code');
		$prototype = $doc->documentElement->getAttribute('prototype');
    if (!$prototype) throw new Exception("Blank prototype in ".debugDom($doc));
		$prototype_cleared = trim(preg_replace('/[^\x20-\x7E]/', '', $prototype));
		if ($prototype != $prototype_cleared) throw new Exception("Illegal chars in xml prototype [{$prototype}]. Cleared: [{$prototype_cleared}]");
		$euid = (int) $doc->documentElement->getAttribute('uid');
		if (ENV == 'DEVELOPMENT' && $GLOBALS['CONFIG']['ENTITY'][$euid] && !$GLOBALS['CONFIG']['ENTITY'][$euid]->is_system()) throw new Exception("Duplicate Entity UID ".self::$filepath." already used by ".$GLOBALS['CONFIG']['ENTITY'][$euid]->name);
		$manager = $doc->documentElement->getAttribute('manager');
		$passportTitle = $doc->getElementsByTagName('title');
		$domx = new DOMXPath($doc);
		$entries = $domx->evaluate("//passport/title");
		foreach ($entries as $entry) {
			$entityTitle = $entry->nodeValue;
		}

		$field_metas = array();
		$hasone_entities = array();
		$useone_entities = array();
		$belongsto_entities = array();
		$hasmany_entities = array();
		$usemany_entities = array();
		$lists = array();
		$statuses = array();
		$usereditfields = array();
		$astitles = array();
		$usereditfieldsOrdered = array();
		$allOrdered = array();
		$required = array();
		$extendstructure = array();

		// ORDERED ALL
		$domx = new DOMXPath($doc);
		$entries = $domx->evaluate("//states/status");
		foreach ($entries as $n)
		{
			$fname = $n->getAttribute('name');
			array_push($allOrdered, $fname);
			$userEditable = $n->getAttribute('usereditable');
			if ($userEditable == 'yes') array_push($usereditfieldsOrdered, $fname);
		}

        $multy_lang = array();
        $entries = $domx->evaluate("//international//language");
        foreach ($entries as $n)
        {
            $lang = $n->getAttribute('code');
            array_push($multy_lang, $lang);
        }

		$entries = $domx->evaluate("//structure//*");
		foreach ($entries as $n)
		{
			$fname = $n->getAttribute('name');
			if ($fname) // field
			{
				if (($pn = $n->parentNode) && $pn->tagName == 'group')
				{
					$pname = $n->parentNode->getAttribute('name');
					if ($pname) $fname = $pname.'_'.$fname;
				}
				array_push($allOrdered, $fname);
				$userEditable = $n->getAttribute('usereditable');
				if ($userEditable == 'yes') array_push($usereditfieldsOrdered, $fname);
				if ($n->getAttribute('required') == 'yes') array_push($required, $fname);
			}
			else // entity
			{
				$enamebase = $n->getAttribute('entity');
				$enameas = $n->getAttribute('as');
				$enameas_cleared = trim(preg_replace('/[^\x20-\x7E]/', '', $enameas));
				if ($enameas != $enameas_cleared) throw new Exception("Illegal chars in xml as= [{$enameas}]. Cleared: [{$enameas_cleared}]");
				if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
				if ($enameas) $ename = array($enameas => $enamebase);
				elseif ($enamebase) $ename = $enamebase;
				else continue;
				/*
				if (($pn = $n->parentNode) && $pn->tagName == 'group')
				{
					$pname = $n->parentNode->getAttribute('name');
					if ($pname) $ename = $pname.'_'.$ename;
				}
				*/
				array_push($allOrdered, $enameas);
				$userEditable = $n->getAttribute('usereditable');
				if ($userEditable == 'yes') array_push($usereditfieldsOrdered, $ename);
				if ($n->getAttribute('required') == 'yes') array_push($required, $ename);
			}
		}
		$entries = $domx->evaluate("//lists/list");
		foreach ($entries as $n)
		{
			$fname = $n->getAttribute('name');
			$fname_cleared = trim(preg_replace('/[^\x20-\x7E]/', '', $fname));
			if ($fname != $fname_cleared) throw new Exception("Illegal chars in xml as= [{$fname}]. Cleared: [{$fname_cleared}]");
			array_push($allOrdered, $fname);
			$userEditable = $n->getAttribute('usereditable');
			if ($userEditable == 'yes') array_push($usereditfieldsOrdered, $fname);
		}
		//if ($entityname == 'user') println($allOrdered);
		//if ($entityname == 'user') println($usereditfieldsOrdered,1,TERM_GREEN);

		// ORDERED USER EDITABLES
		/*
		$domx = new DOMXPath($doc);
		$entries = $domx->evaluate("//*[@usereditable]"); // //structure/*[@usereditable]
		foreach ($entries as $n) {
			$userEditable = $n->getAttribute('usereditable');
			if ($userEditable != 'yes') continue;
			//$nodename = $n->nodeName;
			$fname = $n->getAttribute('name');
			if ($fname) // field
			{
				array_push($usereditfieldsOrdered, $fname);
			}
			else // entity
			{
				$enamebase = $n->getAttribute('entity');
				$enameas = $n->getAttribute('as');
				if ($enameas) $ename = array($enameas => $enamebase);
				else $ename = $enamebase;
				array_push($usereditfieldsOrdered, $ename);
			}
		}
		*/

		// GET FIELDS
        $lang_field_metas = array();
		$nds = $doc->getElementsByTagName('field');
		foreach ($nds as $n)
		{
			$fname = $n->getAttribute('name');
			//$fbasename = $n->getAttribute('base');
			$userEditable = $n->getAttribute('usereditable');
            $international = ($n->getAttribute('role') == 'international') ? true : false; // TODO !!!
			$pname = groupsfeed($n, $groups, $virtualgroups, $groupnames);
			if ($pname)
				$fsname = array($pname.'_'.$fname => $fname);
			else
				$fsname = $fname;
			//if ($userEditable == 'yes') array_push($usereditfields, $pname.'_'.$fname);
			if (LEGACY_CONFIG_FIELDS_ASPHP !== true) // create & register FieldMeta
			{
				$f = parseFieldFromDom($n);
				$F = new FieldMeta($f);
				$GLOBALS['CONFIG']['FIELD'][$f['uid']] = $F;
				$GLOBALS['CONFIG']['FIELDINENTITY'][$euid][$F->name] = $F;
				//if (Field::exists($f['name'])) println($f['name']);//Log::debug("Duplicated name of field ".anyToString($f), 'legacy');
				array_push($field_metas, $f['uid']);
				if ($international) $lang_field_metas[] = $f['uid'];
			}
			else
			{
				array_push($field_metas, $fsname);
				if ($international) $lang_field_metas[] = $fname;
			}
		}
		foreach ($doc->getElementsByTagName('status') as $n) {
			$enamebase = $n->getAttribute('name');
			array_push($statuses, $enamebase);
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $enamebase);
			if (LEGACY_CONFIG_FIELDS_ASPHP !== true) // create & register Status
			{
				$f = parseStatusFromDom($n);
				$S = new StatusMeta($f);
				$GLOBALS['CONFIG']['STATUS'][$f['uid']] = $S;
				$GLOBALS['CONFIG']['STATUSINENTITY'][$euid][$S->name] = $S;
			}
		}
		foreach ($doc->getElementsByTagName('hasmany') as $n) {
			$enamebase = $n->getAttribute('entity');
            $enameas = $n->getAttribute('as');
            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
            if ($enameas) $ename = array($enameas => $enamebase);
            else $ename = $enamebase;
			array_push($hasmany_entities, $ename);
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $ename);
		}
		foreach ($doc->getElementsByTagName('usemany') as $n) {
			$enamebase = $n->getAttribute('entity');
            $enameas = $n->getAttribute('as');
            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
            if ($enameas) $ename = array($enameas => $enamebase);
            else $ename = $enamebase;
			if (!$enamebase) throw new Exception('No attribute entity="" in usemany relation');
			array_push($usemany_entities, $ename);
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $ename);
		}
		foreach ($doc->getElementsByTagName('belongsto') as $n) {
			$enamebase = $n->getAttribute('entity');
			$enameas = $n->getAttribute('as');
            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
			if ($enameas) $ename = array($enameas => $enamebase);
			else $ename = $enamebase;
			if (!$enamebase) throw new Exception('No attribute entity="" in belongsto relation');
			array_push($belongsto_entities, $ename);
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $ename);
		}
		$nds = $doc->getElementsByTagName('hasone');
		foreach ($nds as $n) {
			$enamebase = $n->getAttribute('entity');
			if (!$enamebase) throw new Exception('No attribute entity="" in hasone relation');
			$enameas = $n->getAttribute('as');
            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
			$astitle = $n->getAttribute('title');
			groupsfeed($n, $groups, $virtualgroups, $groupnames);
			if ($astitle) $astitles[$enameas] = $astitle;
			if ($enameas) $ename = array($enameas => $enamebase);
			else $ename = $enamebase;
			array_push($hasone_entities, $ename);
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $ename);
		}
		$nds = $doc->getElementsByTagName('useone');
		foreach ($nds as $n) {
			$enamebase = $n->getAttribute('entity');
			if (!$enamebase) throw new Exception('No attribute entity="" in useone relation');
			$enameas = $n->getAttribute('as');
            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
			//if ($enameas) throw new Exception("Dont use AS in useone. Hasone only");
			$astitle = $n->getAttribute('title');
			groupsfeed($n, $groups, $virtualgroups, $groupnames);
			if ($astitle) $astitles[$enameas] = $astitle;
			if ($enameas) $ename = array($enameas => $enamebase);
			else $ename = $enamebase;
			array_push($useone_entities, $ename);
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $ename);
		}
		$nds = $doc->getElementsByTagName('list');
		foreach ($nds as $n) {
			$enamebase = $n->getAttribute('entity');
			$enameas = $n->getAttribute('name');
			$ns = (int) $n->getAttribute('ns');
			$listtitle = $n->getAttribute('title');
			$membership = $n->getAttribute('membership'); // shared or exclusive
			$graph = ($n->getAttribute('graph') == 'true') ? true : false;
			$reverse = $n->getAttribute('reverse');
			$notify = $n->getAttribute('notify');
			array_push($lists, array('ns'=>$ns, 'entity'=>$enamebase, 'name'=>$enameas, 'title'=>$listtitle, 'notify'=>$notify, 'graph'=>$graph, 'reverse'=>$reverse, 'membership'=>$membership, 'order' => array('id'=>'desc') ));
			//$userEditable = $n->getAttribute('usereditable');
			//if ($userEditable == 'yes') array_push($usereditfields, $enameas);
		}
		$reverserelated = array();
		foreach ($doc->getElementsByTagName('reverserelated') as $n) {
			$enameas = $n->getAttribute('name');
			array_push($reverserelated, $enameas);
		}
		$adminfields = array();
		foreach ($doc->getElementsByTagName('column') as $n) {
			$col = $n->getAttribute('selector');
			array_push($adminfields, $col);
		}

        /*
         * LEGACY
		$mediaoptions = array();
		foreach ($doc->getElementsByTagName('image') as $n) {
			$o = array();
			$name = $n->getAttribute('name');
			$o[] = $n->getAttribute('size');
			($n->getAttribute('fx')) ? ($o[] = 'fx_'.$n->getAttribute('fx')) : null;
			($n->getAttribute('crop') == 'yes') ? ($o[] = 'crop') : null;
			($n->getAttribute('trim') == 'yes') ? ($o[] = 'trim') : null;
			($n->getAttribute('watermark') == 'yes') ? ($o[] = 'watermark') : null;
			$complexsize = join(':',$o);
			$mediaoptions[$name] = $complexsize;
		}
        */

		$imagesettings = array();
		$imagesettingsImage = $domx->evaluate("//imagesettings/mainimage");
		foreach ($imagesettingsImage as $mi)
		{
			$paradigm = $mi->getAttribute('paradigm');
			$hd = $mi->getAttribute('hd');
			$watermark = $mi->getAttribute('watermark');
			$imagesettings['mainimage'] = array();
			$imagesettings['mainimage']['paradigm'] = $paradigm;
			$imagesettings['mainimage']['hd'] = $hd;
			foreach ($mi->getElementsByTagName('size') as $sx) {
				$size = domGetImageDimSize($sx);
				//println($size);
				$imagesettings['mainimage']['size'] = $size;
				$verticalfixed = $sx->getAttribute('verticalfixed');
				$horizontalfixed = $sx->getAttribute('horizontalfixed');
				$verticalmin = $sx->getAttribute('verticalmin');
				$verticalmax = $sx->getAttribute('verticalmax');
				$horizontalmin = $sx->getAttribute('horizontalmin');
				$horizontalmax = $sx->getAttribute('horizontalmax');
				if ($verticalfixed) $imagesettings['mainimage']['size']['verticalfixed'] = $verticalfixed;
				if ($horizontalfixed) $imagesettings['mainimage']['size']['horizontalfixed'] = $horizontalfixed;
				if ($verticalmin) $imagesettings['mainimage']['size']['verticalmin'] = $verticalmin;
				if ($verticalmax) $imagesettings['mainimage']['size']['verticalmax'] = $verticalmax;
				if ($horizontalmin) $imagesettings['mainimage']['size']['horizontalmin'] = $horizontalmin;
				if ($horizontalmax) $imagesettings['mainimage']['size']['horizontalmax'] = $horizontalmax;
			}
		}
		$imagesettingsPreviews = $domx->evaluate("//imagesettings/previews");
		foreach ($imagesettingsPreviews as $mi)
		{
			$paradigm = $mi->getAttribute('paradigm');
			$hd = $mi->getAttribute('hd');
			$watermark = $mi->getAttribute('watermark');
			$reframe = $mi->getAttribute('reframe');
			$verticalfixed = $mi->getAttribute('verticalfixed');
			$horizontalfixed = $mi->getAttribute('horizontalfixed');
			$verticalmin = $mi->getAttribute('verticalmin');
			$verticalmax = $mi->getAttribute('verticalmax');
			$horizontalmin = $mi->getAttribute('horizontalmin');
			$horizontalmax = $mi->getAttribute('horizontalmax');
			$imagesettings['previews'] = array();
			/*
			if ($verticalfixed) $imagesettings['previews']['verticalfixed'] = $verticalfixed;
			if ($horizontalfixed) $imagesettings['previews']['horizontalfixed'] = $horizontalfixed;
			if ($verticalmin) $imagesettings['previews']['verticalmin'] = $verticalmin;
			if ($verticalmax) $imagesettings['previews']['verticalmax'] = $verticalmax;
			if ($horizontalmin) $imagesettings['previews']['horizontalmin'] = $horizontalmin;
			if ($horizontalmax) $imagesettings['previews']['horizontalmax'] = $horizontalmax;
			*/
			$imagesettings['previews']['paradigm'] = $paradigm;
			$imagesettings['previews']['hd'] = $hd;
			$imagesettings['previews']['sizes'] = array();
			foreach ($mi->getElementsByTagName('size') as $sx) {
				$sizename = $sx->getAttribute('name');
				if ($sizename == 'thumbnail') throw new Exception('Dont use thumbnail as preview image name');
				$base64store = $sx->getAttribute('base64');
				$base64store = txt2boolean($base64store);
				$size = domGetImageDimSize($sx);
				$imagesettings['previews']['sizes'][$sizename] = $size;
				$imagesettings['previews']['sizes'][$sizename]['base64'] = $base64store;
			}
		}
		if ($manager == 'Photo' and !$imagesettings) throw new Exception("$entityname managed by Photo by have no xml imagesettings section");

		$directmanage = true;
        $adminadd = true;
		$translit = null;
		$options = array();
		foreach ($doc->getElementsByTagName('aparam') as $n) {
			$oname = $n->getAttribute('name');
			$oval = $n->getAttribute('value');
			if ($n->getAttribute('type')=="boolean") $oval = txt2boolean($oval);
			$options[$oname] = $oval;
			if ($oname == 'directmanage') $directmanage = $oval;
			if ($oname == 'adminadd') $adminadd = $oval;
			if ($oname == 'clonable') $clonable = $oval;
			if ($oname == 'attributed') $attributed = $oval;
			if ($oname == 'translit' && $oval == 'legacytitle2uri') $translit = array('title'=>'uri');
			if ($oname == 'extendstructure')
			{
				$extendstructure = array($oval); // extended structure
				array_unshift($allOrdered, 'extended');
			}
		}
		foreach ($doc->getElementsByTagName('param') as $n) {
			$oname = $n->getAttribute('name');
			$oval = $n->getAttribute('value');
			if ($n->getAttribute('type')=="boolean") $oval = txt2boolean($oval);
			if ($oname == 'treeview') $treeview = $oval;
		}
		$indexes = array();
		foreach ($doc->getElementsByTagName('index') as $n) {
			array_push($indexes, $n->getAttribute('column'));
		}
		$uniqs = array();
		foreach ($doc->getElementsByTagName('unique') as $n) {
			array_push($uniqs, $n->getAttribute('column'));
		}
		$defaultorder = array();
		foreach ($doc->getElementsByTagName('by') as $n) { // defaultorder
			$field = (string) $n->getAttribute('field');
			$order = (string) $n->getAttribute('order');
			$defaultorder[$field] = $order;
		}
		$searchtextin = array();
		foreach ($doc->getElementsByTagName('searchin') as $n) {
			array_push($searchtextin, $n->getAttribute('column'));
		}
		$adminsearchtextin = array();
		foreach ($doc->getElementsByTagName('adminsearchin') as $n) {
			array_push($adminsearchtextin, $n->getAttribute('column'));
		}
		// REPLACE general $usereditfields with right ORDERED
		$usereditfields = $usereditfieldsOrdered;
        $GLOBALS['CONFIG']['ENTITY'][$euid] = new EntityMeta(array('uid' => $euid,
            'class' => $manager,
            'prototype' => $prototype,
            'name' => $entitynamelegacy,
            'code' => $entityCode,
            'title' => array('ru' => $entityTitle, 'en' => $entityTitle),
            "multy_lang" => $multy_lang,
            "lang_field_metas" => $lang_field_metas,
            'statuses' => $statuses,
            'field_metas' => $field_metas,
            "has_one" => $hasone_entities,
            "use_one" => $useone_entities,
            "has_many" => $hasmany_entities,
            'usemany_entities' => $usemany_entities,
            "belongs_to" => $belongsto_entities,
            'lists' => $lists,
            'reverserelated' => $reverserelated,
            'adminfields' => $adminfields,
            'imagesettings' => $imagesettings,
            'options' => $options,
            'directmanage' => $directmanage,
            'adminadd' => $adminadd,
            'translit' => $translit,
            'index' => $indexes,
            'checkunique' => $uniqs,
            'usereditfields' => $usereditfields,
            'defaultorder' => $defaultorder,
            'clonable' => $clonable,
            'searchtextin' => $searchtextin,
            'adminsearchtextin' => $adminsearchtextin,
            'groupnames' => $groupnames,
            'groups' => $groups,
            'virtualgroups' => $virtualgroups,
            'astitles' => $astitles,
            'allOrdered' => $allOrdered,
            'required' => $required,
            'treeview' => $treeview,
            'extendstructure' => $extendstructure,
            'attributed' => $attributed,
            'system' => $system));
            //'mediaoptions' => $mediaoptions,
	}

    public static function loadoverlay($filepath, $type) // $type == 'entity'
    {
        $field_metas = array();
        $hasone_entities = array();
        $useone_entities = array(); // TODO !!!
        $belongsto_entities = array();
        $hasmany_entities = array();
        $usemany_entities = array(); // TODO !!!
        $lists = array();
        $statuses = array();

        $doc = new DOMDocument();
        $doc->load($filepath);
        if (!$doc->documentElement) throw new Exception("Error in config file ".$filepath);
        $entityname = $doc->documentElement->getAttribute('name');
        if ($entityname) throw new Exception("LEGACY NAME IN OVERLAY in ".debugDom($doc));
        $prototype = $doc->documentElement->getAttribute('prototype');
        if (!$prototype) throw new Exception("Blank prototype in overlay in ".debugDom($doc));

		$prototypeParts = explode(":",$prototype);
		$patchEntities = [];
		if ($prototypeParts[1] == '*' && $prototypeParts[2] == '*')
		{
			foreach (Entity::each_entity() as $entity)
			{
				if ($entity->prototype->getInDomain() == $prototypeParts[0])
					array_push($patchEntities, (string) $entity->prototype);
			}
		}
		else
		{
			array_push($patchEntities, $prototype);
		}

		foreach ($patchEntities as $prototype)
		{
			$E = Entity::ref($prototype);

			$E->overlayed = true;

			$field_metas = [];
	        $nds = $doc->getElementsByTagName('field');
	        foreach ($nds as $n)
	        {
				if (LEGACY_CONFIG_FIELDS_ASPHP !== true) // create & register FieldMeta
				{
					$f = parseFieldFromDom($n);
					$F = new FieldMeta($f);
					$GLOBALS['CONFIG']['FIELD'][$f['uid']] = $F;
				}
	            $fname = $n->getAttribute('name');
	            array_push($field_metas, $fname);
							$GLOBALS['CONFIG']['FIELDINENTITY'][$E->uid][$F->name] = $F;
	        }
	        $E->extend('field_metas', $field_metas);

			$statuses = [];
	        foreach ($doc->getElementsByTagName('status') as $n) {
				if (LEGACY_CONFIG_FIELDS_ASPHP !== true) {
					$f = parseStatusFromDom($n);
					$S = new StatusMeta($f);
					$GLOBALS['CONFIG']['STATUS'][$f['uid']] = $S;
				}
	            $enamebase = $n->getAttribute('name');
	            array_push($statuses, $enamebase);
							$GLOBALS['CONFIG']['STATUSINENTITY'][$E->uid][$S->name] = $S;
	        }
	        $E->extend('statuses', $statuses);

			$hasmany_entities = [];
	        foreach ($doc->getElementsByTagName('hasmany') as $n) {
	            $enamebase = $n->getAttribute('entity');
	            array_push($hasmany_entities, $enamebase);
	        }
	        $E->extend('has_many', $hasmany_entities);

			$belongsto_entities = [];
			foreach ($doc->getElementsByTagName('belongsto') as $n) {
				$enamebase = $n->getAttribute('entity');
	            $enameas = $n->getAttribute('as');
	            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
	            if ($enameas) $ename = array($enameas => $enamebase);
	            else $ename = $enamebase;
				if (!$enamebase) throw new Exception('No attribute entity="" in belongsto relation');
	            array_push($belongsto_entities, $ename);
	        }
	        $E->extend('belongs_to', $belongsto_entities);

			$hasone_entities = [];
			$nds = $doc->getElementsByTagName('hasone');
	        foreach ($nds as $n) {
				$enamebase = $n->getAttribute('entity');
	            $enameas = $n->getAttribute('as');
	            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
	            if ($enameas) $ename = array($enameas => $enamebase);
	            else $ename = $enamebase;
				if (!$enamebase) throw new Exception('No attribute entity="" in hasone relation');
	            array_push($hasone_entities, $ename);
	        }
	        $E->extend('has_one', $hasone_entities);

			$hasone_entities = [];
			$nds = $doc->getElementsByTagName('useone');
	        foreach ($nds as $n) {
				$enamebase = $n->getAttribute('entity');
	            $enameas = $n->getAttribute('as');
	            if (!$enameas) $enameas = str_replace(':','',$enamebase); // !
	            if ($enameas) $ename = array($enameas => $enamebase);
	            else $ename = $enamebase;
				if (!$enamebase) throw new Exception('No attribute entity="" in useone relation');
	            array_push($hasone_entities, $ename);
	        }
	        $E->extend('use_one', $hasone_entities);

			// lists overlay
			$lists = [];
	        $nds = $doc->getElementsByTagName('list');
	        foreach ($nds as $n) {
	            $enamebase = $n->getAttribute('entity');
	            $enameas = $n->getAttribute('name');
	            $ns = (int) $n->getAttribute('ns');
	            $listtitle = $n->getAttribute('title');
	            $membership = $n->getAttribute('membership'); // shared or exclusive
	            $graph = ($n->getAttribute('graph') == 'true') ? true : false;
	            $reverse = $n->getAttribute('reverse');
	            $notify = $n->getAttribute('notify');
	            array_push($lists, array('ns'=>$ns, 'entity'=>$enamebase, 'name'=>$enameas, 'title'=>$listtitle, 'notify'=>$notify, 'graph'=>$graph, 'reverse'=>$reverse, 'membership'=>$membership, 'order' => array('id'=>'desc') ));
	        }
	        $E->extend('lists', $lists);
		}


    }
}
/**
function processStructure($x)
{
	foreach($x->childNodes as $node)
	{
		if ($node->nodeType == 1)
		{
			$name = $node->getAttribute('name');
			println("$node->tagName $name",2,TERM_VIOLET);
			if ($node->tagName == 'group')
			{
				foreach($node->childNodes as $nodeinner)
				{
					if ($nodeinner->nodeType == 1)
					{
						$name = $nodeinner->getAttribute('name');
						println("$nodeinner->tagName $name",3,TERM_VIOLET);
					}
				}
			}
		}
	}
}
foreach($doc->documentElement->childNodes as $node)
{
	if ($node->nodeType == 1)
	{
		println("$node->tagName",1,TERM_GREEN);
		if ($node->tagName == 'structure') processStructure($node);
	}
}
		*/
?>