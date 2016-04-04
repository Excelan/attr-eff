<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB',TRUE);
define('PRODUCTION_DB_IN_TEST_ENV',TRUE);

class RemapVisantsTest implements TestCase
{
    private $docID = 672526831;
    private $mpeid = 9291028;
    private $actorVisant = 'urn:Management:Post:Individual:1118804000';
    private $actorVisaCanceler = 'urn:Management:Post:Individual:680501005';
    private $actorVisantEmployee = 'urn:People:Employee:Internal:1118804000';
    private $actorVisaCancelerEmployee = 'urn:People:Employee:Internal:680501005';

    /**
     * called from Java DecisionIn
     * на входе в процесс с визированием (DraftOut) (или на Document.createDraft?) - создаем DecisionSheet, наполняем византами из прототипа, каждому византу делаем MPETicket
     * на входе в этап визирования процесса визирования - лист уже есть
     * после неуспешного визирования - лист в неактивные, создание нового активного листа, на основании старого листа
     * при повторном входе в визирование - лист уже есть
     */
    function doit()
    {
        pendingTest();
        $m = new Message();
        $m->gate = 'Process/RemapDecisionSheet';
        $m->subjectURN = 'urn:Document:Detective:C_IS:'.$this->docID;
        $m->mpeId = $this->mpeid;
        $m->rand = rand(100,999);
        $r = $m->send();
        printlnd($r);
        assertEqual($r['status'], 200);
    }

    function cleanup()
    {
        $m = new Message();
        $m->urn = 'urn:DMS:DecisionSheet:Signed:1183577022';
        $m->action = 'update';
        $m->hascancelfrom = [];
        $m->hassignfrom = [];
        $m->deliver();
    }

    /**
     * внесение византа в список давших визу
     * сравнение трех списков, если needsfrom и визировали+отказали по сумме стали равны - вызвать http complete stage от имени системы
     */
    function visa1() {
        /**
         * загрузить mpe процесс по mpeid
         * загрузить DecisionSheet по mpe.subject
         * внести византа в один из списков решений (если это последний голос, отправить команду на завершение процесса)
         */
        $m = new Message();
        $m->urn = "urn:Document:Detective:C_IS:{$this->docID}"; // UNUSED! subject got from mpe.subject
        $m->status = 'cancel';
        $m->text = 'Reason for cancellation';
        $m->mpeid = $this->mpeid;
        $m->actor = $this->actorVisaCanceler;
        $m->actorEmployee = $this->actorVisaCancelerEmployee;
        $m->gate = 'Decision/VisaByOne';
        $r = $m->send();
        printlnd($r);
        //
        $m = new Message();
        $m->urn = "urn:Document:Detective:C_IS:{$this->docID}"; // UNUSED! subject got from mpe.subject
        $m->status = 'visa';
        $m->mpeid = $this->mpeid;
        $m->actor = $this->actorVisant;
        $m->actorEmployee = $this->actorVisantEmployee;
        $m->gate = 'Decision/VisaByOne';
        $r = $m->send();
        printlnd($r);
    }

    function failit()
    {
        pendingTest();
        $m = new Message();
        $m->gate = 'Process/RemapDecisionSheet';
        $m->subjectURN = 'urn:Document:Complaint:C_IS:0';
        $m->mpeId = 345;
        $m->rand = rand(100,999);
        $r = $m->send();
        printlnd($r);
        assertEqual($r['status'], 400);
    }

}
