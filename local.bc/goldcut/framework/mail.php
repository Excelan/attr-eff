<?php
// TODO 2 stage template - feed context data (1 pass, 1 time), feed user data (2 pass, N times)
class Mail
{

	static function sendUserTemplatesContext($user, $mailBaseTemplate, $mailContentTemplate, $contexts)
	{
        if ( is_array($user) ) {

            $to  = $user['mail'];
            $nameto = $user['name'];

        } else {

            if (!count($user)) throw new Exception("User not exists");
            $to = $user->email;
            if (!$to) throw new Exception("User->email not exists");

            if ($user->name)
                $nameto = $user->email;
            elseif ($user->nickname)
                $nameto = $user->email;
            else
                $nameto = $user->email;
        }
		
		$m = new Message();
		$m->action = "load";
//		$m->urn = "urn-mailtemplate";
		$m->urn = "urn:Mail:Template:Plain";
		$m->uri	= $mailContentTemplate;
		$m->last = 1;
		$m->lang = SystemLocale::$REQUEST_LANG;
		$tmpl = $m->deliver();

		if (!count($tmpl)) throw new Exception("mailContentTemplate $mailContentTemplate not exists");

		$from = $tmpl->fromemail;
		$namefrom = $tmpl->fromname;
		$subject = $contexts['subject'] ? $contexts['subject'] : $tmpl->title;

        /*OLD mail template model*/

/*		$body = urldecode($tmpl->mailhtml);
		$T = new Template($body);
		foreach ($contexts as $contextKey => $contextValue)
		{
			$T->context->add($contextKey, $contextValue);
		}
		$body = (string) $T;
		
		$body = str_replace('/img', BASEURL.'/img', $body);
		$body = str_replace('class="FS22"', 'style="font-size:22px;"', $body);
		$body = str_replace('class="FS20"', 'style="font-size:20px;"', $body);
		$body = str_replace('class="FS18"', 'style="font-size:18px;"', $body);
		$body = str_replace('class="FS16"', 'style="font-size:16px;"', $body);
		$body = str_replace('class="FS14"', 'style="font-size:14px;"', $body);*/

        /*New mail template model*/

        //header
        $header = urldecode($tmpl->headerplain);
        $header = renderContextObjectsOverPlainTemplate($contexts, $header);
        $header = Mail::parseMailText($header);

        //content
        $content = urldecode($tmpl->contentplain);
        $content = renderContextObjectsOverPlainTemplate($contexts, $content);
        $content = Mail::parseMailText($content);

        //footer
        $footer = urldecode($tmpl->footerplain);
        $footer = renderContextObjectsOverPlainTemplate($contexts, $footer);
        $footer = Mail::parseMailText($footer);

        //special
        $special = urldecode($tmpl->specialplain);
        if ( trim($special) ) {
            $special = renderContextObjectsOverPlainTemplate($contexts, $special);
            $special = Mail::parseMailText($special);
            $special = "<p style='{$GLOBALS['CONFIG']['OTHER']['MAIL_PLAIN_SPECIAL']}'>".$special."</p>";
        }

        //prepare full email
        $fullEmail = Mail::prepare($mailBaseTemplate, array('<header />'=>$header,
                                                            '<content />'=>$content,
                                                            '<footer />'=>$footer,
                                                            '<special />'=>$special,
             ));

        //Log::debug($fullEmail,'mailtext');

        /*New mail template model*/

        $filename = null;
        if ( $contexts['_attach'] ) $filename = $contexts['_attach'];

        Mail::send($from, $namefrom, $to, $nameto, $subject, $fullEmail, $filename);

        //save letter to html file for debug
		/**
        $file = fopen(BASE_DIR."/mailTemplate.html","w+");
        if ( fwrite($file,$fullEmail) === FALSE) echo "Error. Cant write mail template to /mailTemplate.html. Create it and chmod 777";
        fclose($file);
		 */

	}

    static function parseMailText($text) {

        /** [link http*text*attribute] */
        preg_match('/\[link(.*?)\]/', $text, $return);

        $a = array("[link","]");
        $str = str_replace($a,"",$return[0]);
        $str = explode("*",$str);

//        $returnLink = "<a href='{$str[0]}' {$str[2]} >{$str[1]}</a>";
        $returnLink = "<p style='{$GLOBALS['CONFIG']['OTHER']['MAIL_PLAIN_BUTTON']}'><a style='{$GLOBALS['CONFIG']['OTHER']['MAIL_PLAIN_BUTTON_LINK']}' href='{$str[0]}'>{$str[1]}</a></p>";
        $text = str_replace($return[0],$returnLink,$text);


        /** [H text] */
        preg_match('/\[H(.*?)\]/s', $text, $return);

        $a = array("[H","]");
        $str = str_replace($a,"",$return[0]);
        $str = explode("*",$str);

        $returnLink = "<p style='{$GLOBALS['CONFIG']['OTHER']['MAIL_PLAIN_H']}'>{$str[0]}</p>";
        $text = str_replace($return[0],$returnLink,$text);

        /** [TEXT text] */
        preg_match('/\[TEXT(.*)\]/s', $text, $return);

        $a = array("[TEXT","]");
        $str = str_replace($a,"",$return[0]);

        $txt = nl2br($str);
        $text = str_replace($return[0],$txt,$text);

        return $text;
    }

	static function prepare($tmplName = 'mail', $params)
	{
        $globalTemplate = file_get_contents(BASE_DIR.'/views/layout/'.$tmplName.'.html');

        foreach($params as $htmlTag => $value) {
            $globalTemplate = str_replace($htmlTag, $value, $globalTemplate);
        }

		return $globalTemplate;
	}

    // $from = null, $namefrom not used with API postmark
    public static function send($from = null, $namefrom = null, $to, $nameto, $subject, $body, $filename = null)
    {
        if (!defined('POSTMARKAPI') || ENV == 'DEVELOPMENT') {
            //dprintlnd('no postmark || env dev');
            self::sendWithSMTP($from, $namefrom, $to, $nameto, $subject, $body, $filename);
            return;	
	    }

        if (!is_array($to)) $to = array($to);
        $todbg = json_encode($to);
        Log::debug($todbg, 'mail');
        if (ENV == 'DEVELOPMENT')
        {
            //dprintlnd('env dev, maybe postmark');
            //dprintln("mail to:{$todbg} \"$subject\"",1,TERM_GRAY);
            foreach ($to as $to1) Log::debug($body, 'mail-'.$to1);
            // todo return rnd path to html file created with mail
            if (defined('SENDMAILINENVDEV') && SENDMAILINENVDEV === true)
                $override = 1;
            else
                return null;
        }

        //if (!defined('POSTMARKAPI')) throw new Exception('POSTMARKAPI not defined');
        //if (!POSTMARKAPI) throw new Exception('POSTMARKAPI is not string');

        foreach ($to as $to1) {
            $x = Postmark\Mail::compose(POSTMARKAPI)
                ->from($GLOBALS['CONFIG']['SENDER']['FROMEMAIL'], $GLOBALS['CONFIG']['SENDER']['FROMNAME'])
                ->addTo($to1, $nameto)
                ->subject($subject)
                //->messagePlain('Test text')
                ->messageHtml($body);
            if ( $filename ) {

                if (file_exists($filename) ) {
                    $x->addAttachment($filename);
                } else {
                    throw new Exception('attach '.$filename.' not found !');
                }
            }
            $x->send();
        }
    }

	public static function sendWithSMTP($from = null, $namefrom = null, $to, $nameto, $subject, $body, $filename = null)
	{
        if (!is_array($to)) $to = array($to);
        $todbg = json_encode($to);
		Log::debug($todbg, 'mail');
		if (ENV == 'DEVELOPMENT')
		{
            //dprintln("mail to:{$todbg} \"$subject\"",1,TERM_GRAY);
            foreach ($to as $to1) Log::debug($body, 'mail-'.$to1);
            // todo return rnd path to html file created with mail
			if (defined('SENDMAILINENVDEV') && SENDMAILINENVDEV === true) {
				$override = 1;
			}
            else {
	            Log::debug('NOT SENT','mail');
				return null;
			}
		}
		
		require_once BASE_DIR.'/lib/php-mailer/class.phpmailer.php';
		require_once BASE_DIR.'/lib/php-mailer/class.smtp.php';
		try 
		{
			$mail = new PHPMailer(true); // enable exceptions 
			$mail->IsSMTP();
			$mail->Port = 25;
			$mail->Host = $GLOBALS['CONFIG']['SMTP']['SERVER'];
			Log::debug("Mail env server:{$GLOBALS['CONFIG']['SMTP']['SERVER']} user:{$GLOBALS['CONFIG']['SMTP']['USERNAME']}", 'mail');
			if ($GLOBALS['CONFIG']['SMTP']['USERNAME'])
			{
				$mail->SMTPAuth = true;
				$mail->Username = $GLOBALS['CONFIG']['SMTP']['USERNAME'];
				$mail->Password = $GLOBALS['CONFIG']['SMTP']['PASSWORD'];
				//$mail->SMTPDebug = 4;
				if ($GLOBALS['CONFIG']['SMTP']['SSL']) // gmail etc
				{
					$mail->SMTPSecure = "ssl";
					$mail->Port = 465;
				}
				if ($GLOBALS['CONFIG']['SMTP']['TSL']) // gmail etc
				{
					$mail->SMTPSecure = "tls";
					$mail->Port = 587;
				}
			}
			else
				$mail->SMTPAuth = false;
			$mail->Subject  = $subject;
			
			$mail->From = ($from) ? $from : $GLOBALS['CONFIG']['SENDER']['FROMEMAIL'];
			$mail->FromName = ($namefrom) ? $namefrom : $GLOBALS['CONFIG']['SENDER']['FROMNAME'];

            foreach ($to as $to1) $mail->AddAddress($to1);

            if ( $filename ) {

                if (file_exists($filename) ) {
                    $mail->AddAttachment($filename);
                } else {
	                Log::error("No attach $filename", 'mail');
                    throw new Exception('attach '.$filename.' not found !');
                }
            }

			//$mail->AddReplyTo("email@","Имя");
			// $mail->AltBody    = "enable html"; // optional
			$mail->WordWrap = 80;
			$mail->IsHTML(true);
			$mail->MsgHTML($body);
			
			$report = $mail->Send();
			Log::debug("smtp sent mail to:{$todbg} \"$subject\"",'mail');
			Log::debug($report,'mail');
			Log::error($mail->ErrorInfo, 'mail');
			Log::debug($GLOBALS['CONFIG']['SMTP'],'mail');
		}
		catch (phpmailerException $e) 
		{
			//println($GLOBALS['CONFIG']['SMTP']);
			Log::error($e->errorMessage(), 'mailerror');
			Log::error($e->errorMessage(), 'mail');
			Log::error($mail->ErrorInfo, 'mail');
			dprintln($e->errorMessage(),1,TERM_RED);
		}
	}
	
	private static function legacy_send($from, $namefrom, $to, $nameto, $subject, $message)
	{
		$smtpServer = $GLOBALS['CONFIG']['SMTP']['SERVER'];
		$port = "25";
		$timeout = "3";
		$username = $GLOBALS['CONFIG']['SMTP']['USERNAME'];
		$password = $GLOBALS['CONFIG']['SMTP']['PASSWORD'];
		$localhost = $GLOBALS['CONFIG']['SMTP']['SERVER'];
		$newLine = "\r\n";

		$smtpConnect = fsockopen($smtpServer, $port, $errno, $errstr, $timeout);
		$smtpResponse = fgets($smtpConnect, 515);
		if(empty($smtpConnect))
		{
			$output = "Failed to connect: $smtpResponse";
			return $output;
		}
		else
		{
			$logArray['connection'] = "Connected: $smtpResponse";
		}

		fputs($smtpConnect, "HELO $localhost" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		$logArray['heloresponse'] = "$smtpResponse";

		if ($username)
		{
			fputs($smtpConnect,"AUTH LOGIN" . $newLine);
			$smtpResponse = fgets($smtpConnect, 515);
			$logArray['authrequest'] = "$smtpResponse";
	
			fputs($smtpConnect, base64_encode($username) . $newLine);
			$smtpResponse = fgets($smtpConnect, 515);
			$logArray['authusername'] = "$smtpResponse";
		
			fputs($smtpConnect, base64_encode($password) . $newLine);
			$smtpResponse = fgets($smtpConnect, 515);
			$logArray['authpassword'] = "$smtpResponse";
		}

		fputs($smtpConnect, "MAIL FROM: $from" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		$logArray['mailfromresponse'] = "$smtpResponse";

		fputs($smtpConnect, "RCPT TO: $to" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		$logArray['mailtoresponse'] = "$smtpResponse";

		fputs($smtpConnect, "DATA" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		$logArray['data1response'] = "$smtpResponse";

		$headers = "MIME-Version: 1.0" . $newLine;
		$headers .= "To: " . '=?UTF-8?B?'.base64_encode($nameto).'?=' . "<$to>" . $newLine;
		$headers .= "From: " . '=?UTF-8?B?'.base64_encode($namefrom).'?=' . "<$from>" . $newLine;
		$headers .= "Content-type: text/html; charset=UTF-8" . $newLine;

		$subject = '=?UTF-8?B?'.base64_encode($subject).'?=';

		fputs($smtpConnect, "Subject: $subject\n$headers\n\n$message\n.\n");

		$smtpResponse = fgets($smtpConnect, 515);
		$logArray['data2response'] = "$smtpResponse";

		fputs($smtpConnect,"QUIT" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		$logArray['quitresponse'] = "$smtpResponse";

		return $logArray;

	}
	
	
	
}
?>