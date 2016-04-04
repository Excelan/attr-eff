<?php

class GCException {

    public static function ghostBuster($e, $URI)
    {
        if (defined('INTEST') && INTEST === true) return;
        
        // compile error report
        $error = "";
        $error .= "WEB REQUEST ERROR:\t".$e->getMessage()."\n\n";

        if ($_SERVER['QUERY_STRING']) $error .= "URI:\t".$_SERVER['REQUEST_URI']." ({$_SERVER['SERVER_NAME']})\n";
        $error .= "FILE:\t".$e->getFile()." : ".$e->getLine()."\n";

        $exline = getLineInFile($e);

        $error .= "CODE:\t{$exline}\n";

        foreach ($e->getTrace() as $s)
            $error .= "TRACE:\t{$s['class']}.{$s['function']} {$s['file']}:{$s['line']}"."\n";

        if ($e->user) $error .= "USER:\t{$e->user->id} {$e->user->email} {$e->user->name}\n";
        foreach ($e->user->role as $role)
            $error .= "ROLE:\t{$role->id} {$role->title}\n";


        if ($_SERVER['HTTP_REFERER']) $error .= "REFERER:\t".$_SERVER['HTTP_REFERER']."\n";
        $error .= "IP:\t".$_SERVER["REMOTE_ADDR"].' '.gethostbyaddr($_SERVER["REMOTE_ADDR"])." server ip: {$_SERVER['SERVER_ADDR']}\n";
        $clientos = OS::clientOS();
        $error .= "BROWSER:\t({$clientos}) {$_SERVER['HTTP_USER_AGENT']}\n";
        if (count($_GET)>1) $error .= "GET:\t".json_encode($_GET)."\n";
        if (count($_POST)) $error .= "POST:\t".json_encode($_POST)."\n";

        // log error
        Log::error("Exception ".$e->getMessage()." in {$URI} " . $e->getFile().':'.$e->getLine() , 'exception');
        Log::debug($error, 'exception');

        // save bug report to file
        $bugsdir = BASE_DIR."/log/bugreport";
        if (!is_dir($bugsdir)) mkdir($bugsdir, octdec(FS_MODE_DIR), true);
        $bugid = time().'-'.mt_rand(100,999);
        save_data_as_file("{$bugsdir}/{$bugid}.txt", $error);

        if (defined('EMAILBUGSTO')) {
            $nameto = 'GHOSTBUSTER';
            $from = 'bugreport@attracti.com';
            $namefrom = 'GHOSTBUSTER';
            $subject = $e->getMessage();
            $body = join("<br>", explode("\n", $error));
            $emailbugsto = explode(' ', EMAILBUGSTO);
            foreach ($emailbugsto as $emailto) {
                Mail::sendWithSMTP($from, $namefrom, $emailto, $nameto, $subject, $body);
            }
        }
        return $error;
    }

}

?>
