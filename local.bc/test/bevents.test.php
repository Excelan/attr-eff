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

class BusinessEventsTest implements TestCase
{

  private $eventurn;
  private $mpeurn;

  /**
  периодичность обслуживания Бизнес Объектов
  */
  function bo_to_events()
  {
    $m = new \Message();
    $m->action = 'load';
    $m->urn = 'urn:BusinessObject:Record:Polymorph';
    $bos = $m->deliver();
    foreach ($bos as $bo)
    {
      $periodicitymaintenanceEverynmonth = $bo->periodicitymaintenance->everynmonth;
      if ($periodicitymaintenanceEverynmonth)
      {
        println('$periodicitymaintenanceEverynmonth '.$periodicitymaintenanceEverynmonth);
        // TODO start process TO
      }
      $periodicityvalidationEverynmonth = $bo->periodicityvalidation->everynmonth;
      if ($periodicityvalidationEverynmonth)
      {
        println('$periodicityvalidationEverynmonth '.$periodicityvalidationEverynmonth);
        // TODO start process protocol Validation
      }
      $periodicityverificationEverynmonth = $bo->periodicityverification->everynmonth;
      $periodicitycalibrationEverynmonth = $bo->periodicitycalibration->everynmonth;
    }
  }


  /**
  периодичность пересмотр договоров
  */
  function contract_renew_to_events()
  {
    foreach (['BW','LC','LOP','LWP','MT','RSS','SS','TMC','TME'] as $code)
    {
      $m = new \Message();
      $m->action = 'load';
      $m->urn = 'urn:Document:Contract:'.$code;
      $bos = $m->deliver();
      foreach ($bos as $contract)
      {
        if ($contract->prolongation != 'agreement') continue; // =auto
        $dateStart = $contract->date;
        printlnd($dateStart);
        $monthesToEnd = $contract->timenotifyfor->everynmonth; // срок действия в месяцах
        println($monthesToEnd);
        $monthesToNotifyForRenew = $contract->timenotifyfor->everynmonth; // при пересмотре удедомить за
        println($monthesToNotifyForRenew);
      }
    }
  }
  /**
  ивенты с процесса
  */
  function inprocess_new_events()
  {
    pendingTest();
  }

  /**
  создать нечеткий ивент с указанием месяца длпланирования, который позже инициирует процесс
  */
  function newFuzzyEvent()
  {
    $m = new \Message();
    $m->action = 'create';
    $m->urn = 'urn:Event:ProcessExecutionPlanned:Staged';
    $m->planningresponsible = 'urn:Management:Post:Individual:1118804000';
    $m->processproto = 'DMS:Process:SimpleWithPlan';
    $m->subjectproto = 'Document:Contract:TME'; // Протокол Валидации, ТО
    $m->isdateset = false; // нечеткий
    $m->ismpestarted = false; // процесс еще не инициирован
    $m->eventyear = 2016;
    $m->eventmonth = 2;
    //$m->ManagedProcessExecutionRecord = '';
    $r = $m->deliver();
    $this->eventurn = $r->urn;
  }
  /**
  в пришедший месяц перевести в четкий ивент, дав точную дату события
  */
  function fuzzyToConcreteDateEvent()
  {
    $m = new \Message();
    $m->action = 'update';
    $m->urn = $this->eventurn;
    $m->eventdate = '2016-02-20';
    $m->isdateset = true; // четкий
    $m->deliver();
  }

  /**
  в пришедшую дату начать процесс. Кто инициатор?
  */
  function initProcessFromEvent()
  {
    $event = $this->eventurn->resolve();
    println($event->current());
    $processProto = $event->processproto;
    $subjectProto = $event->subjectproto;
    $initiator = (string) $event->planningresponsible->urn;
    $url = 'http://localhost:8020/startprocess/?prototype='.$processProto.'&initiator='.$initiator;
    if ($subjectProto) $url .= '&subjectPrototype='.$subjectProto;
    //println($url, 1, TERM_GREEN);
    $r = httpRequest($url, null, [], 'GET', 3);
    //println($r);
    assertEqual($r['httpcode'], 200);

    $mpeID = explode(':',$r['json']['upn'])[4];

    $m = new \Message();
    $m->action = 'update';
    $m->urn = $this->eventurn;
    $m->ismpestarted = true;
    $m->ManagedProcessExecutionRecord = 'urn:ManagedProcess:Execution:Record:'.$mpeID;
    $m->deliver();

    $this->mpeurn = new \URN('urn:ManagedProcess:Execution:Record:'.$mpeID);
  }

  function check()
  {
    //printlnd($this->mpeurn);
    assertURN($this->mpeurn);
    $mpe = $this->mpeurn->resolve();
    println($mpe->current());
    //pendingTest();
  }

  /**
  все ивенты, которые будут в интервале через 3 дня
  */
  function notifyEmail()
  {
    $m = new \Message();
    $m->action = 'load';
    $m->urn = 'urn:Event:ProcessExecutionPlanned:Staged';
    $m->isdateset = true;
    $m->ismpestarted = false;
    $m->eventyear = date('Y');
    $m->eventmonth = 2; // (int) date('m');
    $m->eventdate = ['2016-01-01','2016-03-02']; // TODO
    $eventsToNotify = $m->deliver();
    foreach ($eventsToNotify as $eventToNotify)
    {
      println($eventToNotify);
      // TODO send email
    }
  }

}
