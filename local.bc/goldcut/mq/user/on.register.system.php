<?php

// способы не отправлять почту здесь
// 1 не включать USEDUMBWELCOME
// 2 не создавать шаблон onregister, вместо него создать свой, напр onregostersite, и отдельный mq

function greetEmailSystem($createduser)
{
    Log::info("REGISTERED [$createduser->urn] with email [$createduser->email]", 'audit');

    $userID = $createduser->urn->uuid;//->toInt();

    // WRBAC
    if (ENABLE_WRBAC === true)
    {
        WRBAC::createUserInMemory($userID);
        WRBAC::addUserToGroup($userID,1000);
    }

    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Mail:Template:HTML';
    $m->uri = 'onregister';
    $hasmailt = $m->deliver();
    if (!count($hasmailt))
    {
        if (defined('USEDUMBWELCOME') && USEDUMBWELCOME === true) {
            $to = $createduser->email;
            $nameto = $createduser->name ? $createduser->name : $createduser->email;
            $from = webmaster . '@' . HOST;
            $namefrom = SITE_NAME;
            $subject = "Регистрация на сайте";
            $body = "<h2>{$createduser->email} зарегистрирован</h2>\nПароль в сервисе {$createduser->password}";
            if (!$createduser->active) $body .= "<h3>Дождитесь активации Вашего аккаунта</h3>";
            if ($to) {
                Mail::send($from, $namefrom, $to, $nameto, $subject, $body);
            } else {
                dprintln('No email in created user '.$createduser);
            }
        }
        else
            Log::debug('No USEDUMBWELCOME, no email sent to '.$createduser->emai, 'email');
    }
    else
    {
        $context = array('user' => $createduser);
        Mail::sendUserTemplatesContext($createduser, 'mailview', 'onregister', $context);
    }

}

$broker = Broker::instance();

$broker->queue_declare ("USEREXTENDED", DURABLE, NO_ACK);
$broker->bind("MANAGERS", "USEREXTENDED", "user.onregister");
$broker->bind_rpc ("USEREXTENDED", "greetEmailSystem");

?>