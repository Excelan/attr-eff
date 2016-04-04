<?php 
/**
EntityDB_delete
*/

class EntityDB_delete extends EntityDB 
{
	/**
	DELETE
	 */
	function delete($data)
	{
        //println($data,1,TERM_RED);
        $uuid = $data->urn->uuid();
        $DEL = $data->urn->resolve()->current(); // .., res($lang, true - nocache resolve
        //println($DEL,1,TERM_GREEN);
        foreach ($data->urn->entity->belongs_to() as $hostEntity)
        {
            $count_field_name = 'count'.$data->urn->entity->name;
            $has_field = $hostEntity->has_field($count_field_name);
            if ($has_field)
            {
                //println("DEL HAS $count_field_name $has_field",1,TERM_RED);
                //println($DEL,1,TERM_YELLOW);
                $dk = $hostEntity->name.'_id';
                //println($dk,2,TERM_VIOLET);
                $hostUUID = $DEL->$dk;
                if (!$hostUUID) continue;
                //printlnd($hostUUID,1,TERM_RED);
                $hostURN = URN::build($hostEntity->name, $hostUUID);
                $m = new Message();
                $m->urn = $hostURN;
                $m->action = 'decrement';
                $m->field = $count_field_name;
                $m->value = 1;
                //println($m,1,TERM_RED);
                $countcurrent = $m->deliver();
                if ($countcurrent < 1) {
                    $mq = "lostlast.{$hostEntity->name}.{$data->urn->entity->name}";
                    Broker::instance()->send(array($hostURN, $DEL->urn), "ENTITY", $mq);
                }
            }
            $mq = "unaquire.{$hostEntity->name}.{$data->urn->entity->name}";
            Broker::instance()->send(array($hostURN, $DEL->urn), "ENTITY", $mq);
        }
		$q = "DELETE FROM ".SQLQT.$this->E->getTableName().SQLQT;
		$q .= " WHERE id = " . $uuid->toInt();
		$this->dblink->nquery($q);

		return $uuid->toInt();
	}

}
?>