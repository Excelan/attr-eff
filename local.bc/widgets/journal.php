<?php

function widget_journal($options)
{
    /*
    Обязательно:
    $urn - урн документа к которому пренадлежит журнал
    _________________________________________________________________
    Опционально:
    */


    extract($options);
    //Log::debug($options, 'slow');

    $arrJournal  = '';

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:ManagedProcess:Journal:Record';
    //$m->ManagedProcessExecutionRecord = $mpe->urn;
    $m->subject = (string) $urn;
    $journals = $m->deliver();
    Log::debug((string)$m, 'slow');
    Log::debug((string)$journals, 'slow');

    foreach ($journals as $journal) {
        $event = '';
        $time = '';
        $time2 = '';

        $journal_ManagedProcessExecutionRecord = $journal->ManagedProcessExecutionRecord;

        if ($journal_ManagedProcessExecutionRecord->subject == $urn) {
            $time = date('H:i', strtotime($journal_ManagedProcessExecutionRecord->created));
            $time2 = date('Y.m.d', strtotime($journal_ManagedProcessExecutionRecord->created));

            $postTitle = '';
            $nameTitle = '';

            if (strlen(trim($journal->actor)) > 0) {
                if (strpos($journal->actor, 'Post')) {
                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = $journal->actor;
                    $post = $m->deliver();

                    $postTitle = $post->title;


                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:People:Employee:Internal';
                    $m->ManagementPostIndividual = $journal->actor;
                    $name = $m->deliver();

                    $nameTitle = $name->title;
                } else {
                    if ($journal->actor == 'urn:Actor:User:System:0' || $journal->actor == 'urn:Actor:User:System:1' || $journal->actor == 'urn:Actor:AI:System:1') {
                        $postTitle = 'Система';
                        $nameTitle = 'System user';
                    } else {
                        println($journal->actor);
                        $m = new Message();
                        $m->action = 'load';
                        $m->urn = 'urn:People:Employee:Internal';
                        $m->ManagementPostIndividual = $journal->actor;
                        $name = $m->deliver();
                        $nameTitle = $name->title;

                        $postTitle = $name->ManagementPostIndividual->title;
                    }
                }
            } else {
                if (strpos($journal_ManagedProcessExecutionRecord->initiator, 'Post')) {
                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = $journal_ManagedProcessExecutionRecord->initiator;
                    $post = $m->deliver();

                    $postTitle = $post->title;


                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:People:Employee:Internal';
                    $m->ManagementPostIndividual = $journal_ManagedProcessExecutionRecord->initiator;
                    $name = $m->deliver();

                    $nameTitle = $name->title;
                } else {
                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:People:Employee:Internal';
                    $m->ManagementPostIndividual = $journal_ManagedProcessExecutionRecord->initiator;
                    $name = $m->deliver();
                    $nameTitle = $name->title;


                    $postTitle = $name->ManagementPostIndividual->title;
                }
            }

            $pageNavTitle = $GLOBALS['MPE'][$journal_ManagedProcessExecutionRecord->prototype][$journal->stage];

            $event = <<<HTML
                <div class="journalRow">
                   <div class="when">
                        <p><span>{$time2}</span></p>
                    </div>
                    <div class="whouser">
                        <p class="name">{$nameTitle}</p>
                        <p class="post">{$postTitle}<span>{$time}</span></p>
                    </div>
                    <div class="event">
                        <p>{$pageNavTitle}</p>
                    </div>
                </div>
HTML;
        }
        $arrJournal .= $event;
    }


    if($level == 'FirstLevel')
    echo "
           {$arrJournal}
        ";
    else
        echo "<div class='journalRow'>
                <p style='text-align: center;'><span>Нет прав для просмотра</span></p>
              </div>";
}
