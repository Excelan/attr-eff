<?php
namespace DMS\UKD;

class GenerateAllPDFForSOP extends \Gate
{

    /**
   * count of plannedreceivers[]
   * generate Copy:Managed[] for plannedreceivers[]
   * generate code128[] for plannedreceivers[]
   * generate pdf with code128 - http gate call (copyid) for plannedreceivers[]
   * pack all pdf[] to archive
   */
    public function gate()
    {
        if ($this->data instanceof \Message) {
            $this->message = $data = json_decode(json_encode($this->data->toArray()));
        } else {
            $this->message = $data = json_decode(json_encode($this->data));
        }

        \Log::info($this->message, 'ukd');

        //$copyids = explode(',', $this->message->copyids);

        $sopURN = new \URN($this->message->sopurn);
        $sop = $sopURN->resolve();

        $rukdURN = new \URN($this->message->rukdurn);
        $rukd = $rukdURN->resolve();

        $m = new \Message();
        $m->urn = 'urn:DMS:Copy:Controled';
        $m->action = 'load';
        //$m->wfstate = 'generated';
        //$m->DocumentRegulationsSOP = $sopURN;
        $m->issueDocumentProtocolRUKD = $rukdURN;
        \Log::debug((string)$m, 'rukd');
        $copies = $m->deliver();

        $uri = '/tmp/zip/'.$sop->id.'_'.$sop->version.'_'.$rukdURN->uuid;
        $path = BASE_DIR.$uri;
        mkdir($path, octdec(FS_MODE_DIR), true);

        $pdfOriginalArray = [];
        $pdfDestinationArray = [];
        foreach ($copies as $copy) {
            \Log::debug((string)$copy, 'rukd');
            $pdfLatex = \LatexUtils::buildLatexPDF($sopURN, ['copyid'=>$copy->id, 'barcode'=>"tmp/code128_{$copy->id}.png", 'holder'=>$copy->holder->nameofemployee]);
            $latex = $pdfLatex[0];
            $pdfURI = $pdfLatex[1];
            \Log::debug('Generate latex pdf with code128 '.$pdfURI, 'rukd');

            $m = new \Message();
            $m->urn = $copy->urn;
            $m->action = 'update';
            $m->pdflink = $pdfURI;
            $m->wfstate = 'linked';
            \Log::info($m, 'rukd');
            $m->deliver();

            $pdfOriginal = BASE_DIR.$pdfURI;
            $pdfDestination = $path.'/'.$copy->id.'.pdf';
            \Log::info('pdfOriginal'.$pdfOriginal, 'rukd');
            \Log::info('pdfDestination'.$pdfDestination, 'rukd');
            array_push($pdfOriginalArray, $pdfOriginal);
            array_push($pdfDestinationArray, $pdfDestination);

            /*
            if (!copy($pdfOriginal, $pdfDestination)) {
                $error = [];
                //$processUser = posix_getpwuid(posix_geteuid()); Im {$processUser['name']}.
                if (!file_exists($pdfOriginal)) {
                    $error[] = 'source not exists';
                }
                if (!file_exists($path)) {
                    $error[] = 'destination folder not exists';
                }
                if (!is_readable($pdfOriginal)) {
                    $error[] = 'source not readable';
                }
                if (!is_writable($pdfDestination)) {
                    $error[] = 'destination not writable';
                }
                throw new \Exception("Cant copy $pdfOriginal to $pdfDestination. Reason ".join(', ', $error));
            }
            */
        }

        // zip all $pdfURI
        // set $rukd->printarchive = zip

        $zipfile = $path.'.zip';
        $zipuri = $uri.'.zip';
        //\Utils::zip_folder($path, $zipfile);

        $ret = ['status' => 200, 'pdfs' => join(',', $pdfOriginalArray), 'pdfdests' => join(',', $pdfDestinationArray), 'zipfolderpath'=>$path, 'zipfile'=>$zipfile, 'zipuri' => $zipuri];
        \Log::debug($ret, 'rukd');
        return $ret;
    }
}
