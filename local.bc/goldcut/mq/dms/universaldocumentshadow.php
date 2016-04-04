<?php
function after_update_any_dms($m)
{
    $old = $m[1]; // DataRow
    $new = $m[0]; // Message

    if ($old->entity->manager != 'Document') {
        return;
    }

    try {
        $newDoc = $new->urn->resolve()->current();

        //  АДДЕЙТИЛА ЖАВА ДО НАС, не сможем сравнить $old->privatedraft == 't'
        if ($newDoc->privatedraft == 'f') {
            //  && $old->entity->manager == 'Document'

            \Log::info('NON DRAFT, WILL SHADOW '.$newDoc->urn, 'setstate');

            $indexableText = "Full text gathered"; // TODO

            if ($unidocpoly = $newDoc->DMSDocumentUniversal) {
                // _id полей больше нет в DCT версии cms

                $created = false;
                if (is_int($unidocpoly)) {
                    $unidocURN = "urn:DMS:Document:Universal:{$unidocpoly}";
                } else {
                    $unidocURN = $unidocpoly->urn;
                }
            } else {
                $created = true;
                // прототип нужен для получения префикса по типу к коду документа и title для его заголовка
                $m = new \Message();
                $m->action = 'load';
                $m->urn = 'urn:Definition:Prototype:Document';
                $m->indomain = $newDoc->urn->entity->prototype->getInDomain();
                $m->ofclass = $newDoc->urn->entity->prototype->getOfClass();
                $m->oftype = $newDoc->urn->entity->prototype->getOfType();
                $definitionProto = $m->deliver();
                // code generate
                $code = $definitionProto->oftype . '-' . mt_rand(100000, 999999); // code !+ id
                $prototypeTitle = $definitionProto->title;
                $shadowTitle = $newDoc->title ? $newDoc->title : $prototypeTitle;     // или использовать title в самом документе или взять общий title прототипа
            }

            $mm = new Message();
            if (!$unidocpoly) {
                // CREATE SHADOW, new CODE
                \Log::info('CREATE SHADOW', 'setstate');
                $mm->action = 'create';
                $mm->urn = 'urn:DMS:Document:Universal';
                $mm->code = $code;
                $mm->title = $shadowTitle;
            } else {
                // UPDATE SHADOW
                \Log::info('UPDATE SHADOW STATE', 'setstate');
                $mm->action = 'update';
                $mm->urn = $unidocURN;
                //$mm->code = $code; // TODO temp чтобы дать коды и тайлы уже созданным документам
                //$mm->title = $shadowTitle; // TODO temp
            }
            // основная часть создания или обновления shadow unidoc
            $mm->document = (string) $old->urn;
            $mm->indexabletext = $indexableText;
            $mm->initiator = (string)$newDoc->initiator;
            $mm->vised = $newDoc->vised;
            $mm->approved = $newDoc->approved;
            $mm->done = $newDoc->done;
            $mm->archived = $newDoc->archived;
            $mm->process = $newDoc->process;
            $mm->parent = $newDoc->parent;
            $cou = $mm->deliver(); // DMSDocumentUniversal
            //
            if ($created) {
                $unidocURN = $cou->urn;
                // документу установить ссылку на универсальный документ
                $mu = new Message();
                $mu->urn = $newDoc->urn;
                $mu->action = 'update';
                $mu->DMSDocumentUniversal = $unidocURN;
                $mu->code = $code;
                $mu->_skipmq = true;
                $mu->deliver();

                // в shadow установить обратную сслыыку на документ (в виде urn строки)
                $mu = new Message();
                $mu->urn = $unidocURN;
                $mu->action = 'update';
                $mu->document = $newDoc->urn;
                $mu->_skipmq = true;
                $mu->deliver();
            }
        }
    } catch (Exception $e) {
        \Log::error($e->getMessage(), 'setstate');
    }
}

$broker = Broker::instance();
$broker->queue_declare("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
$broker->bind("ENTITY", "ENITYUPDATECONSUMER", "after.update");
$broker->bind_rpc("ENITYUPDATECONSUMER", "after_update_any_dms");
