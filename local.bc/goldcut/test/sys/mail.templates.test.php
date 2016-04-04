<?php
require dirname(__FILE__).'/../goldcut/boot.php';
define('DEBUG_SQL',TRUE);

class MailTemplatesTest implements TestCase
{
	private $urns = array();
    private $hash;

    function subscribe_incorrect_email()
    {
        $m = new Message();
        $m->urn = "urn-simplesubscription";
        $m->action = "subscribe";
        $m->email = "max#kievgrad.com";
        $r = $m->deliver();
        assertEqual($r->error, "incorrect_email");
    }

    function subscribe_duplicate_email()
    {
        pendingTest();
        $m = new Message();
        $m->urn = "urn-simplesubscription";
        $m->action = "subscribe";
        $m->email = "test@kievgrad.com";
        $r = $m->deliver();
        assertEqual($r->error, "duplicate_email");
    }

    function subscribe()
    {
        $m = new Message();
        $m->urn = "urn-simplesubscription";
        $m->action = "subscribe";
        $m->email = "max@kievgrad.com";
        $m->geo = array("urn-geo:2","urn-geo:5");
        $m->language = "ru";
        $r = $m->deliver();
        assertURN($r->urn);
        $this->urns['ns1'] = $r->urn;
    }

    private function check_new_subscriber()
    {
        $s = new Message();
        $s->urn = $this->urns['ns1'];
        $s->action = "load";
        $r = $s->deliver();
        $subscriber = $r->current();
        //printlnd($subscriber->geo->asURNs());
        assertFalse($subscriber->is('active'));
        $this->hash = $subscriber->hash;
    }

    function activate_new_subscriber()
    {
        $s = new Message();
        $s->urn = "urn-simplesubscription";
        $s->action = "mailactivate";
        $s->hash = $this->hash;
        $r = $s->deliver();
        // check
        $s = new Message();
        $s->urn = $this->urns['ns1'];
        $s->action = "load";
        $r = $s->deliver();
        $subscriber = $r->current();
        assertTrue($subscriber->is('active'));
    }

    function admin_activate()
    {
        pendingTest();
        $m = new Message();
        $m->urn = "urn-simplesubscription";
        $m->action = "adminactivate";
        $m->email = "test@kievgrad.com";
        $r = $m->deliver();
        $m->email = "maxim@kievgrad.com";
        $r = $m->deliver();
    }

    function list_subscibers()
    {
        $m = new Message();
        $m->action = "load";
        $m->urn = "urn-simplesubscription";
        $r = $m->deliver();
        assertDataSetSize($r,1);
        //foreach($r as $o) printlnd($o);
    }

    function list_subscibers_by_language()
    {
        pendingTest();
        $m = new Message();
        $m->action = "load";
        $m->urn = "urn-simplesubscription";
        $m->geo = "urn-geo-2";
        $m->language = "en";
        $r = $m->deliver();
        assertDataSetSize($r,1);
        //foreach($r as $o) printlnd($o);
    }

    function createMailtemplate()
    {
        $m = new Message('{"action":"create","urn":"urn-mailtemplate","id":123}');
        $m->title = 'mail title';
        $m->uri = 'testemailtemplate';
        $m->mailhtml = '<h1>Hello</h2> {{user.name}} {{ad.title}} {% list product %}
        <div>
            product: {{product.id}} {{product.exporttitle}} {{product.priceusd}}
        </div>
        {% end list %}
        ';
        $m->fromname = 'From';
        $m->fromemail = 'return@email.com';
        $m->deliver();
    }

    function createProducts()
    {
        $creator = 'urn:user-19';

        $m = new Message();
        $m->urn='urn:product';
        $m->id = 123;
        $m->action='create';
        $m->lang = 'ru';
        $m->exporttitle='t1';
        $m->user = $creator;
        $m->deliver();

        $m = new Message();
        $m->urn='urn:product';
        $m->id = 127;
        $m->action='create';
        $m->lang = 'ru';
        $m->exporttitle='t2';
        $m->user = $creator;
        $m->deliver();
    }

    function sendmail()
    {
        if (!file_exists(BASE_DIR."/views/layout/mailview.html")) throw new Exception('No views/layout/mailview.html');

        unlink(BASE_DIR."/log/".HOST."-mail-request@attracti.com.log");

        $user = new stdClass();
        $user->name = "MyName";
        $user->email = 'request@attracti.com';

        $ad = new stdClass();
        $ad->title = "AdTitle";

        $m = new Message();
        $m->urn = 'urn:product';
        $m->action = 'load';
        $ds = $m->deliver();

        $context = array('user' => $user, 'ad' => $ad, 'product' => $ds);
        // to - email
        // toname - to name
        // mailBaseTemplate - view/layout/mailBaseTemplate.html
        // mailContentTemplate - urn-mailtemplate by uri
        // context - for {{object.vars}}
        Mail::sendUserTemplatesContext($user, 'mailview', 'testemailtemplate', $context);
        echo "<iframe width='100%' height='500px' style='background-color: white;' src='/log/".HOST."-mail-request@attracti.com.log'></iframe>";
    }

	function subscribers()
	{
		pendingTest();
		/**
		$m = new Message();
		$m->action = "load";
		$m->urn = "urn:simplesubscription";
		//$m->geo = $_POST['geo'];
		//$m->lang = $_POST['lang'];
		$m->active = 1;
		$r = $m->deliever();
		//printlnd($m);
		//print_r( $r->asURNs() );
		foreach ($r as $subs)
		{
			println($subs->email);
			$to = $subs->email;
			$from = "white@icebrg.net";
			$namefrom = "FROM";
			$subject = "SUBJ";
			$message = $_POST['text'];
			$log = Mail::send( $from, $namefrom, $to, $nameto, $subject, $message );
			
		}
		*/
	}
	
}
?>