<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);
//define('NOMIGRATE',TRUE);
define('NOCLEARDB', true);
define('PRODUCTION_DB_IN_TEST_ENV', true);

set_time_limit(10);

class DMSUniversalDocTest implements TestCase
{
    private $urn;

    public function createDoc()
    {
        $m = new \Message();
        $m->action = 'create';
        $m->urn = 'urn:Document:Detective:C_IS';
        $m->privatedraft = 'f';
        $m->complaintstatus = true;
        $m->conclusion = 'Text conclusion';
        $m->initiator = 'urn:Management:Post:Individual:1118804000';
        $this->urn = $m->deliver()->urn;
    }

    public function onUpdate()
    {
        $m = new \Message();
        $m->action = 'update';
        $m->urn = $this->urn;
        $m->deliver();
    }

    public function onUpdate2()
    {
        $m = new \Message();
        $m->action = 'update';
        $m->urn = $this->urn;
        $m->vised = 't';
        $m->deliver();
    }

    public function check()
    {
        $doc = $this->urn->resolve();
        $DMSDocumentUniversal = $doc->DMSDocumentUniversal;
        assertDataSetSize($DMSDocumentUniversal, 1);
        assertEqual($DMSDocumentUniversal->vised, 't');
    }

  /**
  access for - initiator, visants, approver, reviewer
  no access for - considerators
  no access in nested non docs for - dower, reviewer
  client access -
  */
  public function rbacTest()
  {
  }
}
