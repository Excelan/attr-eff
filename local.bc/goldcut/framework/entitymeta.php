<?php

class entitymeta
{

    public $class = "Content";
    public $manager;
    public $uid;
    public $code;
    public $legacyname;
    public $name;
    public $prototype;
    public $title;
    public $field_metas;
    private $has_one;
    private $use_one;
    private $has_many;
    private $use_many;
    private $belongs_to;
    private $category;
    private $multy_lang;
    public $lang_field_metas;
    private $communitydata = false;
    public $statuses;
    private $related;
    public $reverserelated = array();
    public $compound_of;
    public $media;
    public $mediaoptions;
    public $imagesettings;
    public $urn;
    public $directmanage = true;
    public $adminadd = true;
    public $container;
    public $treeview;
    public $translit;
    public $alias;
    public $checkunique;
    public $index;
    public $defaultorder;
    public $adminfields;
    public $usereditfields;
    public $cacheMemoryEnabled;
    public $lists = array();
    public $groupby = array();
    public $extendstructure = array();
    public $searchtextin;
    public $adminsearchtextin;
    public $clonable;
    public $groupnames;
    public $groups;
    public $virtualgroups;
    public $options = array();
    public $astitles;
    public $allOrdered;
    public $required;
    public $attributed;
    public $overlayed;
    private $system;


    public function getClass()
    {
        try {
            $Class = $this->class;
            $O = new $Class($this->class);
            return $O;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTableName()
    {
        return str_replace(':', '_', (string)$this->prototype);
    }

    public function getFolderName()
    {
        return str_replace(':', '', (string)$this->prototype);
    }

    public function getAlias()
    {
        return str_replace(':', '', (string)$this->prototype);
    }

    public function __construct($config)
    {
        foreach ($config as $option => $value) {
            $this->$option = $value;
        }
        //$this->checkConsistency();
        $this->manager = $this->getClass();
        $this->legacyname = $this->name;
        $this->name = $this->prototype; // string:::
        $this->urn = "urn:".$this->prototype;
        $this->prototype = new Prototype($this->prototype);
//		$this->name = $this->getTableName(); // NEW
    }

    public function extend($feature, $extv)
    {
        foreach ($extv as $v) {
            array_push($this->$feature, $v);
            array_push($this->allOrdered, $v);
            //array_push($this->usereditfields, $v);
        }
    }

    public function has_extended_structure()
    {
        if (count($this->extendstructure)) {
            return true;
        }
        return false;
    }

    public function checkConsistency()
    {
        $reportOnConsistency = array();
        $preerrortxt = "Inconsistency in entity [{$this->name}].";
        foreach ($this->statuses as $status) {
            if (!Status::exists($status)) {
                array_push($reportOnConsistency, "{$preerrortxt} Status [$status] not exists");
            }
        }
        foreach ($this->field_metas as $f) {
            if (is_array($f)) {
                $f = $f[key($f)];
            } // ex {"socialprofiles_fb":"fb"}
            if (!Field::exists($f)) {
                array_push($reportOnConsistency, "{$preerrortxt} Field [$f] not exists");
            }
        }
        foreach ($this->belongs_to as $e) {
            if (!Entity::exists($e)) {
                array_push($reportOnConsistency, "{$preerrortxt} Entity [".json_encode($e)."] not exists for belongs_to relation");
            }
        }
        foreach ($this->has_one as $e) {
            if (!Entity::exists($e)) {
                array_push($reportOnConsistency, "{$preerrortxt} Entity [".json_encode($e)."] not exists for has_one relation");
            }
        }
        foreach ($this->use_one as $e) {
            if (!Entity::exists($e)) {
                array_push($reportOnConsistency, "{$preerrortxt} Entity [".json_encode($e)."] not exists for use_one relation");
            }
        }
        foreach ($this->has_many as $e) {
            if (!Entity::exists($e)) {
                array_push($reportOnConsistency, "{$preerrortxt} Entity [".json_encode($e)."] not exists for has_many relation");
            }
        }
        foreach ($this->use_many as $e) {
            if (!Entity::exists($e)) {
                array_push($reportOnConsistency, "{$preerrortxt} Entity [".json_encode($e)."] not exists for use_many relation");
            }
        }
        foreach ($this->related as $e) {
            if (!Entity::exists($e)) {
                array_push($reportOnConsistency, "{$preerrortxt} Entity [".json_encode($e)."] not exists for related relation");
            }
        }
        return $reportOnConsistency;
    }

    public function getPath()
    {
        if ($this->alias) {
            return $this->alias;
        } else {
            return $this->name;
        }
    }

    public function category()
    {
        return $this->category;
    }

    public function is_timestamped()
    {
        return $this->communitydata;
    }

    public function is_community()
    {
        return $this->communitydata;
    }

    public function is_system()
    {
        if ($this->system == true) {
            return true;
        } else {
            return false;
        }
    }

    public function is_multy_lang()
    {
        if (count($this->multy_lang)) {
            return true;
        }
        return false;
    }

    public function option($name)
    {
        if ($option = $this->options[$name]) {
            return $option;
        }
        return null;
    }

    public function lang_codes()
    {
        if (count($this->multy_lang)) {
            return $this->multy_lang;
        }
        return false;
    }

    public function ftype($fname)
    {
        if (in_array($fname, array_keys($this->has_one()))) {
            return "has_one";
        }
        if (in_array($fname, array_keys($this->use_one()))) {
            return "use_one";
        }
        foreach ($this->use_many as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return "use_many";
            }
        }
        foreach ($this->has_many as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return "has_many";
            }
        }
        foreach ($this->belongs_to as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return "belongs_to";
            }
        }
        foreach ($this->lists as $listmeta) {
            if ($fname == $listmeta['name']) {
                return "list";
            }
        }
        if (Status::exists($fname)) {
            return 'status';
        }
        if ($this->has_field($fname)) {
            return "general_field";
        } else {
            return false;
        }
    }

    public function entityByUsedName($fname)
    {
        foreach ($this->belongs_to as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return Entity::ref($entity_uid[$usedas]);
            }
        }
        foreach ($this->has_one as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return Entity::ref($entity_uid[$usedas]);
            }
        }
        foreach ($this->use_one as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return Entity::ref($entity_uid[$usedas]);
            }
        }
        foreach ($this->use_many as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return Entity::ref($entity_uid[$usedas]);
            }
        }
        foreach ($this->has_many as $entity_uid) {
            $usedas = key($entity_uid);
            if ($fname == $usedas) {
                return Entity::ref($entity_uid[$usedas]);
            }
        }
        foreach ($this->lists as $listmeta) {
            if ($fname == $listmeta['name']) {
                return Entity::ref($listmeta['entity']);
            }
        }
    }



    public function has_statuses()
    {
        $statuses = array();
        foreach ($this->statuses as $statusid) {
            $status = $this->entityStatusByName($statusid); //$xstatus->name
            $statuses[] = $status;
        }
        return $statuses;
    }

    public function related()
    {
        $e=array();
        foreach ($this->related as $rel) {
            $e[] = Entity::ref($rel);
        }
        return $e;
    }

    public function lists($ename=null)
    {
        $em=array();
        if (!count($this->lists)) {
            return $em;
        }
        foreach ($this->lists as $list) {
            $listentity = Entity::ref($list['entity']);
            $add = true;
            if ($ename) {
                if ($listentity != $ename) {
                    $add = false;
                }
            }
            if ($add) {
                $em[] = array( 'entity'=> $listentity, 'ns'=> $list['ns'], 'name'=> $list['name'], 'title'=> $list['title'] );
            }
        }
        return $em;
    }

    public function listbyname($name, $throw = true)
    {
        foreach ($this->lists as $list) {
            if ($name == $list['name']) {
                return $list;
            }
        }
        if ($throw === true) {
            throw new Exception("List $name not found in {$this->name}");
        }
    }

    public function listbyns($id, $throw = true)
    {
        foreach ($this->lists as $list) {
            if ($id == $list['ns']) {
                return $list;
            }
        }
        if ($throw === true) {
            throw new Exception("List $name not found");
        }
    }

    public function has_listbyname($name)
    {
        foreach ($this->lists as $list) {
            if ($name == $list['name']) {
                return true;
            }
        }
        return false;
    }

    public function hasListOverRelation($entityname)
    {
        foreach ($this->lists as $list) {
            if ($entityname == $list['entity']) {
                return true;
            }
        }
        return false;
    }

    public function has_date()
    {
        $date_fields=array();
        foreach ($this->fields() as $f) {
            if ($f->type == 'date') {
                $date_fields[] = $f;
            }
        }
        return $date_fields[0];
    }

    public function has_field($field)
    {
        foreach ($this->fields() as $f) {
            if ($f->name == $field) {
                return true;
            }
        }
        return false;
    }

    public function is_compound_of($entity)
    {
        if (in_array($entity, $this->compound_of)) {
            return true;
        } else {
            return false;
        }
    }

    public function has_one($only=null)
    {
        $e=array();
        foreach ($this->has_one as $em) {
            if (is_array($em)) {
                $entity_key = key($em);
            } else {
                $entity_key = $em;
            }
            if ($only && $only == $entity_key) {
                $eonly[$entity_key] = Entity::ref($em);
                return $eonly;
            } else {
                $e[$entity_key] = Entity::ref($em);
            }
        }
        return $e;
    }

    public function use_one($only=null)
    {
        $e=array();
        foreach ($this->use_one as $em) {
            if (is_array($em)) {
                $entity_key = key($em);
            } else {
                $entity_key = $em;
            }
            if ($only && $only == $entity_key) {
                $eonly[$entity_key] = Entity::ref($em);
                return $eonly;
            } else {
                $e[$entity_key] = Entity::ref($em);
            }
        }
        return $e;
    }

    public function belongs_to($only=null)
    {
        $e=array();
        foreach ($this->belongs_to as $em) {
            //$e[] = Entity::ref($em);
            if (is_array($em)) {
                $entity_key = key($em);
            } else {
                $entity_key = $em;
            }
            if ($only && $only == $entity_key) {
                $eonly[$entity_key] = Entity::ref($em);
                return $eonly;
            } else {
                $e[$entity_key] = Entity::ref($em);
            }
        }
        return $e;
    }

    public function has_many($only=null)
    {
        $e=array();
        foreach ($this->has_many as $em) {
            //$e[] = Entity::ref($em);
            if (is_array($em)) {
                $entity_key = key($em);
            } else {
                $entity_key = $em;
            }
            if ($only && $only == $entity_key) {
                $eonly[$entity_key] = Entity::ref($em);
                return $eonly;
            } else {
                $e[$entity_key] = Entity::ref($em);
            }
        }
        return $e;
    }

    public function use_many($only=null)
    {
        $e=array();
        foreach ($this->use_many as $em) {
            //$e[] = Entity::ref($em);
            if (is_array($em)) {
                $entity_key = key($em);
            } else {
                $entity_key = $em;
            }
            if ($only && $only == $entity_key) {
                $eonly[$entity_key] = Entity::ref($em);
                return $eonly;
            } else {
                $e[$entity_key] = Entity::ref($em);
            }
        }
        return $e;
    }

    public function has_uuid()
    {
        foreach ($this->fields() as $f) {
            if ($f->name == 'uuid') {
                return true;
            }
        }
        return false;
    }

    public function lang_fields()
    {
        $f = array();
        if (sizeof($this->multy_lang) == 0) {
            return $f;
        }
        foreach ($this->lang_field_metas as $field_uid) {
            $F = Field::id($field_uid);
            if ($F->virtual) {
                continue;
            }
            $f[$F->name] = $F;
        }
        return $f;
    }

    public function fields()
    {
        $f=array();
        foreach ($this->field_metas as $field_uid) {
            if (is_array($field_uid)) {
                $field_key = key($field_uid);
            } else {
                $field_key = $field_uid;
            }
            $F = Field::id($field_uid);
            if ($F->virtual) {
                continue;
            }
            //$f[$field_key] = $F;
            $f[$F->name] = $F;
        }
        return $f;
    }

    public function general_fields()
    {
        $f=array();
        if (sizeof($this->multy_lang) == 0) {
            $gf = array_values($this->field_metas);
        } else {
            $gf = array_diff(array_values($this->field_metas), array_values($this->lang_field_metas));
        }
        foreach ($gf as $field_uid) {
            //println($gf,1,TERM_VIOLET);
            if (is_array($field_uid)) {
                $field_key = key($field_uid);
            } else {
                $field_key = $field_uid;
            }
            $F = Field::id($field_uid);
            if ($F->virtual) {
                continue;
            }
            //println($F,2,TERM_VIOLET);
            //println($field_key,3,TERM_VIOLET);
            //$f[$field_key] = $F;
            $f[$F->name] = $F;
        }
        return $f;
    }

    public function entityStatusByName($fname)
    {
        //println($GLOBALS['CONFIG']['STATUSINENTITY'][$this->uid]);
        if ($s = $GLOBALS['CONFIG']['STATUSINENTITY'][$this->uid][$fname]) {
            return $s;
        } else {
            throw new Exception("Status $fname in $this not found");
        }
    }

    public function entityFieldByName($fname)
    {
        if ($f = $GLOBALS['CONFIG']['FIELDINENTITY'][$this->uid][$fname]) {
            return $f;
        } else {
            throw new Exception("Field $fname in $this not found");
        }
    }

    public function hasEntityFieldByName($fname)
    {
        if ($f = $GLOBALS['CONFIG']['FIELDINENTITY'][$this->uid][$fname]) {
            return $f;
        } else {
            return false;
        }
    }

    public function is_lang_field($fname)
    {
        if (sizeof($this->multy_lang) == 0) {
            return false;
        }
        foreach ($this->lang_field_metas as $field_uid) {
            $F = Field::id($field_uid);
            if ($F->name == $fname) {
                return true;
            }
        }
        return false;
    }

    public function __toString()
    {
        return $this->urn;
    }

    public function toXMLConfig()
    {
        $E = $this;

        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $xentity = $dom->createElement("entity");
        $xentity->setAttribute('name', $E->name);
        $xentity->setAttribute('manager', $E->class);
        $xentity->setAttribute('uid', $E->uid);
        $dom->appendChild($xentity);

        $xpassport = $dom->createElement("passport");
        $xentity->appendChild($xpassport);
        $xtitle = $dom->createElement("title");
        $text = $dom->createTextNode($E->title['ru']);
        $xtitle->appendChild($text);
        $xpassport->appendChild($xtitle);
        $xentity->appendChild($xpassport);

        if ($E->is_multy_lang()) {
            $xi = $dom->createElement("international");
            $xentity->appendChild($xi);

            $xil = $dom->createElement("language");
            $langCodes = $E->lang_codes();
            $mainLang = array_shift($langCodes);
            $xil->setAttribute('code', $mainLang);
            $xit = $dom->createElement("translations");
            foreach ($langCodes as $langcode) {
                $xill = $dom->createElement("language");
                $xill->setAttribute('code', $langcode);
                $xit->appendChild($xill);
            }
            $xi->appendChild($xil);
            $xi->appendChild($xit);
        }

        $xstates = $dom->createElement("states");
        $xentity->appendChild($xstates);

        $xstructure = $dom->createElement("structure");
        $xentity->appendChild($xstructure);

        $xlists = $dom->createElement("lists");
        $xentity->appendChild($xlists);


        foreach ($E->statuses as $statusid) {
            $status = Status::ref($statusid);
            $xfield = $dom->createElement("status");
            $xfield->setAttribute('name', $status->name);
            $xfield->setAttribute('default', boolean2yesnotxt(($status->default == 1 || $status->default === true) ? true : false));
            $xfield->setAttribute('title', $status->title);
            $xstates->appendChild($xfield);
        }

        foreach ($E->has_one() as $as => $F) {
            $xfield = $dom->createElement("hasone");
            $xfield->setAttribute('entity', $F->name);
            if ($F->name != $as) {
                $xfield->setAttribute('as', $as);
            }
            if (in_array($F->name, $E->required)) {
                $xfield->setAttribute('required', 'yes');
            }
            $xstructure->appendChild($xfield);
        }

        foreach ($E->use_one() as $as => $F) {
            $xfield = $dom->createElement("useone");
            $xfield->setAttribute('entity', $F->name);
            if ($F->name != $as) {
                $xfield->setAttribute('as', $as);
            }
            if (in_array($F->name, $E->required)) {
                $xfield->setAttribute('required', 'yes');
            }
            $xstructure->appendChild($xfield);
        }

        foreach ($E->belongs_to() as $F) {
            $xfield = $dom->createElement("belongsto");
            $xfield->setAttribute('entity', $F->name);
            if (in_array($F->name, $E->required)) {
                $xfield->setAttribute('required', 'yes');
            }
            $xstructure->appendChild($xfield);
        }

        foreach ($E->has_many() as $F) {
            $xfield = $dom->createElement("hasmany");
            $xfield->setAttribute('entity', $F->name);
            $xstructure->appendChild($xfield);
        }

        foreach ($E->lists() as $list) {
            $rel = $list['entity'];
            $listns = $list['ns'];
            $listname = $list['name'];
            $listtitle = $list['title'];
            $xfield = $dom->createElement("list");
            $xfield->setAttribute('name', $listname);
            $xfield->setAttribute('entity', $rel->name);
            $xfield->setAttribute('ns', $listns);
            $xfield->setAttribute('title', $listtitle);
            $xlists->appendChild($xfield);
        }
        /*
        foreach ($E->related() as $rel)
        {
            $listns = rand(1000,2000);
            $listname = $rel->name;
            $listtitle = $rel->title['ru'];
            $xfield = $dom->createElement("list");
            $xfield->setAttribute('name', $listname);
            $xfield->setAttribute('entity', $rel->name);
            $xfield->setAttribute('ns', $listns);
            $xfield->setAttribute('title', $listtitle);
            $xlists->appendChild($xfield);
        }
        */
        foreach ($E->fields() as $fname => $F) {
            $xfield = $dom->createElement("field");
            $fname = str_replace('count_', 'count', $fname); // !!!!!!!!!!??? count_x = countx?
            $xfield->setAttribute('name', $fname);
            //$xfield->setAttribute('uid', $F->uid);
            $xfield->setAttribute('type', $F->type);
            if ($F->units) {
                $xfield->setAttribute('units', $F->units);
            }

            if ($E->is_lang_field($F->name)) {
                $xfield->setAttribute('role', 'international');
            }

            if (in_array($fname, $E->required)) {
                $xfield->setAttribute('required', 'yes');
            }
            if ($F->usereditable) {
                $xfield->setAttribute('usereditable', boolean2yesnotxt($F->usereditable));
            }

            if (isset($F->default)) {
                $xfield->setAttribute('default', $F->default);
            }

            $xfield->setAttribute('title', $F->title);

            if ($F->raw) {
                $xfield->setAttribute('raw', boolean2yesnotxt($F->raw));
            }
            if ($F->system) {
                $xfield->setAttribute('system', boolean2yesnotxt($F->system));
            }
            if ($F->virtual) {
                $xfield->setAttribute('virtual', boolean2yesnotxt($F->virtual));
            }
            if ($F->noneditable) {
                $xfield->setAttribute('noneditable', boolean2yesnotxt($F->noneditable));
            }
            if ($F->disabled) {
                $xfield->setAttribute('disabled', boolean2yesnotxt($F->disabled));
            }

            if ($F->type == 'option') {
                //println($F->values, 1, TERM_GREEN);
                foreach ($F->values[0] as $valname => $valtitle) {
                    $xo = $dom->createElement("value");
                    $xo->setAttribute('name', $valname);
                    $xo->setAttribute('title', $valtitle);
                    $xfield->appendChild($xo);
                }
                foreach ($F->values[1] as $valname => $valtitle) {
                    $xo = $dom->createElement("value");
                    $xo->setAttribute('name', $valname);
                    $xo->setAttribute('title', $valtitle);
                    $xfield->appendChild($xo);
                }
            } elseif ($F->type == 'set') {
                //println($F->options, 1, TERM_GREEN);
                foreach ($F->options as $valname => $valtitle) {
                    $xo = $dom->createElement("value");
                    $xo->setAttribute('name', $valname);
                    $xo->setAttribute('title', $valtitle);
                    $xfield->appendChild($xo);
                }
            } elseif ($F->type == 'richtext') {
                if ($F->illustrated) {
                    $xfield->setAttribute('illustrated', boolean2yesnotxt($F->illustrated));
                }
                if ($F->autoparagraph) {
                    $xfield->setAttribute('autoparagraph', boolean2yesnotxt($F->autoparagraph));
                }
                if ($F->nofollow) {
                    $xfield->setAttribute('nofollow', boolean2yesnotxt($F->nofollow));
                }
                if ($F->htmlallowed) {
                    $xfield->setAttribute('htmlallowed', $F->htmlallowed);
                }
            }


            if ($F->createDefault) {
                $xfield->setAttribute('createDefault', $F->createDefault);
            }
            if ($F->updateDefault) {
                $xfield->setAttribute('updateDefault', $F->updateDefault);
            }
            //$xfield->setAttribute('', $F->);

            $xstructure->appendChild($xfield);
        }

        $xdefaultorder = $dom->createElement("defaultorder");
        $xentity->appendChild($xdefaultorder);
        foreach ($E->defaultorder as $f => $dir) {
            $x = $dom->createElement("by");
            $x->setAttribute('field', $f);
            $x->setAttribute('order', $dir);
            $xdefaultorder->appendChild($x);
        }

        $xadmincolumns = $dom->createElement("admincolumns");
        $xentity->appendChild($xadmincolumns);
        foreach ($E->adminfields as $f) {
            $f = str_replace('count_', 'count', $f);
            $x = $dom->createElement("column");
            $x->setAttribute('selector', $f);
            $xadmincolumns->appendChild($x);
        }

        $xoptions = $dom->createElement("options");
        $xentity->appendChild($xoptions);
        foreach ($E->translit as $f => $ff) {
            $x = $dom->createElement("aparam");
            $x->setAttribute('name', 'translit');
            $x->setAttribute('value', 'legacytitle2uri');
            $xoptions->appendChild($x);
        }

        if (!$E->directmanage) {
            $x = $dom->createElement("aparam");
            $x->setAttribute('name', 'directmanage');
            $x->setAttribute('type', 'boolean');
            $x->setAttribute('value', 'no');
            $xoptions->appendChild($x);
        }

        if ($E->mediaoptions) {
            $x = $dom->createElement("aparam");
            $x->setAttribute('name', 'filenaming');
            $x->setAttribute('value', $E->option('filenaming'));
            $xoptions->appendChild($x);
            $x = $dom->createElement("aparam");
            $x->setAttribute('name', 'titling');
            $x->setAttribute('value', $E->option('titling')); // auto or original, or uuid // uri2title
            $xoptions->appendChild($x);
            if ($E->option('metadata')) {
                $x = $dom->createElement("aparam");
                $x->setAttribute('name', 'metadata');
                $x->setAttribute('type', 'boolean');
                $x->setAttribute('value', 'yes');
                $xoptions->appendChild($x);
            }
        }

        if ($E->imagesettings) {
            //println($E->imagesettings,1,TERM_GRAY);
            $xmoptions = $dom->createElement("imagesettings");
            $xentity->appendChild($xmoptions);
            $x = $dom->createElement("mainimage");
            $x->setAttribute('paradigm', $E->imagesettings['mainimage']['paradigm']);
            $x->setAttribute('hd', boolean2yesnotxt($E->imagesettings['mainimage']['hd']));
            $xs = $dom->createElement("size");
            $xs->setAttribute($E->imagesettings['mainimage']['size']['dim'], $E->imagesettings['mainimage']['size']['size']);
            $x->appendChild($xs);
            $xmoptions->appendChild($x);
            if ($E->imagesettings['previews']) {
                $px = $dom->createElement("previews");
                $px->setAttribute('paradigm', $E->imagesettings['previews']['paradigm']);
                $px->setAttribute('hd', boolean2yesnotxt($E->imagesettings['previews']['hd']));
                $xmoptions->appendChild($px);
            }
            foreach ($E->imagesettings['previews']['sizes'] as $sizename => $psize) {
                $x = $dom->createElement("size");
                $x->setAttribute('name', $sizename);
                $x->setAttribute($psize['dim'], $psize['size']);
                $x->setAttribute('base64', boolean2yesnotxt($psize['base64']));
                $px->appendChild($x);
            }
        }

        $xsearchfields = $dom->createElement("searchfields");
        $xentity->appendChild($xsearchfields);
        foreach ($E->searchtextin as $f) {
            $x = $dom->createElement("searchin");
            $x->setAttribute('column', $f);
            $xsearchfields->appendChild($x);
        }
        foreach ($E->adminsearchtextin as $f) {
            $x = $dom->createElement("adminsearchin");
            $x->setAttribute('column', $f);
            $xsearchfields->appendChild($x);
        }

        $xindexes = $dom->createElement("indexes");
        $xentity->appendChild($xindexes);
        foreach ($E->index as $f) {
            $x = $dom->createElement("index");
            $x->setAttribute('column', $f);
            $xindexes->appendChild($x);
        }
        foreach ($E->checkunique as $f) {
            $x = $dom->createElement("unique");
            $x->setAttribute('column', $f);
            $xindexes->appendChild($x);
        }

        //return $json;
        return $dom->saveXML();
    }
}
