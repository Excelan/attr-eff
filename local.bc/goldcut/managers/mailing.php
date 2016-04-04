<?php

class Mailing extends EManager
{
    protected function config()
    {
        $this->behaviors[] = 'general_crud';
    }

    private function validate_email($email)
    {
        $valid = false;
        if(filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            // TODO Validate the domain SLOW! getmxrr
            // list($username,$domaintld) = split("@",$email);
            $valid = true;
        }
        else
        {
            $valid = false;
        }
        return $valid;
    }

    function subscribe($m)
    {
        if (!$this->validate_email($m->email))
        {
            return new Message('{"error": "incorrect_email"}');
        }
        // duplicate check
        $s = new Message();
        $s->urn = "urn-simplesubscription";
        $s->action = "load";
        $s->email = $m->email;
        $r = $s->deliver();
        if (count($r) > 0)
        {
            return new Message('{"error": "duplicate_email"}');
        }
        // save email
        $s = new Message();
        $s->urn = "urn-simplesubscription";
        $s->action = "create";
        //$s->language = $m->language;
        $s->hash = shortcode();
        // activate default?
        $s->active = 0;
        $s->email = $m->email;
        $r = $s->deliver();
        // Notify MQ
        Broker::instance()->send($s, "MANAGERS", "subscribe.new");
        return $r;
    }

    function mailactivate($m)
    {
        $s = new Message();
        $s->urn = "urn-simplesubscription";
        $s->action = "load";
        $s->hash = $m->hash;
        $r = $s->deliver();
        if (count($r))
        {
            $subscriber = $r->current();
            if (!$subscriber->is('active'))
            {
                $s = new Message();
                $s->action = "update";
                $s->urn = $subscriber->urn;
                $s->active = 1;
                $r = $s->deliver();
            }
            else
                throw Exception('Email already activated');
        }
        else
        {
            throw Exception('Email to activate not exists');
        }
        return $r;
    }

    function adminactivate($m)
    {
        $s = new Message();
        $s->urn = "urn-simplesubscription";
        $s->action = "load";
        $s->email = $m->email;
        $subscriber = $s->deliver();
        $s = new Message();
        $s->action = "update";
        $s->urn = $subscriber->urn;
        $s->active = 1;
        $r = $s->deliver();
        return $r;
    }

}

?>