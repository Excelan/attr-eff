<?php
require dirname(__FILE__).'/../../goldcut/boot.php';

define('DEBUG_SQL',true);

class CRUD3Test implements TestCase
{
    function createUser()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:User:System';
        $m->action = 'create';
        $m->id = 100;
        $m->email = "test@";
        $r = $m->deliver();
        println($r);
    }

    function createRole()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System';
        $m->id = 1;
        $m->title = "Roletitle";
        $m->action = 'create';
        $r = $m->deliver();
        println($r);
    }

    function updateRole()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System:1';
        $m->title = "Roletitle up";
        $m->action = 'update';
        $r = $m->deliver();
        println($r);
    }

    function updateUO()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:User:System:100';
        $m->ActorRoleSystem = "urn:Actor:Role:System:1";
        //$m['Actor:Role:System'] = "urn:Actor:Role:System:1";
        $m->action = 'update';
        $r = $m->deliver();
        println($r);
    }

    function loadAll()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System';
        $m->action = 'load';
        $r = $m->deliver();
        assertDataSetSize($r,1);
        println($r->current());
    }

    function createOAuthLink()
    {
        $m = new Message();
        $m->urn = 'urn:OAuth:Link:UserId';
        $m->action = 'create';
        $m->id = 9;
        $m->userid64 = "1234567890";
        $m->oauth2service = "fb";
        $m->ActorUserSystem = 'urn:Actor:User:System:100';
        $r = $m->deliver();
        println($r);
    }

    function updateBT()
    {
        //pendingTest();
        $m = new Message();
        $m->urn = 'urn:Actor:User:System:100';
        $m->ActorRoleSystem = "urn:Actor:Role:System:1";
        $m->action = 'update';
        $r = $m->deliver();
        println($r);
    }

    function load1()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System';
        $m->action = 'load';
        $r = $m->deliver();
        assertDataSetSize($r,1);
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System:1';
        $m->action = 'load';
        $r = $m->deliver();
        assertDataSetSize($r,1);
    }

    function loadTree()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:User:System:100';
        $m->action = 'load';
        $m->ActorRoleSystem = "urn:Actor:Role:System:1";
        $r = $m->deliver();
        assertDataSetSize($r,1);
        $user = $r->current();
        println($user);
        // ho
        $role = $user->ActorRoleSystem;
        println($role);
        assertDataSetSize($role,1);
        // hm with alias
        $oauth2link = $user->oauth2link;
        assertDataSetSize($oauth2link,1);
        println($oauth2link);
        // bt
        $reverseUser = $oauth2link->ActorUserSystem;
        println($reverseUser);
        assertDataSetSize($reverseUser,1);
    }

    function removeRole()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System:1';
        $m->action = 'delete';
        $r = $m->deliver();
        println($r);
    }

}