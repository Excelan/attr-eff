<?php
/**
TODO noneditable(disabled) fields, statuses, links filter from user provided (user cant give uri if its translated, cat set moderated status)
TODO trail whitespace in text fields
*/
class entitystore
{
    public static function store($d)
    {
        /**
        $EORET - for mirror return from action
        */
        //$EORET = array();

        $urn = $d->urn;
        $E = $urn->entitymeta;

        // LANG
        if ($E->is_multy_lang()) {
            $lang = $d->lang;
            if (strlen($lang) != 2) {
                $lang = DEFAULT_LANG;
            }
            //throw new Exception("Entity (".$E.") is multylang but no lang code provided");
        }

        // create
        if ($d->action == 'create') {
            $update = false;
            $create = true;
        }
        // update
        elseif ($d->action == 'update') {
            $update = true;
            $create = false;

            if ($urn->hasUUID()) {
                $d->id = $urn->uuid()->toInt();
                $durn = $d->urn;
            } else {
                if ($d->id) {
                    $durn = new URN($d->urn . ':' . $d->id);
                } else {
                    throw new Exception('Cant update general urn. Provide id or concrete urn');
                }
            }
            /**
            GET OLD OBJECT FOR COMPARE CHANGES
            */
            $old = $durn->resolve($lang, true)->current(); // .., true - nocache resolve
            // TODO if no uuid here return error
        }

        // check entity existence
        if (!$E) {
            throw new Exception("Entity named [".$e."] doesn't exists (in EntityStore::store())");
        }

        // generate UUID if not provided
        if ($d->exists('id')) {
            if (is_object($d->id)) {
                throw new Exception("Entity create ID is object! not int. Try to check if it is UUID?");
            }
            if ($d->id > 0) {
                $EO['id'] = $d->id;
            }
        } else {
            if ($E->name == 'product') {
                $newuuid = new UID();
            } else {
                $newuuid = new UUID();
            }
            $EO['id'] = $newuuid->toInt();
        }

        // STATUSES
        foreach ($E->statuses as $statusid) {
            $status = Status::ref($statusid)->name;
            if ($d->exists($status) && $d->$status !== null) {
                if ($d->$status !== '1' and $d->$status !== 'true' and $d->$status !== 'yes' and $d->$status !== 1 and $d->$status !== true) {
                    $d->$status = 0;
                }
                $EO[$status] = $d->$status;
            } else {
                if ($update === false) {
                    if (is_bool(Status::ref($statusid)->default)) {
                        $EO[$status] = Status::ref($statusid)->default ? 1 : 0;
                    } else {
                        $EO[$status] = Status::ref($statusid)->default;
                    }
                }
            }
            //$EORET[$status] = $EO[$status];
        }

        // HAS_*, BEL*
        /**
        BELONGS_TO
        */
        foreach ($E->belongs_to() as $usedAs => $EBT) {
            //$v = $EBT->name;
            $v = $usedAs;
            if (!$d->exists($v)) {
                continue;
            }
            if ($d->$v === '') {
                continue;
            } // SET EVERYWERE === VS ==
            if (!is_numeric($d->$v)) {
                if ($d->$v instanceof Message) {
                    $urn = $d->$v->urn;
                    $ismessage = true;
                } elseif ($d->$v instanceof URN) {
                    $urn = $d->$v;
                    $isurn = true;
                } else {
                    if ($d->$v == 'NULL') {
                        $EO[$EBT->name] = 0;
                    }
                }
                if ($ismessage || $isurn) {
                    $EO[$EBT->name] = $urn->uuid()->toInt();
                } // TODO protect from fatal error
            } else {
                // TODO INT OR UUID(string or uuid object)?
                if (strlen($d->$v)) {
                    $EO[$EBT->name] = $d->$v;
                }
            }
            /**
            NOTIFY
            */
            $aqkey = "{$EBT->name}";
            if ($EO[$EBT->name] && $old->$aqkey && $EO[$EBT->name] != $old->$aqkey) {
                $notifyLost[$EBT->name] = $old->$aqkey;
            }
            if ($EO[$EBT->name]) {
                $notify[$EBT->name] = $EO[$EBT->name];
            }
            //if ($EO[$EBT->name] != 0) $EORET[$EBT->name] = 'urn-'.$EBT->name.'-'.$EO[$EBT->name];
        }

        /**
        HAS_ONE
        no notify self or subject on aquire
        */
        foreach ($E->has_one() as $usedAs => $EHO) {
            //println("$usedAs => $EHO",1,TERM_RED);
            $v = $usedAs; //$EHO->name;
            if (!$d->exists($v)) {
                continue;
            }
            if ($d->$v === '') {
                continue;
            }
            if ($d->$v == 'NULL') {
                $EO[$v] = 0;
            } elseif (is_numeric($d->$v)) {
                $EO[$v] = $d->$v;
            } elseif (!is_numeric($d->$v)) {
                $sideManager = $EHO->getClass();
                $EManager = $E->getClass();
                $urn = new URN($d->$v);
                /**
                TODO IF SAME ENTITY! but we can assign photo as illustration
                 */
                if ($EHO->name == $urn->entitymeta->name) {
                    if ($urn->is_concrete()) {
                        $EO[$v] = $urn->uuid()->toInt();
                    } else {
                        throw new Exception("Incorrect urn $urn for $v");
                    }
                } else {
                    $sideManager->recieve(new Message('{"action": "extend", "urn": "urn:'.$EHO->name.'", "from": "'.$urn.'", "host": "'.$host_urn.'"}')); //, "size": "400x400"
                    $EO[$v] = $urn->uuid()->toInt();
                }
                $current_uuid = new UUID($EO['id']);
                /**
                TODO NOTIFY managers about link acquire or loose
                 */
                //println("Assign has one /$v/ to /{$E->name}/", 1, TERM_YELLOW);
                //$sideManager->notify(new Message('{"assigned": {"object": "'.$urn.'",  "host": "urn:'.$E->name.':'.$current_uuid.'"}}'));
                //$EManager->notify(new Message('{"aquired": {"object": "'.$urn.'",  "host": "urn:'.$E->name.':'.$current_uuid.'"}}'));
                //$EORET[$EHO->name] = 'urn-'.$EHO->name.'-'.$EO[$EHO->name];
            }
        }

        /**
        USE_ONE
        */
        foreach ($E->use_one() as $usedAs => $EHO) {
            //println("$usedAs => $EHO",1,TERM_RED);
            $v = $usedAs; //$EHO->name;
            if (!$d->exists($v)) {
                continue;
            }
            if ($d->$v === '') {
                continue;
            }
            if ($d->$v == 'NULL') {
                $EO[$v] = 0;
            } elseif (is_numeric($d->$v)) {
                $EO[$v] = $d->$v;
            } elseif (!is_numeric($d->$v)) {
                $sideManager = $EHO->getClass();
                $EManager = $E->getClass();
                $urn = new URN($d->$v);
                /**
                TODO IF SAME ENTITY! but we can assign photo as illustration
                 */
                if ($EHO->name == $urn->entitymeta->name) {
                    $EO[$v] = $urn->uuid()->toInt();
                } else {
                    $sideManager->recieve(new Message('{"action": "extend", "urn": "urn:'.$EHO->name.'", "from": "'.$urn.'", "host": "'.$host_urn.'"}')); //, "size": "400x400"
                    $EO[$v] = $urn->uuid()->toInt();
                }
                $current_uuid = new UUID($EO['id']);
            }
            /**
            NOTIFY
            */
            $aqkey = "{$EHO->name}_id";
            if ($EO[$EHO->name] && $old->$aqkey && $EO[$EHO->name] != $old->$aqkey) {
                $notifyLost[$EHO->name] = $old->$aqkey;
            }
            if ($EO[$EHO->name]) {
                $notify[$EHO->name] = $EO[$EHO->name];
            }
        }


        /**
        HAS_MANY
        */
        foreach ($E->has_many() as $EHM) {
            $v = $EHM->name;

            $sideManager = $EHM->getClass();

            if (sizeof($d->$v) > 0) {
                if ($d->$v instanceof URN) {
                    throw new Exception('If you wish to attach to has_many update that object belongs_to link');
                    /**
                    TODO Do that "inverse update" here
                    */
                }
                if ($d->$v instanceof Message) {
                    foreach ($d->$v->toArray() as $urn) {
                        // create urn: album, photoitem: [photo-1, photo-2]

                        $urn = urn($urn);
                        if ($EHM->name == $urn->entitymeta->name) {
                            //$EO[$EHM->name][] = $urn->uuid()->toInt();
                            // проапдейтить им parent_id?
                        } else {
                            /**
                            extend assigned [has many item>exteneditem]
                            */
                            $host_uuid = new UUID($EO['id']);
                            $m = new Message();
                            $m->action = 'extend';
                            $m->urn = 'urn:'.$EHM->name;
                            $m->from = $urn;
                            $m->host = 'urn:'.$E->name.'-'.$host_uuid;
                            $newchild = $sideManager->recieve($m);
                        }
                    }
                }
            }
        }

        // (not translated) fields get values
        /**
        foreach($E->fields() as $key => $F)
        {
            $baseName = $F->name;
            println("$key : $baseName",2,TERM_GRAY);
            if (!$d->$key) continue;
            $EORET[$key] = EntityStore::fieldSecure($F, $d->$key);
        }
        */

        // PREPROCESS URI TRANSLIT
        if (is_array($E->translit)) {
            foreach ($E->translit as $tf => $tt) {
                if (strlen($d->$tf)) {
                    $d->$tt = Security::safeStr(translit($d->$tf, $d->lang));
                } //
            }
        }

        // LANG FIELDS (ALL STRING!)
        foreach ($E->lang_fields() as $F) {
            $v = $F->name;
            //println("LANG $v $F" ,2,TERM_GRAY);
            $EOi[$F->name] = EntityStore::fieldSecure($F, $d->$v);
        }

        // GENERAL FIELDS
        // TODO Move def values to Fields objects
        foreach ($E->general_fields() as $v => $F) {
            $base = $F->name;
            //$dbs = ($base != $v) ? $base : '';
            //println("GENE $v $F" ,1,TERM_GRAY);
            //println($d ,2,TERM_GRAY);
            //println($d->$v ,2,TERM_GRAY);
            //if (!$d->$key) continue;

            if ($F->type == 'timestamp') {
                //print('F: '.$F->name.' cd'.$F->createDefault.' ud'.$F->updateDefault.' ');
                if (strlen($d->$v)) {
                    // has val

                    if (is_numeric($d->$v)) {
                        $EO[$v] = $d->$v;
                    } else {
                        $EO[$v] = strtotime($d->$v);
                    } // Format: YYYY-MM-DD HH:MM, HH:MM
                } else {
                    // no value

                    if ($update === true) {
                        if ($F->updateDefault == 'now') {
                            $EO[$v] = TimeOp::now();
                        }
                    } elseif ($create === true) {
                        if ($F->createDefault == 'now') {
                            $EO[$v] = TimeOp::now();
                        }
                    }
                }
            } elseif ($F->type == 'sequence') {
                $EO[$v] = $d->$v;
            } elseif ($F->type == 'iarray') {
                $EO[$v] = $d->$v;
            } elseif ($F->type == 'tarray') {
                $EO[$v] = $d->$v;
            } elseif ($F->type == 'date') {
                if (strlen($d->$v)) {
                    $dt = strtotime($d->$v);
                    $EO[$v] = date('Y-m-d', $dt);
                } else {
                    // current date by default
                    //if ($update === false) $EO[$v] = TimeOp::date();
                }
            } elseif ($F->type == 'option') {
                //println($F,1,TERM_GREEN);
                //println($d->$v,2);
                if ($d->$v) {
                    $index = $F->indexOfValue($d->$v);
                    $EO[$v] = $index;
                }
                //println($EO[$v],3);
                /*;
                $option1value = key($F->values[0]);
                $option2value = key($F->values[1]);
                if ($d->$v === $option1value or $d->$v === '1' or $d->$v === 1 or $d->$v === 'true' or $d->$v === true or $d->$v === 'Y') $EO[$v] = 1;
                if ($d->$v === $option2value or $d->$v === '-1' or $d->$v === -1 or $d->$v === 0 or $d->$v === 'false' or $d->$v === false or $d->$v === 'N') $EO[$v] = 0;
                */
            } elseif ($F->type == 'set') {
                if ($d->exists($v)) {
                    $index = $F->indexOfValue($d->$v, true);
                    if ($index) {
                        $EO[$v] = $index;
                    } else {
                        $EO[$v] = 0;
                    }
                }
            } else {
                // all other field types (int, float, string, SET/ENUM!)

                if ($F->name == '_parent' && isURN($d->$v)) {
                    $d->$v = $d->$v->uuid;//->toInt();
                }
                if (($F->type == 'integer' || $F->type == 'float' || $F->type == 'money')) {
                    // !!! $update &&

                    if (is_numeric($d->$v)) {
                        if ($F->type == 'float' || $F->type == 'money') {
                            $EO[$v] = (float) $d->$v;
                        } else {
                            $EO[$v] = (int) $d->$v;
                        }
                    } elseif ($d->$v instanceof Message) {
                        // it may be message (inc, dec etc)

                        $EO[$v] = $d->$v;
                    }
                } else {
                    // all non integer

                    if ($d->$v) {
                        if ($F->type == 'xml') {
                            $EO[$v] = $d->$v;
                        } elseif ($F->type == 'json') {
                            if ($d->$v instanceof stdClass) {
                                $EO[$v] = json_encode($d->$v);
                            } else {
                                $EO[$v] = $d->$v;
                            }
                        }
                        //$d->$v = EntityStore::fieldSecure($F, $d->$v);
                        else {
                            if ($F->type == 'string') {
                                //Log::info('UPDATE '.bin2hex($d->$v), 'sql');
                                $EO[$v] = EntityStore::fieldSecure($F, $d->$v);
                            } else {
                                $EO[$v] = EntityStore::fieldSecure($F, $d->$v);
                            }
                        }
                    }
                }
                // set default value if needed
                if ($update !== true && $EO[$v] === null && $F->default !== null) {
                    $EO[$v] = $F->default;
                }
            }
        }

        foreach ($EO as $EOkey => $EOval) {
            if ($EOval) {
                $EOkeys[]=$EOkey;
            }
        }
        $dKeys = $d->keysNonSystem();
        foreach ($dKeys as $k) {
            if (in_array($k, $EOkeys)) {
                $kc++;
            }
        }
        //if ($kc < count($dKeys)) println('need extended check', 1, TERM_RED);
        //else println('NOT need extended check');

        if ($E->attributed) {
            /*
            println('ATTR',1,TERM_GREEN);
            $attr = json_decode($d->_attributes, true);
            foreach ($attr as $ak => $av)
                println("$ak => $av");
            //println($d->_attributes);
            $attrover = json_decode($d->_attributeoverlay, true);
            foreach ($attrover as $ak => $av)
                println("$ak => ".json_encode($av), 1, TERM_GRAY);
            */
        }

        // EXTENDED STRUCTURE
        $eattributes = array();
        //Log::error($d, 'ev');
        if ($kc < count($dKeys)) {
            // only if query has possible extended fields

            foreach ($E->extendstructure as $ee) {
                // category etc

                //if (!$d->$ee) continue;
                $dEe = $d->$ee; // urn-category-11

                if (!$dEe && $update !== false) {
                    $dEe = $d->urn->resolve()->category->urn; // TODO! why ->category static
                } // ! preload on update if not provided

                if ($dEe) {
                    $extender = $dEe->resolve();//->current();
//                    dprintln($extender,1,TERM_VIOLET);
                    //Log::info($extender, 'ev');

                    $extender->extendMergeParents();
//                    dprintln($extender,2,TERM_VIOLET);

                    if ($update !== false) {
                        $incself = $d;
                    }
                    $attributes = Entity::extenderAttributesHelper($extender, $incself);

                    foreach ($attributes as $attr) {
                        $pname = $attr->uri;
                        if ($d->$pname) {
                            $eattributes[$pname] = $d->$pname;
                        }
                    }
                } else {
                    throw new Exception("Need urn:{$ee} provided for extended update");
                }
            }
        }

        if (count($eattributes)) {
            $EO['_attributes'] = $eattributes;
        } elseif ($d->clearattributes !== null) {
            $EO['_attributes'] = array();
        }
        /*
        if (count($evariators))
        {
            $EO['_variators'] = $evariators;
        }
        else
        {
            if ($E->extendstructure && $d->$ee && !$catnotprovided)
                $EO['_variators'] = '';
        }
        */
        //Log::info(json_encode($EO['_properties']), 'ev');
        //Log::debug(json_encode($EO['_variators']), 'ev');
        /**
        ORDERED
        !!! TODO MOVE TO BEHAVIOR (list "create" before crud "create")
        or in entity PhtoItem Meta > Container = array('photoalbum')
        */
        if ($d->action == 'create' && $E->has_field('ordered')) {
            if (!$d->ordered) {
                $m = new Message();
                $m->action = 'load';
                $m->urn = 'urn:'.$E->prototype;
                $m->field = 'max(ordered) as '.SQLQT.'max'.SQLQT;
                foreach ($E->belongs_to() as $usedas => $F) {
                    $bt[] = $usedas;
                }
                if ($belongs_to = array_intersect($d->keys(), $bt)) {
                    $btindex = array_values($belongs_to);
                    foreach ($btindex as $btcur) {
                        $EP = Entity::ref($btcur);
                        $hm = array();
                        foreach ($EP->has_many() as $usedas => $F) {
                            $hm[] = $usedas;
                        }
                        if ($pp = array_intersect($hm, array($E->name))) {
                            $m->$btcur = $d->$btcur;
                        }
                    }
                }
                // SELECT id, max(ordered) as max FROM productphoto  WHERE product_id = 564  ORDER BY ordered ASC
                // SELECT id,ordered FROM productphoto WHERE product_id = 564 AND ordered = MAX(ordered)
                // SELECT id,max(ordered) FROM productphoto WHERE product_id = 564
                $m->order = SQLQT.'max'.SQLQT.' asc';
                $mm = $m->deliver()->current();
                $max = $mm->max;

                if ($max > 0) {
                    $d->ordered = $max + 1;
                } else {
                    $d->ordered = 1;
                }
                $EO['ordered'] = $d->ordered;
                //$EORET['ordered'] = $d->ordered;
            }
        }


        /**
        UPDATE
         */
        if ($update !== false) {
            if ($EO && $EOi) {
                $updata = array_merge($EO, $EOi);
            }
            if ($EO && !$EOi) {
                $updata =$EO;
            }
            if (!$EO && $EOi) {
                $updata = $EOi;
            }
            if ($d->id) {
                $UPID = $d->id;
            } else {
                throw new Exception("FOR UPDATE URN PROVIDE ID OR UUID (URN={$d->urn}), $d");
            }
            $edb = new EntityDB_update($E);
            $edb->update($lang, $UPID, $updata);
            $CREATED_ID = $d->id;
        }
        /**
        SAVE
        */
        else {
            $edb = new EntityDB_insert($E);
            $CREATED_ID = $edb->insert($lang, $EO, $EOi, $eoid);
        }

        $EO['_e'] = $E->name;
        if ($EO && $EOi) {
            $merged = array_merge($EO, $EOi);
        }
        if ($EO && !$EOi) {
            $merged = $EO;
        }
        if (!$EO && $EOi) {
            $merged = $EOi;
        }
        if ($lang) {
            $merged = array_merge($merged, array("lang"=>$lang, "translated_$lang"=>1));
        }
        $merged = array_merge($merged, array("id" => $CREATED_ID));

        $uuid = new UUID($CREATED_ID);
        $created = new Message($merged); // $EORET as result now disabled
        if ($E->is_multy_lang()) {
            $created->lang = $lang;
        }
        $created->urn = "urn:".$E->name.":".$uuid;

        if ($eattributes) {
            $created->__extended = $eattributes;
        }

        /**
        NOTIFY AQUIRE & LOST
        */
        if (count($notify)) {
            foreach ($notify as $aquirerHost => $hostUUID) {
                $hostURN = URN::build($aquirerHost, $hostUUID);
                //println("$aquirerHost => $hostUUID");
                $count_field_name = 'count'.$created->urn->entity->name;
                $has_field = $hostURN->entity->has_field($count_field_name);
                //printlnd($has_field);
                if ($has_field) {
                    $m = new Message();
                    $m->urn = $hostURN;
                    $m->action = 'increment';
                    $m->field = $count_field_name;
                    $m->value = 1;
                    $newcountval = $m->deliver();
                }

                $mq = "aquire.{$aquirerHost}.{$E->name}";
                Broker::instance()->send(array((string)$hostURN, (string)$created->urn, $newcountval), "ENTITY", $mq);
            }
        }
        if (count($notifyLost)) {
            foreach ($notifyLost as $aquirerHost => $hostUUID) {
                //println("$aquirerHost => $hostUUID");
                $hostURN = URN::build($aquirerHost, $hostUUID);
                $count_field_name = 'count'.$created->urn->entity->name;
                $has_field = $hostURN->entity->has_field($count_field_name);
                //printlnd($has_field);
                if ($has_field) {
                    $m = new Message();
                    $m->urn = $hostURN;
                    $m->action = 'decrement';
                    $m->field = $count_field_name;
                    $m->value = 1;
                    $m->deliver();
                }

                $mq = "unaquire.{$aquirerHost}.{$E->name}";
                Broker::instance()->send(array($hostURN, $created->urn), "ENTITY", $mq);
            }
        }

        if ($update !== false) {
            return array($created, $old);
        } else {
            return $created;
        }
    }

    public static function fieldSecure($F, $dv)
    {
        if ($F->raw == true) {
            return $dv;
        } // !
        if ($F->type == 'richtext' && $F->htmlallowed) {
            // TODO always filter any richtext

            $o = array(
                'HTML.Allowed' => $F->htmlallowed,
                'HTML.Nofollow' => $F->nofollow,
                'AutoFormat.AutoParagraph' => $F->autoparagraph
                );
            $dv = Security::safeHtml($dv, $o);
        } else {
            $old = $dv;
            if (!$F->raw) {
                $dv = Security::safeStr($dv);
            } // !
            //if ($dv != $old) println($dv,1,TERM_VIOLET); // check fo double enter
        }
        return $dv;
    }
}
