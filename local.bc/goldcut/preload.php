<?php
require BASE_DIR.'/lib/postmark-php/src/Postmark/Autoloader.php';
Postmark\Autoloader::register();

require BASE_DIR.'/lib/Mobile-Detect/Mobile_Detect.php';

spl_autoload_register(function ($sClass)
{
    if (strpos($sClass, 'Everyman\Neo4j\\') === 0) {
        $sLibPath = BASE_DIR.'/lib/';
        $sClassFile = str_replace('\\',DIRECTORY_SEPARATOR,$sClass).'.php';
        $sClassPath = $sLibPath.$sClassFile;
        if (file_exists($sClassPath)) require($sClassPath);
    }
    else
    {
        $sLibPath = BASE_DIR.'/gates/';

        $sClassFile = str_replace('\\',DIRECTORY_SEPARATOR,$sClass).'.php';
        $sClassPath = $sLibPath.$sClassFile;

        //$nsa = explode('\\', $sClass);
        //$className = array_pop($nsa);
        //$sClassFile2 = str_replace('\\',DIRECTORY_SEPARATOR,$sClass).DIRECTORY_SEPARATOR.$className.'.php';
        //$sClassPath2 = $sLibPath.$sClassFile2;
        //if (file_exists($sClassPath2)) require($sClassPath2);
        if (file_exists($sClassPath)) require($sClassPath);
        else
        {
            $sLibPath = BASE_DIR.'/goldcut/gates/';
            $sClassFile = str_replace('\\',DIRECTORY_SEPARATOR,$sClass).'.php';
            $sClassPath = $sLibPath.$sClassFile;
            if (file_exists($sClassPath)) require($sClassPath);
        }
    }
});

function autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require $fileName;
}


require BASE_DIR . '/lib/vendor/autoload.php';

//use PhpAmqpLib\Connection\AMQPStreamConnection;
//use PhpAmqpLib\Message\AMQPMessage;

require BASE_DIR . '/lib/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
//Mustache_Autoloader::register(dirname(__FILE__) . '/../test');
//require BASE_DIR . '/lib/mustache.php/vendor/yaml/lib/sfYamlParser.php';


/*
WRBAC::instance();

$preloadclasses = new WRBACUser();

$gregistered = new WRBACGroup();
$gregistered->id = 1000;
$gregistered->name = 'registered';
WRBAC::instance()->groups[1000] = $gregistered;

$gactivated = new WRBACGroup();
$gactivated->id = 1001;
$gactivated->name = 'activated';
WRBAC::instance()->groups[1001] = $gactivated;

// TODO role owner needed?
// permissions for entity owners
$permUD = new WRBACPermission();
$permUD->actionsAllowed = array('update','delete','load');
$permUD->fieldsProtected = array('password');
$permUD->name = 'update,delete,load';
WRBAC::instance()->permissions[] = $permUD;
foreach (Entity::each_entity() as $entity)
{
    if ($entity->is_system()) continue;
    $k = 'urn:'.$entity->name;
    $roleAdOwner = new WRBACRole();
    $roleAdOwner->name = $k.'-owner';
    $roleAdOwner->urns [] = $k;
    $roleAdOwner->type = 2;
    WRBAC::instance()->ownersRoles[$k][] = $roleAdOwner; // auto generate owner roles for every entity with user_id
    $roleAdOwner->permissions [] = $permUD; // config permissions for each entity owner role
}
*/
// auto create groups for user states
/**
$entity = Entity::ref('user');
foreach ($entity->statuses as $status)
{
$role = new WRBACGroup();
$role->name = 'users in state '.$status;
$k = 'users-'.$status;
WRBAC::instance()->groups[$k] = $role;
}
 */



?>
