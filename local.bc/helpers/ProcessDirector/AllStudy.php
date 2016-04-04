<?php

$GLOBALS['DIRECTOR_DMS_Regulation_Study_Editing'] = function ($mpe, $subjectPrototype)
{
    Log::info('DIRECTOR_DMS_Regulation_Study_Editing', 'director');
    $subjectURN = new URN($mpe->subject);
    $subject = $subjectURN->resolve();
    Log::info('subject '.$subject, 'director');
    $sop = $subject->DocumentRegulationsSOP;
    if (count($sop))
    {
      $postOfExecutor = $subject->DocumentRegulationsSOP->ManagementPostIndividual;
      if (count($postOfExecutor))
      {
        Log::info('postOfTAResp '.$postOfExecutor, 'director');
        return (string) $postOfExecutor->urn;
      }
      else
      {
        $err = "NO EXECUTOR TA IN SOP. CANT GET RESPONSIBLE";
        Log::info($err, 'director');
        throw new Exception($err);
      }
    }
    else {
      $err = "NO LINK TO SOP IN TA. CANT GET RESPONSIBLE";
      Log::info($err, 'director');
      throw new Exception($err);
    }
};

?>
