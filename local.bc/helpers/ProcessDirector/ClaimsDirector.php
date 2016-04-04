<?php

$GLOBALS['DIRECTOR_DMS_Execution_Doing_DoingTask'] = function ($mpe, $subjectPrototype)
{
    Log::info('DIRECTOR_DMS_Execution_Doing_DoingTask', 'director');
    $subjectURN = new URN($mpe->subject);
    $subject = $subjectURN->resolve();
    Log::info('subject '.$subject, 'director');
    $postOfExecutor = $subject->DocumentSolutionUniversal->executor; // <<<
    Log::info('postOfExecutor '.$postOfExecutor, 'director');
    return (string)$postOfExecutor->urn;
};

$GLOBALS['DIRECTOR_DMS_Decisions_Reviewing_Review'] = function ($mpe, $subjectPrototype)
{
    Log::info('DIRECTOR_DMS_Decisions_Reviewing_Review', 'director');
    Log::info('mpe '.$mpe, 'director');
    $subjectURN = new URN($mpe->subject);
    $subject = $subjectURN->resolve();
    Log::info('subject '.$subject, 'director');
    //$post = $subject->initiator; // <<
    $post = $mpe->initiator; // <<
    Log::info('post '.$post, 'director');
    Log::info('post alt '.$subject->initiator, 'director');
    if ($post->urn)
    {
      Log::info('post.urn '.$post->urn, 'director');
      return (string)$post->urn;
    }
    elseif ($subject->initiator)
    {
      return (string)$subject->initiator;
    }
      else {
        Log::error('NO post.urn '.$post, 'director');
        return 'urn:Actor:Error:Error:0';
      }
};

?>
