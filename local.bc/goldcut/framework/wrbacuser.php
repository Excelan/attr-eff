<?php

class WRBACUser
{
    //public $userRolen;
    public $id;
    public $groups = array();
    public $roles = array();

    public function getRoles()
    {
        $roles = array();
        foreach($this->roles as $role) $roles []= $role;
        foreach($this->groups as $group)
        {
            //print '<pre>';
            //print_r($group);
            foreach($group->roles as $role) $roles []= $role;
        }
        return $roles;
    }

    public function proveOwnership($urn)
    {
        $me = $this;
        $userID = $me->id;

        // user in db
        $m = new Message();
        $m->action = 'load';
        $m->urn = $urn;
        $object = $m->deliver();
        if (!count($object)) return null;

        if (DEBUG_WRBAC === true)
        {
            if ($object->user_id == $me->id)
                if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln('Object ownership APPROVED. Dynamic merge user roles with per urn owner role for current session',1,TERM_GRAY);
            else
                if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln('Object ownership NOT APPROVED',1,TERM_GRAY);
        }

        // clear owner roles from prev test
        foreach($me->roles as $idx => &$roleProductOwner)
        {
            if ($roleProductOwner->type == 2) // owned type
                unset(WRBAC::instance()->users[$userID]->roles[$idx]);
        }

        // on ownership dyn add role to WRBAC instance (WITHOUT PERMANENT SAVING!)
        if ($object->user_id == $me->id)
        {
            $generalURN = (string) $object->urn->generalize();
            $rolesProductOwner = WRBAC::instance()->ownersRoles[$generalURN];  // array! // TODO urn->generalize() !!!!!!!!!!!!!!!!!!!!!!
            WRBAC::instance()->users[$userID]->roles = array_merge($me->roles, (array) $rolesProductOwner);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function acessibleEntities()
    {
        $hasAccessTo = array();
        $userRoles = $this->getRoles();
        foreach($userRoles as $userRole)
        {
            if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln("+ user has role {$userRole->name}",2,TERM_GRAY);
            // println($userRole->urns);
            $hasAccessTo = array_merge($hasAccessTo, $userRole->urns);
            if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) foreach($userRole->permissions as $perm) dprintln($perm->actionsAllowed);
        }
        return $hasAccessTo;
    }

    // + if owner on concrete urns
    public function acessibleActionsOnEntity($onUrn)
    {
        $onUrnObject = new URN($onUrn);
        if ($onUrnObject->is_concrete())
        {
            $own = $this->proveOwnership($onUrn);
            if ($own) $onUrn = $onUrnObject->generalize();
            else return false; // or array()?
        }
        $actionsAllowed = array();
        $userRoles = $this->getRoles();
        //var_dump($userRoles);
        foreach($userRoles as $userRole)
        {
//            var_dump($userRole);
//            var_dump($userRole->urns);
            if (defined('DEBUG_WRBAC') && DEBUG_WRBAC)  dprintln("+ user has role {$userRole->id} {$userRole->name}",2,TERM_GRAY);
            if (in_array($onUrn, $userRole->urns))
            {
                // println($userRole->urns);
                foreach($userRole->permissions as $perm)
                {
                    $actionsAllowed = array_merge($actionsAllowed, $perm->actionsAllowed);
                    if (defined('DEBUG_WRBAC') && DEBUG_WRBAC)  dprintln($perm->actionsAllowed);
                }
            }
        }
        return $actionsAllowed;
    }

    /*
    check action. all user groups. all roles of user & it groups.
    find role having perm for !urn-ad/create.
    process to action with param(fieldsAllowed/Protected). warn on try use protected/unallowed
    */
    function can($action, $onUrn)
    {
        //if (DEBUG_WRBAC === true)
        if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln("? user [{$this->id}] requests access to do [{$action}] on [{$onUrn}]",1,TERM_GRAY);
        $userRoles = $this->getRoles();
        foreach($userRoles as $userRole)
        {
            //if (DEBUG_WRBAC === true)
            if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln("? try role [{$userRole->name}] with urns ".json_encode($userRole->urns),1,TERM_GRAY);
            if (in_array($onUrn, $userRole->urns)) // to with urns this role related
            {
                //if (DEBUG_WRBAC === true)
                if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln("+ user has role [{$userRole->name}] that has relation (but not permission yet!) to [{$onUrn}]",2,TERM_GRAY);
                foreach($userRole->permissions as $perm)
                {
                    if (in_array($action, $perm->actionsAllowed))
                    {
                        //if (DEBUG_WRBAC === true) d
                        if (defined('DEBUG_WRBAC') && DEBUG_WRBAC) dprintln("+ granted access for {$action} {$onUrn} by Perm ({$perm->name}) in role [{$userRole->name}]",3,TERM_GREEN);
                        return $perm;
                    }
                }
            }
        }
        return false;
    }
}
class WRBACGroup
{
    public $name;
    public $users = array();
    public $roles = array();
}
/*
roles protected from deletion or change
selectable roles
system roles - owner (Scope of role)
delegated roles - internal, not selectable
*/
class WRBACRole
{
    public $name;
    public $type = 1;
    //public $userRolens = array();
    public $permissions = array();
}
// Atomic? actionsAllowed not array. Fields are actual not only for update but for any custom actions
class WRBACPermission
{
    public $name;
    public $actionsAllowed = array();
    // IN
    public $fieldsOnlyAllowed;// = array();
    public $fieldsProtected;// = array();
    // OUT
    // TODO ADD OUT/RETURN FILTERING
}

?>