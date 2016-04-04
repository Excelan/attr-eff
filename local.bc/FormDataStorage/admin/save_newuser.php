<?php

$GLOBALS['SAVE_newuser'] = function ($d)
{
    Log::info("SAVE_newuser", 'uniloadsave');
    Log::debug(json_encode($d), 'uniloadsave');

    try {

        $m = new Message();
        $m->action = "register";
        $m->urn = "urn:Actor:User:System";
        $m->autologin = 0;
        $m->email = $d->email; // rand(100,999).
        $m->name = $d->fio;
        $m->providedpassword = $d->password;
        $m->providedpasswordcopy = $d->password;
        $user = $m->deliver();

        // precreate posttype
        // ТИП ДОЛЖНОСТИ
        if (!$d->posttype && $d->newposttype) {
            Log::info("!d->posttype && d->newposttype", 'uniloadsave');
            $m = new Message();
            $m->action = "create";
            $m->urn = "urn:Management:Post:Group";
            $m->title = $d->newposttype;
            $postTypeURN = $m->deliver()->urn;
        } elseif ($d->posttype) {
            Log::info("d->posttype", 'uniloadsave');
            $postTypeURN = $d->posttype;
        } else {
            Log::info("post ELSE {$d->posttype} {$d->newposttype}", 'uniloadsave');
            throw new Exception("Post/Type misconfig {$d->posttype} {$d->newposttype}");
        }

        // precreate post (attach to new/old post type)
        // ДОБАВИТЬ ДОЛЖНОСТЬ
        try {
            $m = new Message();
            $m->action = "create";
            $m->urn = "urn:Management:Post:Individual";
            $m->title = $d->posttitle;
            $m->ManagementPostGroup = $postTypeURN; // тип должности
            $m->CompanyStructureDepartment = $d->department; // департамент
            Log::debug($m, 'uniloadsave');
            $post = $m->deliver();
        } catch (Exception $e) {
            Log::error($e->getMessage(), 'sql');
        }

        // ДОБАВИТЬ СОТРУДНИКА
        $m = new Message();
        $m->action = "create";
        $m->urn = "urn:People:Employee:Internal";
        $m->istrener = $d->istrener == 'y' ? true : false;
        $m->ActorUserSystem = $user->urn; // созданный юзер!
        $m->ManagementPostIndividual = $post->urn; // должность
        $m->title = $d->fio;
        Log::debug($m, 'uniloadsave');
        $employee = $m->deliver();

        // сделать сотрудника главой выбранного отдела или департамента
        if ($d->isheadofdep == 'y') {
            $m = new Message();
            $m->action = "update";
            $m->urn = $d->department;
            $m->HeadOfDepartment = $post->urn; //$employee->urn;
            Log::debug($m, 'uniloadsave');
            $m->deliver();
        }

        // начало процесса
        foreach ($d->processstartaccess as $psa) {
            Log::debug($psa, 'uniloadsave');
            if (!$psa->processprototype || !$psa->subjectprototype) continue;
            $m = new Message();
            $m->action = "create";
            $m->urn = "urn:RBAC:ProcessStartPermission:System";
            $m->managementrole = $post->urn;
            $m->processprototype = $psa->processprototype;
            $m->subjectprototype = $psa->subjectprototype;
            Log::debug($m, 'uniloadsave');
            $m->deliver();
        }

        // документы на этапах
        foreach ($d->dctstagerbac as $psa) {
            Log::debug($psa, 'uniloadsave');
            if (!$psa->processprototype || !$psa->subjectprototype || !$psa->stage) continue;
            $m = new Message();
            $m->action = "create";
            $m->urn = "urn:RBAC:DocumentPrototypeResponsible:System";
            $m->managementrole = $post->urn;
            $m->processprototype = $psa->processprototype;
            $m->subjectprototype = $psa->subjectprototype;
            $m->stage = $psa->stage;
            Log::debug($m, 'uniloadsave');
            $x = $m->deliver();
            Log::debug($X, 'uniloadsave');
        }

        // BO boattached
        foreach ($d->boattached as $boURN) {
            if (!$boURN) continue;
            Log::debug($boURN, 'uniloadsave');
            $m = new Message();
            $m->action = "update";
            $m->urn = $boURN;
            $m->MateriallyResponsible = $employee->urn;
            Log::debug($m, 'uniloadsave');
            $m->deliver();
        }

        // Risk riskattached
        foreach ($d->riskattached as $riskURN) {
            if (!$boURN) continue;
            Log::debug($riskURN, 'uniloadsave');
            $m = new Message();
            $m->action = "update";
            $m->urn = $riskURN;
            $m->ManagementPostIndividual = $post->urn;
            Log::debug($m, 'uniloadsave');
            $m->deliver();
        }

        Log::debug("USER CREATED", 'uniloadsave');

    }
    catch (Exception $e)
    {
        Log::error($e->getMessage(), 'uniloadsave');
    }
    /*
    $m = new Message();
    $m->action = "create";
    $m->urn = "urn:";
    $m-> = $d->;
    $m->deliver();

    $m = new Message();
    $m->action = "update";
    $m->urn = "urn:";
    $m-> = $d->;
    $m->deliver();
    */

};


$GLOBALS['LOAD_newuser'] = function ()
{

    $d = new stdClass();
    $d->password = Security::generatePassword(5);
    //$d->d = ['key'=>'val'];
    //$d->d2 = ['key2'=>'val2'];

    return $d;
};

?>