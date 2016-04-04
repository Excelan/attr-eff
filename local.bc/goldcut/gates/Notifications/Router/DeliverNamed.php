<?php
namespace Notifications\Router;

class DeliverNamed
{
    use Builders;

    function gate($m)
    {
        $masterEvent = $m->masterevent->resolve();
        $systemeventrecord = $m->systemeventrecord->resolve();

        $FN = "prepare_{$masterEvent->name}";

        $mailTextContexts = $this->$FN($masterEvent, $systemeventrecord);

        // TODO push

        $sent = 0;
        // SEND MAIL
        $mailt = $masterEvent->mailplain;
        if (count($mailt))
        {
            foreach ($mailTextContexts as $mailTextContext)
            {
                //println($mailTextContext,1,TERM_VIOLET);
                $contextUser = $mailTextContext['user'];
                \Mail::sendUserTemplatesContext($contextUser, $mailt->layout, $mailt->uri, $mailTextContext);
                $sent++;
            }
        }

        return array('sent'=>$sent);
    }

}
?>