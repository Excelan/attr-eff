<?php
function after_update_user_state($m)
{
    $old = $m[1]; // DataRow
    $new = $m[0]; // Message

    $entity = Entity::ref('user');
    foreach ($entity->statuses as $status)
    {
        if ($old->$status != $new->$status)
        {
            if (ENABLE_WRBAC === true) {
                if ($status == 'active' && $new->$status)
                    WRBAC::addUserToGroup($old->id, 1001);
            }
            // println($status);
            // add role to user
            /*
            $k = 'users-'.$status;
            $g = WRBAC::instance()->groups[$k];
            $u = WRBAC::getUserByID($old->id);
            $u->groups[]= $g;
            $g->users[]= $u;
            */
            // save to db
        }
    }

}

    $broker = Broker::instance();
    $broker->queue_declare("ENITYUPDATECONSUMER", DURABLE, NO_ACK);
    $broker->bind("ENTITY", "ENITYUPDATECONSUMER", "after.update.user");
    $broker->bind_rpc ("ENITYUPDATECONSUMER", "after_update_user_state");
?>