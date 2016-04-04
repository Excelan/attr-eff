<?php
/**
General_Virtual: fork, describe
 */
class General_Virtual
{
    /**
     * reload base virtual object by urn
     * get virtual attributes
     * check is overlay allowed from attributes
     * ! create new realization object with attr overlay
     */
    public function fork($message)
    {
        $self = $message->urn->resolve();
        if (!$self->virtual) throw new Exception("Cant fork non virtual product");

        //$attr = json_decode($self->_attributes, true);
        //$attrover = json_decode($v->_attributeoverlay, true);
        /*
        $attrover = json_decode($message->_attributeoverlay, true);
        $am = array_merge($attr, $attrover);
        foreach ($am as $ak => $av)
            println("$ak => ".json_encode($av), 1, TERM_VIOLET);

        */

        $attributesds = $self->attribute;

        foreach ($attributesds as $attr) {
            println($attr,1,TERM_RED);
            $attributes[] = $attr;
        }

        foreach ($attributes as $attr)
        {
            $pname = $attr->uri;
            if ($message->$pname)
            {
                $eattributes[$pname] = $message->$pname;
                $message->clear($pname);
                println($attr,2,TERM_RED);
            }
        }
        println($eattributes,3,TERM_RED);

        $m = $message; // to pass title etc
        $m->action = 'create';
        $m->virtual = false;
        $m->urn = (string) $message->urn->entity;
        $m->_parent = $self->urn->uuid()->toInt();
        $m->_attributes = json_encode($eattributes);
        //$m->_attributeoverlay = $message->_attributeoverlay;
        println($m,1,TERM_RED);
        $d = $m->deliver();
        return $d;
    }

    public function describe($message)
    {
        println($message);
        $v = $message->urn->resolve();

        $attr = json_decode($v->_attributes, true);
        foreach ($attr as $ak => $av)
            println("$ak => $av");

        $attrover = json_decode($v->_attributeoverlay, true);
        foreach ($attrover as $ak => $av)
            println("$ak => ".json_encode($av), 1, TERM_BLUE);

        return "DES";
    }

}
?>