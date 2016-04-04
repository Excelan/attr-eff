<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);

class SetUnsetTest implements TestCase
{
    private $urn;

    public function doSet()
    {
        $m = new Message();
        $m->urn = 'urn:Directory:UKDState:IssueRecord';
        $m->action = 'create';
        $m->issued = 'yes';
        $m->withdrawal = 'no';
        $r = $m->deliver();
        $o = $r->urn->resolve();
        $this->urn = $o->urn;
        assertEqual($o->issued, 'yes');
        assertEqual($o->withdrawal, 'no');
    }

    /**
    Работает любой вариант, кроме одного из валидных значений set. В базе он станет равен int 0
    */
    public function doUnset()
    {
        $m = new Message();
        $m->urn = $this->urn;
        $m->action = 'update';
        $m->issued = null;
        $m->issued = 0;
        $m->issued = '0';
        $r = $m->deliver();
    }

    public function check()
    {
        $o = $this->urn->resolve();
        assertNull($o->issued);
        assertEqual($o->withdrawal, 'no');
    }

    public function doSet2()
    {
        $m = new Message();
        $m->urn = $this->urn;
        $m->action = 'update';
        $m->issued = 'no';
        $r = $m->deliver();
    }

    public function check2()
    {
        $o = $this->urn->resolve();
        assertEqual($o->issued, 'no');
        assertEqual($o->withdrawal, 'no');
    }
}
