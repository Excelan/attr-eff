<?php

class UniversalLoadControl extends AjaxApplication implements ApplicationFreeAccess, ApplicationUserOptional
{
    function exclusive($path)
    {
        if (!$this->managementrole)
        {
            // TODO Security
        }
        $this->view = false;
        //Log::info($path, 'uniloadsave');
        Log::debug($this->message, 'uniloadsave');
        Log::debug((string)$this->managementrole, 'uniloadsave');

        $d = new stdClass();

        $urn = $this->message->urn;
        if (!$urn) $urn = json_decode($this->message->json)->urn;

        if (!$urn) {
            throw new Exception("No URN in UniversalLoadControl this->message {$this->message}");
        }

        $fn = 'LOAD_'.join('_', $path);
        if ($GLOBALS[$fn])
        {
            $d = $GLOBALS[$fn]($urn, $this->managementrole);
        }
        elseif ($GLOBALS['LOAD_FALLBACK'])
        {
            Log::info("NO FN {$fn}", 'uniloadsave');
            $d = $GLOBALS['LOAD_FALLBACK']($urn);
        }

        //var_dump($fn);
        //var_dump($GLOBALS['LOAD_Complaints_editing_Complaint_C_IS']);
        //var_dump($$fn);
        //$d = $$fn($this->message->json);

        if (is_string($d))
            return $d;
        else
            return json_encode($d);
    }
}

?>