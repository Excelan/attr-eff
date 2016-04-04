<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);

class NewMediaTest implements TestCase
{
    public function doit()
    {
        $file = BASE_DIR.'/original/2003491964.jpg';
        $m = new Message();
        $m->action = 'create';
        $m->urn = 'urn:Media:UniversalImage:Container';
        //if ($sess->user) $m->user = $sess->user;
        $m->file = $file;
        $m->uri = basename($file);
        $r = $m->deliver();
        println($r);
        $uimage = $r->urn->resolve()->current();
        println($uimage);
        printlnd($uimage->image);
        printlnd($uimage->thumb);
        printlnd($uimage->thumb['uri']);
        //printlnd($uimage->thumbnail);
        print "<img src='{$uimage->thumb['uri']}'><br>";
        print "<img src='{$uimage->image['uri']}'>";
    }
}
