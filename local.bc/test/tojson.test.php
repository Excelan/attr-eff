<?php
require dirname(__FILE__) . '/../goldcut/boot.php';

define('DEBUG_SQL',true);

class ToJsonTest implements TestCase
{
    function createUser()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:User:System';
        $m->action = 'create';
        $m->id = 100;
        $m->email = "test@x.com";
        $r = $m->deliver();
        //println($r);
    }

    function createRole()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System';
        $m->id = 1;
        $m->title = "Roletitle";
        $m->action = 'create';
        $r = $m->deliver();
        //println($r);
    }

    function updateRole()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:Role:System:1';
        $m->title = "Roletitle up";
        $m->action = 'update';
        $r = $m->deliver();
        //println($r);
    }

    function updateUO()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:User:System:100';
        $m->ActorRoleSystem = "urn:Actor:Role:System:1";
        //$m['Actor:Role:System'] = "urn:Actor:Role:System:1";
        $m->action = 'update';
        $r = $m->deliver();
        //println($r);
    }


    public function initUsers()
    {
        $m = new Message('{"action":"create","urn":"urn:Actor:User:System","id":123,"name":"N123","email":"1@test.com"}');
        $m->deliver();
        $m = new Message('{"action":"create","urn":"urn:Actor:User:System","id":456,"name":"N456","email":"2@test.com"}');
        $m->deliver();
        $m = new Message('{"action":"create","urn":"urn:Actor:User:System","id":789,"name":"N789","email":"3@test.com"}');
        $m->deliver();
        $m = new Message('{"action":"create","urn":"urn:Actor:User:System","id":710,"name":"N710","email":"31@test.com"}');
        $m->deliver();
        $m = new Message('{"action":"create","urn":"urn:Actor:User:System","id":990,"name":"N990","email":"4@test.com"}');
        $m->deliver();
        $this->urns['user1'] = 'urn:Actor:User:System:123';
        $this->urns['user2'] = 'urn:Actor:User:System:456';
        $this->urns['user3'] = 'urn:Actor:User:System:789';
        $this->urns['user4'] = 'urn:Actor:User:System:990';
    }

    public function add_user_to_Followings()
    {
        $m = new Message();
        $m->action = 'add';
        $m->urn = 'urn:Actor:User:System:456';
        $m->to = new URN('urn:Actor:User:System:789:following');
        $m->deliver();
    }

    public function add_user_to_Followings_2()
    {
        $m = new Message();
        $m->action = 'add';
        $m->urn = 'urn:Actor:User:System:456';
        $m->to = new URN('urn:Actor:User:System:990:following');
        $m->deliver();
    }

    public function add_user_to_Followings_3()
    {
        $m = new Message();
        $m->action = 'add';
        $m->urn = 'urn:Actor:User:System:710';
        $m->to = new URN('urn:Actor:User:System:990:following');
        $m->deliver();
    }



    function loadAll()
    {
        $m = new Message();
        $m->urn = 'urn:Actor:User:System:990';
        $m->action = 'load';
        $r = $m->deliver();
        //assertDataSetSize($r,1);
        //println($r->current()->toJSON());
        //println($r->current()->toArray(['ActorRoleSystem']));
        $a = $r->current()->toArray('following');
//        $a = $r->current()->toArray();
        println($a);
        println($a['following']);
    }



}