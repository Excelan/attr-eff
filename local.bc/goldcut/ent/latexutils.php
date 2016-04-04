<?php

/**
uses AMQP, read/write files, OS
*/

class latexutils
{

    public static function loadDocumentForPrintWithLatex($urn)
    {
        $m = new Message();
        $m->action = "load";
        $m->urn = $urn;
        $loaded = $m->deliver();
        if (count($loaded)) {
            $prototypeNesting = $GLOBALS['NESTING'][(string)$urn->prototype];
            if (is_string($prototypeNesting)) {
                //throw new Exception("NESTING config for $urn not found");
          $forlatex = true;
                $d = $GLOBALS[$prototypeNesting]($urn, $forlatex);
                Log::info($d, 'latex');
                return $d;
            }
            $d = $loaded->current()->toArray($prototypeNesting, 0, ['richtext'=>'aslatex']);
            if (!$d['code']) {
                $d['code'] = $d['urn']." NOCODE";
            }
        //$json = json_encode($d);
        Log::info($d, 'latex');
            return $d;
        } else {
            throw new Exception("$urn not found");
        }
    }

    public static function buildLatexPDF($urn, $copy=null)
    {
        $bw = self::loadDocumentForPrintWithLatex($urn);
        $bw['graphicspath'] = '\graphicspath{{' .BASE_DIR. '/}}';
        if ($copy) {
            if (!$copy['copyid']) {
                throw new Exception("No copyid");
            }
            $bw['copyid'] = $copy['copyid'];
            $bw['barcode'] = $copy['barcode'];
        }

        //Log::info($bw, 'latex');
        //Log::info($bw['graphicspath'], 'latex');

        $workDir = '/latex/workdir/';
        if (!file_exists(BASE_DIR.$workDir)) {
            mkdir(BASE_DIR.$workDir, octdec(FS_MODE_DIR), true);
        }

        $protoCanonical = str_replace(':', '_', (string)$urn->prototype);

        $resultFileNameCanonicalName = $protoCanonical.'-'.$urn->uuid;
        if ($copy) {
            $resultFileNameCanonicalName .= '-'.$copy['copyid'];
        }
        $pdfURI = '/tmp/'."{$resultFileNameCanonicalName}.pdf";
        $pdfFile = BASE_DIR.$pdfURI;
        unlink($pdfFile);

        $t = read_data_from_file(BASE_DIR."/latex/templates/{$protoCanonical}.tex");
        $m = new Mustache_Engine;
        $latex = $m->render($t, $bw);

        $latexFile = "{$resultFileNameCanonicalName}.tex";
        save_data_as_file(BASE_DIR.$workDir.$latexFile, $latex);

    // deferred
    try {
        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('hello', false, false, false, false);
        $msg = new \PhpAmqpLib\Message\AMQPMessage($latexFile);
        $channel->basic_publish($msg, '', 'hello');
    } catch (Exception $e) {
        Log::error($e->getMessage(), 'latex');
    }
    // immediate
    //$pdfURI = latex2pdf(BASE_DIR.$workDir, $resultFileNameCanonicalName);

    return [$latex, $pdfURI, $info];
    }

    public static function latex2pdf($latexFolder, $resultFileNameCanonicalName)
    {
        if (\OS::getOS() == 'SMARTOS' || \OS::getOS() == 'SUNOS') {
            $os = 'smartos';
        } elseif (\OS::getOS() == 'DARWIN' || \OS::getOS() == 'OSX') {
            if (file_exists('/Library/TeX/texbin/pdflatex')) {
                $os = 'osx';
            } else {
                throw new Exception("pdflatex bin not found");
            }
        } elseif (\OS::getOS() == 'LINUX') {
            $os = 'linux';
            if (!file_exists('/usr/bin/pdflatex')) {
                throw new Exception("pdflatex bin not found");
            }
        } else {
            \Log::error('No pdflatex on win', 'latex');
            return false;
        }

        $latexFile = "$resultFileNameCanonicalName.tex";
        $tmpPDFFile = "$resultFileNameCanonicalName.pdf";

        $pdfURI = '/tmp/'."{$resultFileNameCanonicalName}.pdf";
        $pdfFile = BASE_DIR.$pdfURI;
        unlink($pdfFile);

        $cmdbase = BASE_DIR."/latex/convertlatexpdf{$os}.sh";
        if (!file_exists($cmdbase)) {
            throw new Exception("Converter for $os $cmdbase not found");
        }

        $cmd = "$cmdbase $resultFileNameCanonicalName";
        $res = shell_run_command($latexFolder, $cmd);
        rename($latexFolder.'/'.$tmpPDFFile, $pdfFile);
        if (!file_exists($pdfFile)) {
            \Log::error($res, 'latex');
            throw new \Exception("Latex to PDF failed");
        }
        return $pdfURI;
    }

    public static function html2latex($html)
    {
        if (!strlen($html)) {
            throw new Exception("Blank html param");
        }
        $temp_filename = save_data_as_temp_file($html);
      //$html = read_data_from_file($temp_filename);
      //if (!strlen($html)) throw new Exception("Blank html reread");
      //println($html);
      $temp_result_file = new_temp_file();
      //println($temp_filename);
      //println($temp_result_file);
      if (\OS::getOS() == 'SMARTOS' || \OS::getOS() == 'SUNOS') {
          $prog = '~/.cabal/bin/pandoc';
      } elseif (\OS::getOS() == 'DARWIN' || \OS::getOS() == 'OSX') {
          if (file_exists('/usr/local/bin/pandoc')) {
              $prog = '/usr/local/bin/pandoc';
          } elseif (file_exists('/opt/local/bin/pandoc')) {
              $prog = '/opt/local/bin/pandoc';
          } else {
              throw new Exception("Pandoc bin not found");
          }
      } elseif (\OS::getOS() == 'LINUX') {
          $prog = '/usr/bin/pandoc';
          if (!file_exists($prog)) {
              throw new Exception("Pandoc bin not found");
          }
      } else {
          $prog = 'pandoc';
      }
        $cmd = "$prog -f html $temp_filename -t latex -o $temp_result_file";
        $res = shell_run_command('.', $cmd);
        $latex = read_data_from_file($temp_result_file);
        if (!strlen($latex)) {
            throw new Exception("Blank latex readen after pandoc from file $temp_result_file");
        }
        return $latex;
    }

    private static function getLatexWorkDir()
    {
    }
}
