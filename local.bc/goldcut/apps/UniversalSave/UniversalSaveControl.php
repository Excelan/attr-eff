<?php

class UniversalSaveControl extends AjaxApplication implements ApplicationUserOptional // ApplicationAccessManaged
{
    public function exclusive($path)
    {
        $this->view = false;
        Log::info($path, 'uniloadsave');
        Log::debug($this->message->json, 'uniloadsave');

        $ticketurn = (string)$this->message->ticket;

        $data = json_decode($this->message->json);

        //$fn = 'SAVE_Complaints_editing_Complaint_C_IS';
        $fn = 'SAVE_'.join('_', $path);

        if ($GLOBALS[$fn]) {
            $saveState = $GLOBALS[$fn]($data, $ticketurn);
        } elseif ($GLOBALS['SAVE_FALLBACK']) {
            Log::info("NO FN {$fn}", 'uniloadsave');
            $saveState = $GLOBALS['SAVE_FALLBACK']($data);
        }

        $dir = BASE_DIR.'/test/data/'.join('/', $path);
        if (!file_exists($dir.'.json')) {
            $tail = array_pop($path);
            $basedir = BASE_DIR.'/test/data/'.join('/', $path);
            array_push($path, $tail);
            if (!file_exists($basedir)) {
                mkdir($basedir, octdec(FS_MODE_DIR), true);
            }
            $testdata = $data;
            $testdata->urn = '%URN%';
            save_data_as_file($dir.'.json', json_encode($testdata));
            Log::info("Test data saved to {$dir}.json", 'uniloadsave');
        }


        $d = new stdClass();
        $d->result = 'OK';
        $d->nextstage = $saveState->nextstage;
        $d->text = $saveState->text;
        $d->savestate = $saveState->state;

        return json_encode($d);
    }
}
