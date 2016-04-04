<?

function formDataManageListOfItemsIn($d, $entityList, $subjectURN)
{
  foreach ($entityList as $key) {
      foreach ($d->$key as $listitem) {
          if (!$listitem) continue;
          $listURN = $subjectURN . ':' . $key;
          $m = new Message();
          $m->action = 'exists';
          $m->urn = $listitem;
          $m->in = $listURN;
          Log::info($m, 'unisave');
          $e = $m->deliver();
          if (!$e->exists) {
              $m = new Message();
              $m->action = 'add';
              $m->urn = $listitem;
              $m->to = $listURN;
              Log::info($m, 'unisave');
              $m->deliver();
          } else
              Log::info($e, 'unisave');
      }
  }
}

function formDataManageListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN)
{
  foreach ($ListOfEntityDir as $key => $entity) {
      foreach ($d->$key as $listitem) {
          $newItem = false;
          $m = new Message((array)$listitem);
          if ($listitem->urn)
              $m->action = "update";
          else {
              $m->urn = "urn:{$entity}";
              $m->action = "create";
              $newItem = true;
          }
          Log::info($m, 'unisave');
          $savedItem = $m->deliver();
          Log::debug($savedItem, 'unisave');
          if ($newItem == true) {
              $m = new Message();
              $m->action = 'add';
              $m->urn = $savedItem->urn;
              $m->to = $subjectURN . ':' . $key; // add to
              $added = $m->deliver();
              Log::debug("created $key added to list $added", 'unisave');
          } else {
              Log::debug("Old $key not need to add to list", 'unisave');
          }
      }
  }
}


function formDataManageHasmanyOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN,$document)
{
    foreach ($ListOfEntityDir as $key => $entity) {
        foreach ($d->$key as $listitem) {
            $newItem = false;
            $m = new Message((array)$listitem);
            if ($listitem->urn)
                $m->action = "update";
            else {
                $m->urn = "urn:{$entity}";
                $m->action = "create";
                $m->set($document,$subjectURN);
            }
            Log::info($m, 'unisave');
            $savedItem = $m->deliver();

        }
    }
}

function formDataManageHasmanyListOfEditableItemsIn($d, $ListOfEntityDir, $subjectURN,$document){
    foreach ($ListOfEntityDir as $key => $entity) {
        foreach ($d->$key as $listitem) {

            $m = new Message((array)$listitem);
            if ($listitem->urn)
                $m->action = "update";
            else {
                $m->urn = "urn:{$entity}";
                $m->action = "create";
            }
            $m->set($document,(string)$subjectURN);
            $savedItem = $m->deliver();

            $listURN = (string)$subjectURN . ':' . $key;

            $m = new Message();
            $m->action = 'exists';
            $m->urn = (string)$savedItem->urn;
            $m->in = new URN($listURN);
            $ex = $m->deliver();

            if (!$ex->exists)
            {
                $m = new Message();
                $m->action = 'add';
                $m->urn = (string)$savedItem->urn;
                $m->to = $listURN;
                $m->deliver();
            }

        }
    }
}

function updateMPEMetadata($mpe, $addmetadata)
{
  $metadata = $mpe->metadata;
  foreach ($addmetadata as $k => $v)
  {
    $metadata->$k = $v;
  }
  $m = new \Message();
  $m->action = 'update';
  $m->urn = $mpe->urn;
  $m->metadata = $metadata;
  $m->deliver();
}

function getInMPEByTicketURN($ticketurn)
{
  // ticket
  $m = new Message();
  $m->action = 'load';
  $m->urn = $ticketurn;
  $ticket = $m->deliver();

  // mpe
  $mpe = $ticket->ManagedProcessExecutionRecord;
  /*
  $m = new Message();
  $m->action = 'load';
  $m->urn = 'urn:ManagedProcess:Execution:Record';
  $m->id = (string)$ticket->ManagedProcessExecutionRecord->id;
  $mpe = $m->deliver();
  */
  return $mpe;
}

function getMPEAndSubjectByMpeID($MPE_ID)
{
    if (!$MPE_ID) throw new Exception('No mpe id param');

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:ManagedProcess:Execution:Record';
    $m->id = $MPE_ID;
    $mpe = $m->deliver();
    if (count($mpe)==1) $mpe = $mpe->current();
    else throw new Exception('No mpe by id '.$MPE_ID);

    //Log::debug('MPE', 'processrouter');
    //Log::debug((string)$mpe->current(), 'processrouter');
    //Log::debug('subject:'.(string)$mpe->subject, 'processrouter');

    $subjectURN = new URN((string)$mpe->subject);
    $subject = $subjectURN->resolve()->current();

    return [$mpe, $subject];
}


function getParentMPEAndSubjectByMpe($MPEOBJ)
{
  // TODO
}







function parse_html_for_document($html_string) {

    require_once BASE_DIR.'/lib/simple_html_dom/simple_html_dom.php';

    //создаём новый объект
    $html = new simple_html_dom();

    //загружаем в него данные
    $html = str_get_html($html_string);

    if ( !$html ) return '';
    if ( $html->innertext == '' ) return '';

    //находим все ссылки на странице и...
    $ul_list = $html->find('ul', 0);

    $result = get_li($ul_list, array());

    //освобождаем ресурсы
    $html->clear();
    unset($html);

    return $result;
}

function get_li($ul, $result) {

    foreach ($ul->children as $li) {

        if ( $li->tag != 'li' ) continue;

        foreach ($li->children as $li_child) {

            if ( $li_child->tag == 'ul' ) {

                array_push($result, array('ul' => get_li($li_child, array())));

            } else if ( $li_child->getAttribute('already_parsed') != 1 )  {

                array_push($result, array('li' => $li_child->plaintext));
                $li_child->setAttribute('already_parsed', 1);
            }
        }
    }

    return $result;
}


function create_all_docclass_doctype() {

    $docclass_list = array(

        array('uri_class' => 'capa', 'title_class' => 'САРА', 'types' => array()),

        array('uri_class' => 'requisition', 'title_class' => 'Заявки', 'types' => array(
            array('uri_type' => 'REQ001', 'title_type' => 'заявка на установку ПО'),
            array('uri_type' => 'REQ002', 'title_type' => 'заявка на доработку ПО'),
            array('uri_type' => 'REQ003', 'title_type' => 'заявка на установку компьютерной техники'),
            array('uri_type' => 'REQ004', 'title_type' => 'заявка на создание/удаление корпоративного почтового ящика'),
            array('uri_type' => 'REQ005', 'title_type' => 'заявка на выдачу/изъятие пластиковой карточки / изменение категории доступа СКД'),
            array('uri_type' => 'REQ006', 'title_type' => 'заявка на изменение в параметрах доступа в СКД'),
            array('uri_type' => 'REQ007', 'title_type' => 'заявка на выдачу/удаление доступа к интернет ресурсам'),
            array('uri_type' => 'REQ008', 'title_type' => 'заявка на поставку материалов, поставку и установку оборудования, проведение работ'),
            array('uri_type' => 'REQ009', 'title_type' => 'заявка на получение расходных материалов'),
            array('uri_type' => 'REQ010', 'title_type' => 'заявка на закрепление/перезакрепление ОС, НМА в 1С'),
            array('uri_type' => 'REQ011', 'title_type' => 'заявка на создание нового регламентирующего документа'),
            array('uri_type' => 'REQ012', 'title_type' => 'заявка на внесение изменений в регламентирующий документ'),
            array('uri_type' => 'REQ013', 'title_type' => 'заявка на выдачу/изъятие копий документов'),
            array('uri_type' => 'REQ014', 'title_type' => 'заявка на проведение валидационных исследований / аттестации / квалификации / калибровки или ТО'),
            array('uri_type' => 'REQ015', 'title_type' => 'заявка на обучение и аттестацию'),
            array('uri_type' => 'REQ016', 'title_type' => 'заявка на проведение самоинспекции / аудита'),
            array('uri_type' => 'REQ017', 'title_type' => 'заявка на создание САРА'),
            array('uri_type' => 'REQ018', 'title_type' => 'заявка на доставку груза'),
            array('uri_type' => 'REQ019', 'title_type' => 'заявка на доставку документов'),
            array('uri_type' => 'REQ020', 'title_type' => 'заявка на транспорт для служебных поездок'),
            array('uri_type' => 'REQ021', 'title_type' => 'заявка на договор или дополнение к договору'),
            array('uri_type' => 'REQ022', 'title_type' => 'универсальная заявка'),
            array('uri_type' => 'REQ023', 'title_type' => 'заявка на отгрузку'),
            array('uri_type' => 'REQ024', 'title_type' => 'заявка на приход'),
            array('uri_type' => 'REQ025', 'title_type' => 'заявка на перемещение'),
            array('uri_type' => 'REQ026', 'title_type' => 'заявка на переупаковку'),
            array('uri_type' => 'REQ027', 'title_type' => 'заявка на изменение статуса'),
            array('uri_type' => 'REQ028', 'title_type' => 'заявка на согласование бюджета или внепланового платежа'),
            array('uri_type' => 'REQ029', 'title_type' => 'заявка на осуществление платежа')
        )),

        array('uri_class' => 'contract', 'title_class' => 'Договора', 'types' => array(
            array('uri_type' => 'CON001', 'title_type' => 'договор аренды складских помещений'),
            array('uri_type' => 'CON002', 'title_type' => 'сервисный договор'),
            array('uri_type' => 'CON003', 'title_type' => 'договор о предоставлении услуг логистического комплекса, автостоянки'),
            array('uri_type' => 'CON004', 'title_type' => 'договор выполнения подрядных работ'),
            array('uri_type' => 'CON005', 'title_type' => 'договор на ТО и ремонт оборудования'),
            array('uri_type' => 'CON006', 'title_type' => 'договор на ТО холодильников, кондиционеров'),
            array('uri_type' => 'CON007', 'title_type' => 'договор на закупку материалов'),
            array('uri_type' => 'CON008', 'title_type' => 'договор на оказание услуг ТЛС и СТЗ'),
            array('uri_type' => 'CON009', 'title_type' => 'договор на оказание регулярных услуг'),
            array('uri_type' => 'CON010', 'title_type' => 'договор аренды офисных помещений'),
            array('uri_type' => 'CON011', 'title_type' => 'другие')
        )),

        array('uri_class' => 'contractsupplement', 'title_class' => 'Дополнения к договорам', 'types' => array(
            array('uri_type' => 'CONSUPP001', 'title_type' => 'дополнение к договору аренды складских помещений'),
            array('uri_type' => 'CONSUPP002', 'title_type' => 'дополнение к сервисному договору'),
            array('uri_type' => 'CONSUPP003', 'title_type' => 'дополнение к договору о предоставлении услуг логистического комплекса, автостоянки'),
            array('uri_type' => 'CONSUPP004', 'title_type' => 'дополнение к договору выполнения подрядных работ'),
            array('uri_type' => 'CONSUPP005', 'title_type' => 'дополнение к договору на закупку материалов'),
            array('uri_type' => 'CONSUPP006', 'title_type' => 'дополнение к договору на оказание услуг'),
            array('uri_type' => 'CONSUPP007', 'title_type' => 'дополнение к договору аренды офисных помещений'),
            array('uri_type' => 'CONSUPP008', 'title_type' => 'другие')
        )),

        array('uri_class' => 'selfinspection', 'title_class' => 'Самоинспекции', 'types' => array(
            array('uri_type' => 'SELFINS001', 'title_type' => 'самоинспекция')
        )),

        array('uri_class' => 'applicationform', 'title_class' => 'Заявления', 'types' => array(
            array('uri_type' => 'APPFORM001', 'title_type' => 'заявление на прием на работу'),
            array('uri_type' => 'APPFORM002', 'title_type' => 'заявление на отпуск'),
            array('uri_type' => 'APPFORM003', 'title_type' => 'заявление на перевод на другую должность'),
            array('uri_type' => 'APPFORM004', 'title_type' => 'заявление на увольнение (по согласованию сторон, по собственному желанию)')
        )),

        array('uri_class' => 'specify', 'title_class' => 'Регламентирующие документы', 'types' => array(
            array('uri_type' => 'SPEC001', 'title_type' => 'СОП'),
            array('uri_type' => 'SPEC002', 'title_type' => 'инструкция'),
            array('uri_type' => 'SPEC003', 'title_type' => 'должностная инструкция'),
            array('uri_type' => 'SPEC004', 'title_type' => 'кадровые приказы (прием, увольнение, отпуск, перевод)'),
            array('uri_type' => 'SPEC005', 'title_type' => 'другие приказы'),
            array('uri_type' => 'SPEC006', 'title_type' => 'положение'),
            array('uri_type' => 'SPEC007', 'title_type' => 'программа обучения и аттестации'),
            array('uri_type' => 'SPEC008', 'title_type' => 'мастер-план для валидации'),
            array('uri_type' => 'SPEC009', 'title_type' => 'программа валидации')
        )),

        array('uri_class' => 'specification', 'title_class' => 'Техническое задание (ТЗ)', 'types' => array(
            array('uri_type' => 'SPECIFIC001', 'title_type' => 'ТЗ на закупку материалов'),
            array('uri_type' => 'SPECIFIC002', 'title_type' => 'ТЗ на проведение работ')
        )),

        array('uri_class' => 'validationresearch', 'title_class' => 'Проведение валидационных исследований', 'types' => array(
            array('uri_type' => 'VLDRESEARCH001', 'title_type' => 'протокол валидационного исследования')
        )),

        array('uri_class' => 'maintaince', 'title_class' => 'Проведение технического обслуживания (ТО)', 'types' => array(
            array('uri_type' => 'MAINTNC001', 'title_type' => 'протокол технического обслуживания')
        )),

        array('uri_class' => 'complaint', 'title_class' => 'Жалобы', 'types' => array(
            array('uri_type' => 'COMPL001', 'title_type' => 'жалоба на брокерские услуги и ВМК'),
            array('uri_type' => 'COMPL002', 'title_type' => 'жалоба на услуги по обработке товаров'),
            array('uri_type' => 'COMPL003', 'title_type' => 'жалоба на услуги по хранению и учету товаров'),
            array('uri_type' => 'COMPL004', 'title_type' => 'жалоба на транспортно-экспедиционные услуги'),
            array('uri_type' => 'COMPL005', 'title_type' => 'жалоба другие логистические услуги'),
            array('uri_type' => 'COMPL006', 'title_type' => 'жалоба на работу инженерных систем / оборудование, состояние помещений'),
            array('uri_type' => 'COMPL007', 'title_type' => 'жалоба выполнение инженерно-технических работ'),
            array('uri_type' => 'COMPL008', 'title_type' => 'жалоба на работу ПО'),
            array('uri_type' => 'COMPL009', 'title_type' => 'жалоба на работу компьютерной техники'),
            array('uri_type' => 'COMPL010', 'title_type' => 'жалоба на уборку и гигиену'),
            array('uri_type' => 'COMPL011', 'title_type' => 'жалоба на поддержание и мониторинг температурного режима'),
            array('uri_type' => 'COMPL012', 'title_type' => 'жалоба на валидационные исследования, поверки, калибровки'),
            array('uri_type' => 'COMPL013', 'title_type' => 'жалоба на переупаковку товаров'),
            array('uri_type' => 'COMPL014', 'title_type' => 'жалоба на другие вопросы качества'),
            array('uri_type' => 'COMPL015', 'title_type' => 'универсальная жалоба')
        )),

        array('uri_class' => 'internalinvestigation', 'title_class' => 'Протоколы служебных расследовании', 'types' => array(
            array('uri_type' => 'INTENV001', 'title_type' => 'протокол служебного расследования')
        )),

        array('uri_class' => 'coordinationcontract', 'title_class' => 'Листы согласований договоров', 'types' => array(
            array('uri_type' => 'COORDCONTR001', 'title_type' => 'лист согласования договора')
        ))

    );

    foreach ($docclass_list as $dc) {

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:docclass';
        $m->uri = $dc['uri_class'];
        $docclass = $m->deliver();

        if ( count($docclass) ) continue;

        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:docclass';
        $m->uri = $dc['uri_class'];
        $m->title = $dc['title_class'];
        $docclass = $m->deliver();

        println("Create docclass - {$dc['uri_class']} - {$dc['title_class']}", 1, TERM_GREEN);

        foreach ($dc['types'] as $type) {

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:doctype';
            $m->uri = $type['uri_type'];
            $doctype = $m->deliver();

            if ( count($doctype) ) continue;

            $m = new Message();
            $m->action = 'create';
            $m->urn = 'urn:doctype';
            $m->docclass = $docclass->urn;
            $m->uri = $type['uri_type'];
            $m->title = $type['title_type'];
            $doctype = $m->deliver();

            println("Create doctype - {$type['uri_type']} - {$type['title_type']}", 2, TERM_GRAY);
        }
    }

}


?>
