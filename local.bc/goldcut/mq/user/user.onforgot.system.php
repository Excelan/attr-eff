<?php

function onforgotSystem($user)
{
    $newpass = $user->newpassword;

    $m = new Message();
    $m->action = 'load';
//    $m->urn = 'urn-mailtemplate';
    $m->urn = "urn:Mail:Template:Plain";
    $m->uri = 'onforgot';
    $hasmailt = $m->deliver();

    if (!count($hasmailt))
    {
        $to = $user->email;
        $nameto = $user->name ? $user->name : $user->email;
        $from = webmaster.'@'.HOST;
        $namefrom = SITE_NAME;
        $subject = "Новый пароль в сервисе";
        $message = "Новый пароль в сервисе {$newpass}";
        Mail::send( $from, $namefrom, $to, $nameto, $subject, $message );
    }
    else
    {
        $context = array('user' => $user, 'datetime' => TimeOp::datetime());
        Mail::sendUserTemplatesContext($user, 'mailview', 'onforgot', $context);
    }

}

$broker = Broker::instance();

$broker->queue_declare ("USEREXTENDED", DURABLE, NO_ACK);
$broker->bind("MANAGERS", "USEREXTENDED", "user.onforgot");
$broker->bind_rpc ("USEREXTENDED", "onforgotSystem");

?>