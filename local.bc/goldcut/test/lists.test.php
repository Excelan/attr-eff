<?php

require_once dirname(__FILE__).'/../../goldcut/boot.php';

define('DEBUG_SQL', true);
// define('USE_SQL_MAPPINGS_TABLE', true);

class ListsTest implements TestCase
{
    private $urns;

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

    public function initRole()
    {
        $m = new Message('{"action": "create", "urn": "urn:Actor:Role:System"}');
        $m->title = 'Role1';
        $m->id = 1001;
        $r = $m->deliver();
        $this->urns['role1'] = $r->urn;
        $m = new Message('{"action": "create", "urn": "urn:Actor:Role:System"}');
        $m->title = 'Role2';
        $m->id = 1002;
        $r = $m->deliver();
        $this->urns['role'] = $r->urn;
    }

    public function addRoleToUser()
    {
        //pendingTest();
        $m = new Message();
        $m->action = 'add';
        $m->urn = $this->urns['role1'];
        $m->to = new URN('urn:Actor:User:System:123:actas');
        println($m, 1, TERM_BLUE);
        $m->deliver();
    }

    public function add_user_to_Followings()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'add';
        $m->urn = 'urn:Actor:User:System:456';
        $m->to = new URN('urn:Actor:User:System:789:following');
        println($m, 1, TERM_BLUE);
        $m->deliver();
    }

    public function add_user_to_Followings_2()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'add';
        $m->urn = 'urn:Actor:User:System:456';
        $m->to = new URN('urn:Actor:User:System:990:following');
        println($m, 1, TERM_BLUE);
        $m->deliver();
    }

    public function add_user_to_Followings_3()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'add';
        $m->urn = 'urn:Actor:User:System:710';
        $m->to = new URN('urn:Actor:User:System:990:following');
        println($m, 1, TERM_BLUE);
        $m->deliver();
    }


    public function hasRoleInUser()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'exists';
        $m->urn = 'urn:Actor:Role:System:1001';
        $m->in = new URN('urn:Actor:User:System:123:actas');
        println($m, 1, TERM_BLUE);
        $e = $m->deliver();
        println($e, 1, TERM_BLUE);
        assertEqual($e->exists, 1);
    }

    public function hasUserInRole()
    {
//         pendingTest();
        $m = new Message();
        $m->action = 'exists';
        $m->urn = 'urn:Actor:User:System:123';
        $m->in = new URN('urn:Actor:Role:System:1001:delegatedto');
        println($m, 1, TERM_BLUE);
        $e = $m->deliver();
        assertEqual($e->exists, 1);
    }

    public function hasFollow()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'exists';
        $m->urn = 'urn:Actor:User:System:456';
        $m->in = new URN('urn:Actor:User:System:990:following');
        println($m, 1, TERM_BLUE);
        $e = $m->deliver();
        assertEqual($e->exists, 1);
    }





    public function loadList_Followers()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN('urn:Actor:User:System:456:followers');
        println($m, 1, TERM_BLUE);
        $listMembers = $m->deliver();
        println($listMembers, 1, TERM_VIOLET);
        println([789, 990], 2, TERM_VIOLET);
        assertEqual($listMembers->ids, [789, 990]);

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Actor:User:System';
        $m->in = $listMembers;
        $d = $m->deliver();
        println($d,1,TERM_BLUE);
    }

    public function loadList_Following()
    {
        //pendingTest();
        //pendingTest();
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN('urn:Actor:User:System:789:following');
        println($m, 1, TERM_BLUE);
        $listMembers = $m->deliver();
        println($listMembers, 1, TERM_VIOLET);
        println([456], 2, TERM_VIOLET);
        assertEqual($listMembers->ids, [456]);

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Actor:User:System';
        $m->in = $listMembers;
        //println($m,1,TERM_BLUE);
        $us = $m->deliver();
        println($us);
        assertDataSetNotEmpty($us);
    }

    public function loadList_UserRoles()
    {
        //pendingTest();
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN('urn:Actor:User:System:123:actas');
        println($m, 1, TERM_BLUE);
        $listMembers = $m->deliver();
        // println($listMembers, 1, TERM_VIOLET);
        // println([1001], 2, TERM_VIOLET);
        // assertEqual($listMembers->ids, [1001]);
    }

    public function loadByArrow()
    {
//        pendingTest();
        $user = URN::object_by('urn:Actor:User:System:123');
        $roles = $user->actas;
        println($roles);
        assertDataSetNotEmpty($roles);
    }

    public function loadList_RoleUsers()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN('urn:Actor:Role:System:1001:delegatedto');
        println($m, 1, TERM_BLUE);
        $listMembers = $m->deliver();
        //println($listMembers, 1, TERM_VIOLET);
        //println([123], 2, TERM_VIOLET);
        assertEqual($listMembers->ids, [123]);
    }

    public function removeFollow()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'remove';
        $m->urn = 'urn:Actor:User:System:456';
        $m->from = new URN('urn:Actor:User:System:990:following');
        println($m, 1, TERM_BLUE);
        $e = $m->deliver();
        println($e, 1, TERM_BLUE);
    }

    public function loadList_Followers_re()
    {
//        pendingTest();
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN('urn:Actor:User:System:456:followers');
        $listMembers = $m->deliver();
        println($listMembers, 1, TERM_VIOLET);
        assertEqual($listMembers->ids, [789]);
        $m = new Message();
        $m->action = 'members';
        $m->urn = new URN('urn:Actor:User:System:990:following');
        $listMembers = $m->deliver();
        println($listMembers, 1, TERM_VIOLET);
        assertEqual($listMembers->ids, [710]);
    }

}