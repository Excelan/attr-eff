<?php
require_once dirname(__FILE__).'/../../goldcut/boot.php';
include('kcaptcha.php');

$captcha = new KCAPTCHA();
$c = $captcha->getKeyString();


//$cc1 = Session::get('captcha');
//Log::debug("Getted before captcha ". $cc1, LOG_INFO);

Session::put('captcha', $c);
//Log::debug("Setted captcha ". $c, LOG_INFO);

//$cc2 = Session::get('captcha');
//Log::debug("Getted adter captcha ". $cc2, LOG_INFO);
?>