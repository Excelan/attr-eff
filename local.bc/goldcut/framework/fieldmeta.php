<?php

/**
 All types: integer, string, text, richtext, float, money(14,2), set(int), option(string32), date, timestamp, image, json, xml, iarray, ipv4
 */
class fieldmeta
{
    public $uid;
    public $name;
    public $title; // in primary language
    public $type;
    public $units; // measured in (ui)
    public $default; // default value, sql default value
    public $role; // ex role: international

    public $createDefault; // can be now or dont use
    public $updateDefault; // can be now or dont use

    public $raw; // non filtered html, oauthaccesstoken, urloauthlogin, facelist, freeareas, histogram64
    public $system; // ? ordered, code, dynamicsalt,
    public $virtual; // not stored. ex providedpassword, base64image
    public $noneditable; // ui level, ex: rating, commentcount
    public $disabled; // ? same as $noneditable, used in countpost. count*
    public $usereditable; // ui excplicit allow field to user edit

    //public $validator; // ? unused
    //public $base; // ? unused

    // type depend

    // type Option. sql varchar32. Аналог Status, но может быть NULL и тоже не более 2х вариантов ответа. Ответы именованы.
    public $values; // array(array('yes'=>'да'), array('no'=>'нет')) ) M->M, F->Ж
    // type Set. sql INT. Аналог entity_id связи, только значения хранит статично в конфигурации. Одно из состояний
    public $options; // type SET() dict of value=>title. array('in'=>'Приход','out'=>'Уход')

    // type richtext
    public $illustrated;
    public $autoparagraph;
    public $nofollow;
    public $htmlallowed; // list of tags allowed ex: em,img[alt|src]

    public function __construct($config)
    {
        foreach ($config as $option => $value) {
            $this->$option = $value;
        }
    }

    public function islazy()
    {
        if ($this->type == 'text' || $this->type == 'richtext') {
            return true;
        }
        return false;
    }

    public function indexOfValue($value, $nothrow=false)
    {
        if ($this->type == 'option') {
            $option1value = key($this->values[0]);
            $option2value = key($this->values[1]);
            if ($value == $option1value) {
                return 1;
            } elseif ($value == $option2value) {
                return 2;
            } else {
                if ($nothrow !== true) {
                    throw new Exception("Impossible value $value for $this");
                } else {
                    return false;
                }
            }
        } elseif ($this->type == 'set') {
            $setindex = 0;
            foreach ($this->options as $setitemname => $title) {
                $setindex++;
                if ($setitemname == $value) {
                    return $setindex;
                }
            }
            if ($nothrow !== true) {
                throw new Exception("Impossible value $value for $this");
            } else {
                return false;
            }
        } else {
            throw new Exception('F.indexOfValue only for Option and Set');
        }
    }

    public function sqldefault()
    {
        if ($this->type == 'set' || $this->type == 'option') {
            return null;
        } else {
            return $this->type;
        }
    }

    public function valueOfIndex($index)
    {
        if (!is_numeric($index)) {
            throw new Exception("Non numeric index $index in valueOfIndex");
        }
        if ($this->type == 'option') {
            $option1value = key($this->values[0]);
            $option2value = key($this->values[1]);
            if ($index == 1) {
                return $option1value;
            } elseif ($index == 2) {
                return $option2value;
            } else {
                throw new Exception("Impossible index $index for option $this");
            }
        } elseif ($this->type == 'set') {
            $setindex = 0;
            foreach ($this->options as $setitemname => $title) {
                $setindex++;
                if ($setindex == $index) {
                    return $setitemname;
                }
            }
            throw new Exception("Impossible index $index for set $this");
        } else {
            throw new Exception('F.indexOfValue only for Option and Set');
        }
    }

    public function __toString()
    {
        if ($this->type == 'option') {
            $suffix = anyToString($this->values);
        } elseif ($this->type == 'set') {
            $suffix = anyToString($this->options);
        }
        return 'FIELD '.$this->name." ({$this->type}) {$this->uid} {$suffix} $this->title";
    }
}
