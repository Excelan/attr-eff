<?php
require "boot.php";

if (substr($_GET['uri'],0,1) == "/") $_GET['uri'] = substr($_GET['uri'],1);
$URI = $_GET['uri'];

/*
function currentRoleUser()
{
    if ($this->user !== null) return true;
    $us = new Message('{"urn": "urn-user", "action": "session"}');
    $sess = $us->deliver();
    if ($sess->warning || $sess->error)
    {
        $this->role = "ANONYMOUSE";
    }
    else if ($user_urn = $sess->user)
    {
        $user = $user_urn->resolve()->current();
        $this->role = "USER";
        $this->user = $user;
        // $this->userrole = $user->role; // Lazy load
        if (ENABLE_WRBAC === true)
            WRBAC::unserializeUser($user->id);
    }
}
*/

//$GLOBALS['CONFIG']['GATEROUTING']

if ($_SERVER['REQUEST_METHOD'] == 'POST') // try use gates vs old webrequest to AppControl
{
    //\Log::debug("POST", "RRR");
    $routingResult = null;

    if (Gate::routable($URI))
    {
        // \Log::debug("GATE ROUATBLE", "RRR");
        //Log::info(json_decode($_POST['json']));
        if (!$_POST['json'])
        {
            Log::error("No POST.json", 'gates');
            $cmd = $_POST;
            Log::info($_POST, 'gates');
        }
        else
        {
            Log::info($_POST['json'], 'gates');
            Log::info(json_decode($_POST['json'], true), 'gates');
            $cmd = json_decode($_POST['json'], true);
        }

        $us = new Message('{"urn": "urn:Actor:User:System", "action": "session"}');
        $sess = $us->deliver();
        if ($sess->warning || $sess->error)
        {
            \Log::error($sess, 'usersys');
            $user = null;
        }
        else if ($user_urn = $sess->user)
        {
            \Log::info($sess, 'usersys');
            $user = $user_urn->resolve()->current();
        }
        $cmd['user'] = $user->id;
        foreach ($user->actas as $role)
            $cmd['roles'][] = $role->name;
        \Log::debug($cmd, 'usersys');

        $dblink = DB::link();
        try {
            $dblink->begin();
            $routingResult = Gate::route($URI, $cmd);
            $dblink->commit();
        }
        catch (Exception $e)
        {
            $ecode = 503;
            if ($code = $e->getCode()) $ecode = $code;
            header("HTTP/1.0 $ecode Server Error");
            $dblink->rollback();
            $error = GCException::ghostBuster($e, $URI);
            throw $e;
        }

        header("Content-type: application/json");
        print json_encode($routingResult);
    }
    else
    {
        //\Log::debug("NON GATE ROUTABLE", "RRR");
        WebRequest::dispatch($URI); // Old AppControl
    }

}
else // GET to web Application
{
    WebRequest::dispatch($URI);
}

?>