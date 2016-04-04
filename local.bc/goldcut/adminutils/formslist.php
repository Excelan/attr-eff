<?php
$dir = 'config/form';
$directory = BASE_DIR.DIRECTORY_SEPARATOR.$dir;
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
$objects->setMaxDepth(10);
$rnd = rand(1,1000000);
$errors = 0;

function createDraft($prototype)
{
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:'.$prototype;
    $m->privatedraft = 't';
    $m->last = 1;
    $draft = $m->deliver();
    if (count($draft)) return $draft;

    $m = new Message();
    $m->action = 'create';
    $m->urn = 'urn:'.$prototype;
    $m->privatedraft = 't';
    $draft = $m->deliver();

    return $draft;
}

function analizeFields($domx, $ctx, $entity)
{
    $E = Entity::ref($entity);
    $fs = $domx->evaluate("field", $ctx);
    foreach ($fs as $f)
    {
        $fname = $f->getAttribute('name');
        if ($fname == 'urn') continue; //
        $fnameas = $f->getAttribute('as');
        if ($fnameas)
        {
            println("$fname as $fnameas AS not allowed in form fields",3,TERM_RED);
            $GLOBALS['errors']++;
            continue;
        }
        $ftype = $f->getAttribute('type');
        $fmult = $f->getAttribute('multiple');
        $realField = $E->ftype(($fnameas ? $fnameas : $fname));
        if ($realField)
        {
            if ($realField == 'general_field')
            {
                $F = $E->entityFieldByName(($fnameas ? $fnameas : $fname));
                // multiple in db?
                if ($fmult && $F->type != 'tarray') {
                    println("FIELD IS MULTIPLE BUT NOT OF TARRAY TYPE IN DB $fname {$ftype}~{$F->type}", 3, TERM_RED);
                    $GLOBALS['errors']++;
                }
                if ($fmult && substr($fname,-1,1) != 's') {
                    println("FIELD IS MULTIPLE BUT NAME NOT ENDS ON S $fname {$ftype}~{$F->type}", 3, TERM_RED);
                    $GLOBALS['errors']++;
                }
                else if (!$fmult && substr($fname,-1,1) == 's') {
                    println("FIELD IS NOT MULTIPLE BUT NAME ENDS ON S $fname {$ftype}~{$F->type}", 3, TERM_GRAY);
                }

                // types EQUALS ==
                if ($ftype == $F->type) {
                    // OK
                    //println("$fname {$ftype}~{$F->type}", 3, TERM_GRAY);
                }
                else
                {
                    if ($ftype == 'attachment' && $F->type == 'tarray' && $fmult) {
                        // OK
                        //println("$fname {$ftype}~{$F->type}", 3);
                    }
                    elseif ($ftype == 'attachment' && $F->type == 'string' && !$fmult) {
                        // OK
                        //println("$fname {$ftype}~{$F->type}", 3);
                    }
                    else if (($ftype == 'select' || $ftype == 'radio') && $F->type == 'set') {
                        // OK
                    }
                    else if (($ftype == 'Document') && $F->type == 'string') {
                        // OK
                    }
                    else
                    {
                        println("$fname Field types mismatch {$ftype} != {$F->type}",3,TERM_RED);
                        $GLOBALS['errors']++;
                    }
                }
                // compare select options
                if ($ftype == 'select' || $ftype == 'radio')
                {
                    // entity field
                    if ($F->type != 'set')
                    {
                        println("$F type is not set for Form field select/radio",3,TERM_RED);
                        $GLOBALS['errors']++;
                        continue;
                    }
                    $entityFieldOptions = [];
                    foreach ($F->options as $ok => $ov) array_push($entityFieldOptions, $ok);
                    //println($F);
                    // form field
                    $os = $domx->evaluate("options//option", $f);
                    if ($os->length == 0) {
                        println("$F dont has /options/option tags",3,TERM_RED);
                        $GLOBALS['errors']++;
                    }
                    if ($os->length > 2 && $ftype == 'radio') {
                        println("$F is radio and have {$os->length} > 2 /options/option tags",3,TERM_RED);
                        $GLOBALS['errors']++;
                    }
                    $formFieldOptions = [];
                    foreach ($os as $o)
                    {
                        $oname = $o->getAttribute('value'); // option value in form, value name in entity
                        array_push($formFieldOptions, $oname);
                    }
                    $diff = array_diff($formFieldOptions, $entityFieldOptions);
                    if (count($diff))
                    {
                        println("No needed options from entity in form select ".json_encode($diff),3,TERM_RED);
                        println($formFieldOptions);
                        println($entityFieldOptions);
                        println($E);
                        println($F);
                        $GLOBALS['errors']++;
                    }
                } // select, radio check
            }
            else // non general field
            {
                $RELE = $E->entityByUsedName($fnameas ? $fnameas : $fname);
                if (!$RELE)
                    throw new Exception("Cant get RELE = E->entityByUsedName($fname)");

                if ($fmult && !($realField == 'list' || $realField == 'hasmany')) // &  OK
                    println("$fname {$ftype}~ MULT RELATION INFO $realField", 2, TERM_GRAY);

                if (($fnameas ? $fnameas : $fname) == $RELE->getAlias()) { // TODO  && $fname == $ftype
                    // OK
                    //println("$fname {$ftype}~RELATION $realField", 3, TERM_GRAY);
                }
                else
                {
                    //if ($RELE->getAlias() == 'BusinessObjectRecordPolymorph' && $ftype == 'BusinessObject') {
                        // OK
                        //println("$fname {$ftype}~RELATION SPECIAL $realField", 3, TERM_BLUE);
                    //}
                    if ($ftype == 'BusinessObject') $ftype = 'BusinessObjectRecordPolymorph';
                    if ($RELE->getAlias() == $ftype)
                    {
                        // OK
                        //println($fname ." == ".$RELE->getAlias() . "~RELATION $realField OK BY NAME", 3, TERM_BLUE);
                    }
                    elseif ($ftype == 'select')
                    {
                        // OK
                        // выбор из любой таблицы может быь представлен селектом
                    }
                    else
                    {
                        println("{$fname}/{$ftype} == ".$RELE->getAlias()." Field to Entity type mismatch",3,TERM_RED);
                        $GLOBALS['errors']++;
                    }
                }
            }
            // check types match


        }
        else {
            println("$fname $ftype NO FIELD IN ENTITY", 3, TERM_RED);
            $GLOBALS['errors']++;
        }
    }
}

function recAnalizeStruct($domx, $ctx, $level)
{
    $structs = $domx->evaluate("struct", $ctx);
    foreach ($structs as $struct)
    {
        $entity = $struct->getAttribute('entity');
        if ($entity)
        {
            println($entity,1,TERM_BLUE);
            // TODO DRAFT CREATE
            // analize Fields
            analizeFields($domx, $struct, $entity);
        }
        else {
            if ($level < 3) {
                // OK
                //println($struct->getAttribute('name') . ' ' . $level, 3, TERM_VIOLET);
            }
            else
            {
                println($struct->getAttribute('name')." STRUCT ON LEVEL > 2 " . $level, 3, TERM_GRAY);
                //$GLOBALS['errors']++;
            }
        }

        // recursion
        recAnalizeStruct($domx, $struct, $level++);
    }
}





foreach ($objects as $fileinfo)
{
    try {
    if ($fileinfo->isFile())
    {
        $draft = null;
        $fname = $fileinfo->getFilename();
        $fpath = $fileinfo->getPath();
        if (strpos($fpath, 'Examples')) continue;
        if (substr($fname,-4,4) == '.xml')
        {
            $i++;
            $shortPath = substr($fpath, strlen(BASE_DIR)+13);
            $shortName = substr($fname, 0, -4);
            echo "<div style='border:1px solid #ccc;'>";
            echo("<p>$i <b>{$shortName}</b></p>");

            $fullpath = $fpath.'/'.$fname;

            $doc = new DOMDocument();
            $xmlok = $doc->load($fullpath);
            if (!$xmlok)
            {
                println("XML ERROR!",1,TERM_RED);
                $errors++;
            }
            $domx = new DOMXPath($doc);

            // перечень секций и и проверка на пустые тайтлы
            $entries = $domx->evaluate("//section");
            foreach ($entries as $entry) {
                //$entityTitle = $entry->nodeValue;
                $sectionType = $entry->getAttribute('type');
                $entityTitle = $entry->getAttribute('title');
                $entity = $entry->getAttribute('entity');
                if ($sectionType == 'context')
                    println($entityTitle,1);
                else
                    println($entityTitle,1,TERM_GRAY);
                if ($entityTitle == '')
                {
                    println('Пустое название секции <a name="error"></a>',1,TERM_RED);
                    $errors++;
                }
                // !!! SECTION IS ENTITY
                if ($entity && !$draft) {
                    // TODO DRAFT CREATE
                    $draft = createDraft($entity);
                    analizeFields($domx, $entry, $entity);
                }
            }

            // Double names check
            $entries = $domx->evaluate("//section/struct");
            $ca = [];
            foreach ($entries as $entry) {
                $structName = $entry->getAttribute('name');
                if (in_array($structName, $ca))
                {
                    println('DOUBLE NAME '.$structName.'<a name="error"></a>',1,TERM_RED);
                    $errors++;
                }
                array_push($ca, $structName);
            }
            $entriest = $domx->evaluate("//section");
            foreach ($entriest as $entryt) {
                $entries = $domx->evaluate("field", $entryt);
                $ca = [];
                foreach ($entries as $entry) {
                    $structName = $entry->getAttribute('name');
                    if (in_array($structName, $ca)) {
                        println('DOUBLE NAME ' . $structName . '<a name="error"></a>', 1, TERM_RED);
                        $errors++;
                    }
                    array_push($ca, $structName);
                }
            }
            $entries = $domx->evaluate("//section/struct/field");
            $ca = [];
            foreach ($entries as $entry) {
                $structName = $entry->getAttribute('name');
                if (in_array($structName, $ca))
                {
                    println('DOUBLE NAME '.$structName.'<a name="error"></a>',1,TERM_RED);
                    $errors++;
                }
                array_push($ca, $structName);
            }
            $entries = $domx->evaluate("//section/struct/struct");
            $ca = [];
            foreach ($entries as $entry) {
                $structName = $entry->getAttribute('name');
                if (in_array($structName, $ca))
                {
                    println('DOUBLE NAME '.$structName.'<a name="error"></a>',1,TERM_RED);
                    $errors++;
                }
                array_push($ca, $structName);
            }

            // перечень multiple структур и проверка на пустые тайтлы
            $entries = $domx->evaluate("//struct[@multiple='yes']");
            foreach ($entries as $entry) {
                $structName = $entry->getAttribute('name');
                $structTitle = $entry->getAttribute('title');
                if ($structTitle == '') {
                    println('Пустое название структуры <a name="error"></a>',1,TERM_RED);
                    $errors++;
                }
                else
                {
                    // OK
                    //println("$structName $structTitle",2, TERM_GRAY);
                }
            }

            // проверка на старые имена
            $entries = $domx->evaluate("//struct[@name='innerstruct']|//struct[@name='innerstructmult']");
            foreach ($entries as $entry) {
                $structName = $entry->getAttribute('name');
                $structTitle = $entry->getAttribute('title');
                println("WRONG NAMES $structName $structTitle <a name=\"error\"></a>",2, TERM_RED);
                $errors++;
            }

            // перечень полей и проверка допустимых типов
            $entries = $domx->evaluate("//field");
            foreach ($entries as $entry) {
                $fname = $entry->getAttribute('name');
                $ftype = $entry->getAttribute('type');
                $ftitle = $entry->getAttribute('title');
                if (!in_array($ftype, ['hidden','string','text','richtext','integer','float','money','select','radio','attachment','date','Document','CompanyLegalEntityCounterparty','BusinessObject','CompanyLegalEntityCounterparty','ManagementPostIndividual','RiskManagementRiskApproved'])) {
                    println("INCORRECT FIELD TYPE $ftype IN $fname $ftitle <a name=\"error\"></a>", 1, TERM_RED);
                    $errors++;
                }
            }

            // TODO сравнение структуры с сущностью по именам и типам полей
            // сверка multiple полей, чтобы в базе был tarray
            // сверка multipel структур, чтобы ы базе были соотв связи
            // section > struct @ rec
            $entries = $domx->evaluate("//section");
            foreach ($entries as $section) {
                $entity = $section->getAttribute('entity');
                if ($entity) println($entity,1,TERM_BLUE); //analizeFields($domx, $section, $entity) ;
                //
                recAnalizeStruct($domx, $section, 1);
            }

            // TODO автосоздание черновика для проверки save/load

            if ($draft) $param = "?urn={$draft->urn}";

            echo("<p>{$shortPath}/<a href='/processformtest/{$shortPath}/{$shortName}{$param}'><b>{$shortName}</b></a> draft {$draft->urn}</p>");

            echo '</div>';
        }
    }
  }
  catch (Exception $e)
  {
    println($e,1,TERM_RED);
  }
}

//if (count($errors))
{
    print "<br><br><br>";
    printH("Errors: " . $errors);
    print "<br><br><br>";
}

?>

<script>
    GC.ONLOAD.push(function (e) {
        setTimeout(function() {
            window.location.hash = 'error';
        }, 100);
    });
</script>
