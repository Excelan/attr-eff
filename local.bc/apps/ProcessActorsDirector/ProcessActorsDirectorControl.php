<?php

class ProcessActorsDirectorControl extends AjaxApplication implements ApplicationFreeAccess //, ApplicationUserOptional
{
    public function exclusive($path)
    {
        //Log::info($this->message, 'echo');

        Log::debug('=========================================================================', 'director');

        if (!$this->message) {
            Log::debug(json_encode($_POST), 'director');
        }

        $r = json_decode($this->message->json, true);
        Log::debug($r, 'director');

        $UPN = explode(':', $r['upn']);
        $MPE_ID = $UPN[4];

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:ManagedProcess:Execution:Record';
        $m->id = $MPE_ID;
        $mpe = $m->deliver();

        Log::debug('MPE', 'director');
        Log::debug((string)$mpe->current(), 'director');
        Log::debug('subject:'.(string)$mpe->subject, 'director');

        if ($r['subjectPrototype']) {
            // $subjectURN = new URN($r['subject']);
            // $subject = $subjectURN->resolve()->current();
            Log::info('subject::'.$r['subjectPrototype'], 'director');
            // Log::info('subject::'.(string)$subject, 'director');
        } else {
            throw new Exception("No subject in MPE");
        }

        // return stage actor urn
        $fn = 'DIRECTOR_'.join('_', explode(":", $r['processPrototype'])).'_'.$r['stage'];
        if ($GLOBALS[$fn]) {
            try {
                $actorURN = $GLOBALS[$fn]($mpe, $r['subjectPrototype']);
            } catch (Exception $e) {
                Log::error("Exception in $fn ".$e->getMessage(), 'director');
                throw $e;
            }
        } elseif ($GLOBALS['DIRECTOR_FALLBACK']) { // from table PROCESSTYPE/SUBJECYTYPE/STAGE
            Log::info('NO DIRECTOR FUNCTION '.$fn, 'director');
            $actorURN = $GLOBALS['DIRECTOR_FALLBACK']($mpe, $r['subject']);
        } else {
            Log::error('NO DIRECTOR FUNCTION '.$fn, 'director');
            //$actorURN = new URN("urn:Actor:User:System:" . rand(100, 900));
            throw new Exception("NO PROCESS STAGE DIRECTOR FUNCTION");
        }

        $d = new Message();
        //$d->name = ->name;
        $d->stageActor = (string)$actorURN;

        Log::debug($d, 'director');

        return $d;
    }
}
