<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('SCREENLOG', true);
define('ENABLE_CACHE_LOG', true);
define('DEBUG_SQL', true);
define('DEBUGLOGORIGIN', true);
define('NOCLEARCACHE', true);

class SaveNewUserTest implements TestCase
{
    function doit()
    {
        $d = '{"urn":"undefined","upn":"undefined","fio":"Tester name","email":"test@test.com","password":"petzea51","posttitle":"Tester","posttype":"urn:Management:Post:Group:1747184267","newposttype":"","istrener":"y","department":"urn:Company:Structure:Department:1344860689","isheadofdep":"y","dctstagerbac":[{"subjectprototype":"urn:Definition:Prototype:System:1985904795","processprototype":"urn:Definition:Prototype:System:1893695944","stage":"Ed"}],"processstartaccess":[{"processprototype":"urn:Definition:Prototype:System:1893695944","subjectprototype":"urn:Definition:Prototype:System:1804613985"},{"processprototype":"urn:Definition:Prototype:System:1893695944","subjectprototype":"urn:Definition:Prototype:System:925673655"}]}';
        $GLOBALS['SAVE_newuser'](json_decode($d));
    }

}
