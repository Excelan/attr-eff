<?php

/**
 TODO db store user roles, groups
 * TODO xml groups, roles, permission
 * Юзер обладает ролями (по отношению к сущностям) и вступает в группы.
 * Юзер не обладает Доступами, это задача Роли (Доступ к своим объектам - Динамическая Роль "Владелец")
 * Группа юзеров обладает ролями так же как и отдельный юзер
 * Юзер имеет id, группы и роли
 * Роль по отношению к сущностям. Динамическая роль по отношению к объкету (владелец)
 * Роль обладает набором Permissions Доступных действий (по отношению ко всем ее сущностям)
 * Восстановление - юзер и его группа, роли юзера и группы
 * Доступ проверяется на уровне Owner role только если urn concrete
 * user->can(action, entity)
 * user->acessibleActionsOnEntity(urn)
 * user->acessibleEntities()
 * gate/path push == action?
 * mq.path push pull == action?
 * System roles - registered_user (assign on auth)
 * create, load - general (user will be assigned in any case with ManagedMessage), update, delete - owner role managed
 * user.addrolename(savetodb), user.removerolename()
 * db user.groups, user.roles-
 * mem groups, roles, perms.
 * 	mem group.roles, role.perms
 * delegate self some role to user, group
 * permission check class(who ask, on what, when, nth time) scope - you can do smth if you related to object in way _
 */

// http://hitachi-id.com/identity-manager/docs/beyond-roles.html
// Audit - actions, grants, acl check fails

/*
Business case - hosting control
SCOPE - client_id. client has subusers. 
Account Owner delegates access to whole email subsystem (role EmailManager)
System groups, user owned groups (role canCreateGroup) with scope - needed field client_id scope set
roles: AccountRoot(urn-account-id, scope?), 
	WebSiteManager, EmailManager, DNSManager, PRParkingServiceManager - all (many websites etc) in account scope
	те юзеров или сабюзеров много, а realm/scope у всех один
delegate with/without right to delegate next
ROLE condition - can delete invoice if status is unpayed and selected payment method != bank (mark as deleted, real delete in 7 days) bank id = userid/invoiceid

can ==> gain access, power, permission
+ add check multiple actions at once

! file upload perm
! media convert perm ==? create photo, create video, create audio?

return array which urn-entity i have any actions access
return what i can do with urn-entity

XML store of instance conf can be shared with node.js, python, java

::registerUser
::registerGroup
::addUserToGroup(user, group)
::removeUserFromGroup(user, group)
::registerRole(role)
::addPermissionToRole(perm, role)
::registerPermission(perm)
::assignRoleToUser(role, user) or userActAsRole(user, role)
::unassignRoleFromUser(role, user) userLoseRole(user, role)

{
	role has [permissions] on [urns]
	user has roles
	groups has roles
	user may belongs to group

	permission allow actions and filter fields on urns defined by role
	
	Special array of roles for each urn-entity _owner_ 
		WRBAC::instance->ownersRoles['urn-ad'][]
		dynamic aquiring of roles is per user in memory and not stored - NEED cleanup of type 2 roles from users OR sync after rbac admin ops (atomic, not mixed with dyn role aruire requests) on instance but never save back modified instance (and good for long requests to not lock instance!)
		instance wrbac change is singe threaded locked operation
}

linkexists access? object, with-subject? ownership of object or subject

*/

class WRBAC
{
	public $users = array();
	public $groups = array();
	public $roles = array();
	public $ownersRoles = array();
	//public $managersRoles = array(); // whole entity manager
	//public $delegatedRoles = array(); // potentially big list
	public $permissions = array();
	private static $instance;
	private function __construct() {}
		
	public static function instance()
	{
		if (!self::$instance) self::$instance = new WRBAC(); 
		return self::$instance; 
	}
	
	public static function replaceInstance($instance)
	{
		self::$instance = $instance; 
	}
	
	public static function &getUserByID($userID)
	{
		return self::instance()->users[$userID];
	}

	public static function createUserInMemory($userID)
	{
		$u = new WRBACUser();
		$u->id = $userID;
		self::instance()->users[$userID] = $u;
	}

	public static function addUserToGroup($userID, $groupId)
	{
		$u = self::instance()->users[$userID];
		$g = self::instance()->groups[$groupId];
		$u->groups[]= $g;
		$g->users[]= $u;
		$rdb = DB::link();
		$rdb->nquery("UPDATE \"user\" SET wrbacgroups = array_append(wrbacgroups, $groupId) where id = $userID");
		return true;
	}

	public static function unserializeUser($userID)
	{
		//$u = self::instance()->users[$userID];
		$u = new WRBACUser();
		$u->id = $userID;
		self::instance()->users[$userID] = $u;
		$rdb = DB::link();
		$q = "SELECT wrbacgroups FROM \"user\" WHERE id = $userID";
		$r = $rdb->tohashquery($q);
		$groups = json_decode('['.substr($r[0]['wrbacgroups'],1,-1).']');
		foreach ($groups as $gid)
		{
			$g = self::instance()->groups[$gid];
			$g->users[] = $u;
			$u->groups[] = $g;
		}
		return $u;
	}

	public static function allRolesToStringsArray()
	{	
		$rs = array();	
		foreach (WRBAC::instance()->roles as $role)
		{
			$permActionsStr = '';
			foreach ($role->permissions as $perm)
			{
				$permActionsStr = join(', ', $perm->actionsAllowed);
			}
			$rs[] = "{$role->name} (".join(', ',$role->urns). ") $permActionsStr";
		}
		foreach (WRBAC::instance()->ownersRoles as $urn => $ownerroles)
		{
			foreach ($ownerroles as $role)
			{
				$permActionsStr = '';
				foreach ($role->permissions as $perm)
				{
					$permActionsStr = join(', ', $perm->actionsAllowed);
				}
				$rs[] = "{$role->name} @($urn) $permActionsStr";
			}
		}
		return $rs;
	}
	
	public static function allUsersRolesDebug()
	{
		foreach (WRBAC::instance()->users as $user)
		{
			foreach ($user->groups as $group)
			{
				$groupNames []= $group->name;
			}
			$userStr = "{$user->id} [".join(', ',$groupNames)."]";
			println($userStr,1,TERM_GRAY);
			foreach ($user->roles as $role)
			{
				foreach ($role->permissions as $perm)
				{
					$permActionsStr = join(', ', $perm->actionsAllowed);
				}
				println("{$role->name} (".join(', ',$role->urns). ") $permActionsStr",2,TERM_GRAY);
			}
		}
	}
	
}


class WRBACException extends Exception {}

?>