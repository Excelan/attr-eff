<?php
/*
LAYOUT header, deepMain, widgetPlaces, footer, nav, menu
deepmain => controller node sitemap

*1
find all {% placeholders deep %}i

*2
find all LIST blocks
+ LIST performance.news = 
  if groupby ??
  PAIR - open-end, 
    inner - can be one more list, group by
    if (inner tags > 0) 

*3  
find all {{val}}, {{datarow.field}} var replaces - in footer - {{title}}, etc. replace from end!

*4
render = recursive depth-first from top - layout,> header, nav, DEEP > list << sidebar > widgets < footer
*/

/**
 * в шаблоны можно инжектить отображение в зависимосчти от данных и полей data-action через css классы - (никаких IF) - div id=AUTOID class=ACTIVE UNPUBLISHED
 * сда же кластеризация цветом и тп
 * черезстрочная odd even
 * +++
 * оптимизация под один проход - вырезать все теги и переменных из блоков которые не будут отображаться - role not, if not etc
 */
class Template
{

    private $template;
    public $tags = array();
    /** найденные теги */
    private $offset = 0;
    public $output = '';
    public $vars = array();
    private $voffest = 0;
    public $context;


    /**
     * парсинг шаблона. нахождение всех тегов и всех переменныъ
     */
    function __construct($T)
    {
        if (substr($T, 0, 2) == '{%') throw new Exception ("START TEMPLATE WITH SPACE, NOT WITH {%");
        $this->template = $T;
        $this->context = new TemplateContext();
        $this->eachtag();
        $this->eachvar();
        for ($i = 0; $i < count($this->tags); $i++)
            $this->tags[$i]['index'] = $i;
    }

    /**
     * вызывается из __toString()
     * make private?
     */
    function render()
    {

        $role_tags = $this->pairs('role');
        //if ($role_tags) $this->renderRolePaires($role_tags);

        $if_tags = $this->pairs('if');
        //println($if_tags);
        if ($if_tags) $this->renderIfPaires($if_tags);

        $list_tags = $this->pairs('list');
        //println($list_tags);
        if ($list_tags) $this->renderListPaires($list_tags);
        elseif ($this->tags) {
            foreach ($this->tags as $tag) {
                $this->parseBeforeTag($tag);
                if ($tag['tagp'][0] == 'layout')
                    $this->output .= "LAYOUT: " . $tag['tagp'][1];
                if ($tag['tagp'][0] == '__CONTENT__')
                    $this->output .= $this->context->get('__CONTENT__');
                $this->parseAfterTag($tag);
                if ($tag['tagp'][0] == 'menu')
                    $this->output .= "MENU: " . $tag['tagp'][1];
                if ($tag['tagp'][0] == 'navigation')
                    $this->output .= "NAV: " . $tag['tagp'][1];
            }
        } else
            $this->output = $this->template;

        foreach ($this->vars as $var) {
            $data = $this->context->get($var['vara'][0]);
            $varname = $var['varp'];
            $varvalue = (string)$this->parseVarBody($varname, $data); // (string) ?
            $this->output = str_replace('{{' . $varname . '}}', $varvalue, $this->output);
        }
        $this->output .= "\n";
    }


    /**
     * тег по его индексу
     */
    function refTag($index)
    {
        if (isset($this->tags[$index]))
            return $this->tags[$index];
        else
            throw new Exception ("Tag with index $index not found");
    }

    /**
     * массив пар тегов tagt (начиная с тега номер from по to) в массиве расперсенных тегов this.tags
     */
    function pairs($tagt, $fromindex = 0, $to = null)
    {
        $toindex = ($to) ? $to : count($this->tags);
        for ($i = $fromindex; $i < $toindex; $i++) {
            if ($this->tags[$i]['tagp'][0] == $tagt) {
                $ret['open'] = $i;
                $closetagIndex = $this->findclosetag($tagt, $i);
                $ret['close'] = $closetagIndex;
                $rets[] = $ret;
            }
        }
        if (count($rets))
            return $rets;
        else
            return false;
    }

    /**
     * получение массива переменных(не индексов) между парой тегов
     */
    function varsBetweenTagPair($pair)
    {
        $varsinbetween = array();
        $openTag = $this->refTag($pair['open']);
        $closeTag = $this->refTag($pair['close']);
        foreach ($this->vars as $var)
            if ($var['start'] < $closeTag['start'] && $var['start'] > $openTag['start'])
                $varsinbetween[] = $var;
        return $varsinbetween; // empty array || result
    }

    /**
     * парсинг шаблона внутри пары тегов [list], передача внутрь данных (контекст?)
     * парсинг {тела} переменных проиcходит здесь
     * TODO Сейчас для dataset.count делается полная выборка вместо
     * foreach category in root category
     * C = current category
     * every C topic.count = select count(id) from topic where category_id = C->id || ?
     * every C topic every message count =  select message_count from topic where category_id = C->id || add to next query
     * every C > last topic with author = SELECT id,title,max(id),category_id FROM topic GROUP BY category_id  +cache it
     * in that topic > last message time = SELECT phorum_topic_id, max(id) FROM phorum_message where phorum_topic_id in (2,3) group by phorum_topic_id || select max(id) where topic_id = $last_topic_in_cat ? +cache it

     */
    function tagPairContainer($pair, $data)
    {
        //////////////////println($data);
        $openTag = $this->refTag($pair['open']);
        $closeTag = $this->refTag($pair['close']);
        $t = substr($this->template, $openTag['offset'], ($closeTag['start'] - $openTag['offset'])); // text between pair tags
        /** для каждой {{переменной}} внутри пары тегов    */
        foreach ($this->varsBetweenTagPair($pair) as $var) {
            $varname = $var['varp'];
            $varvalue = (string)$this->parseVarBody($varname, $data); // (string) ?
            $t = str_replace('{{' . $varname . '}}', $varvalue, $t);
        }
        return $t;
    }

    function renderListPaires($list_tags)
    {
        //println($list_tags);
        // для каждого листинга
        $lasttag = count($list_tags) - 1;
        $openTag = $this->refTag($list_tags[0]['open']);
        $this->output .= substr($this->template, 0, $openTag['start']);
        $betw = 0;

        foreach ($list_tags as $pair) {

            $openTag = $this->refTag($pair['open']);
            $closeTag = $this->refTag($pair['close']);

            if ($betw > 0) {
                //println($prevtag);
                //println($openTag);
                $this->output .= substr($this->template, $prevtag['offset'], ($openTag['start'] - $prevtag['offset']));
            }

            $prevtag = $closeTag;
            $betw++;

            $EorC = $openTag['tagp'][1];
            $this->output .= "<!-- LIST [$EorC] -->";

            $x = explode('.', $EorC);
            if (count($x) > 1) {
                $y = $this->context->get($x[0]);
                $Cdata = $y->$x[1];
            } else {
                $Cdata = $this->context->get($EorC);
            }

            /*
            if (!$this->context->defined($EorC))
            {
                println('!$this->context->defined($EorC)');
                // внесение реальных данных в контекст шаблона
                // листинг категорий или данных
                if ($EorC == 'category')
                    throw new Exception("Need to define category in context");
                else
                    throw new Exception("Need to define $EorC in context");
                    //$this->context->add( $EorC, Entity::query( array('e'=>$EorC)) );
            }
            */
            // вызов цикла замены переменных в шаблоне листинга на реальные данные

            $inline_group = $this->pairs('group', $pair['open'], $pair['close']);
            if (isset($inline_group[0])) {
                $openG = $this->refTag($inline_group[0]['open']);
                $closeG = $this->refTag($inline_group[0]['close']);
                //print_r($inline_group[0]);
                foreach ($Cdata as $d)
                    $dataG[$d->year][] = $d;
                krsort($dataG);
                foreach ($dataG as $group => $groupedData) {
                    //usort($v, 'comparator');
                    $this->output .= $this->tagPairContainer($inline_group[0], $groupedData[0]);
                    foreach ($groupedData as $datarow)
                        $this->output .= $this->tagPairContainer(array('open' => 2, 'close' => 3), $datarow); // $pair TODO manual array
                }
            } else {
                foreach ($Cdata as $data)
                    $this->output .= $this->tagPairContainer($pair, $data);
            }

        }

        $closeTag = $this->refTag($list_tags[$lasttag]['close']);
        $this->output .= substr($this->template, $closeTag['offset']);
    }


    function renderIfPaires($role_tags)
    {
        //println($role_tags);
        $lasttag = count($role_tags) - 1;
        $openTag = $this->refTag($role_tags[0]['open']);
        //$this->output .=  substr($this->template, 0, $openTag['start']);

        foreach ($role_tags as $pair) {
            $openTag = $this->refTag($pair['open']);
            $closeTag = $this->refTag($pair['close']);

            $EorC = $openTag['tagp'][1];
            //$o .= $this->tagPairContainer($inline_group[0], $groupedData[0]);
            $this->output .= "<!-- ROLE [$EorC] $o -->";

            $x = explode('.', $EorC);
            println($x, 2);

        }

        $closeTag = $this->refTag($role_tags[$lasttag]['close']);
        //$this->output .=  substr($this->template, $closeTag['offset']);
    }


    function renderRolePaires($role_tags)
    {
        $lasttag = count($role_tags) - 1;
        $openTag = $this->refTag($role_tags[0]['open']);
        //$this->output .=  substr($this->template, 0, $openTag['start']);

        foreach ($role_tags as $pair) {
            $openTag = $this->refTag($pair['open']);
            $closeTag = $this->refTag($pair['close']);

            $EorC = $openTag['tagp'][1];
            //$this->output .= "<!-- ROLE [$EorC] -->";

            //$x= explode('.',$EorC);
            //println($x,2);

        }

        $closeTag = $this->refTag($role_tags[$lasttag]['close']);
        //$this->output .=  substr($this->template, $closeTag['offset']);
    }


    function parseBeforeTag($tag)
    {
        if ($tag['index'] == 0) {
            $this->output .= substr($this->template, 0, $tag['start']);
        } else {
            $pi = $tag['index'] - 1;
            $prevtag = $this->tags[$pi];
            $this->output .= substr($this->template, $prevtag['offset'], $tag['start'] - $prevtag['offset']);
        }
    }

    function parseAfterTag($tag)
    {
        $x = count($this->tags) - 1;
        if ($tag['index'] == $x)
            $this->output .= substr($this->template, $tag['offset']);
    }


    public function __toString()
    {
        $r = '';
        try {
            $this->render();
            $r = $this->output;
        } catch (Exception $e) {
            $r = (string)$e;
        }
        return (string)$r;
    }


    // {{ parse. this (and this) }}
    private function parseVarBody($varbody, $data)
    {
        if (empty($data))
            return 'NULL -' . $varbody;
        //	throw new Exception("Data for tag {$varbody} value is empty");
        $r = $data;
        $vara = explode('.', $varbody);
        for ($i = 1; $i < count($vara); $i++) {
            $v = $vara[$i];
            if (strpos($v, '(') && strpos($v, ')')) {
                $bs = strpos($v, '(');
                $be = strpos($v, ')');
                $vvar = substr($v, 0, $bs);
                $vcommand = substr($v, $bs + 1, $be - $bs - 1);
                try {
                    if (is_object($r))
                        $r = $r->$vvar;
                    if (is_object($r))
                        $r = $r->$vcommand();
                } catch (Exception $e) {
                    //echo 'Caught exception in (): ',  $e->getMessage(), "\n";
                }
            } else {
                try {
                    //$r = $r->$v;
                    if (is_object($r)) $r = $r->$v;
                    elseif (is_array($r)) $r = $r[$v];
                    else throw new Exception('Data for tag value is not array or object' . $r);
                } catch (Exception $e) {
                    //echo 'Caught exception in ->: ',  $e->getMessage(), "\n";
                }
                //if (is_array($r)) $r = $r[$v];
                //else $r = $r->$v;
                //if (is_object($r)) $r = $r->$v;
                //elseif (is_array($r)) $r = $r[$v];
                //else throw new Exception('Data for tag value is not array or object'.$r);
            }
        }
        return $r;
    }

    // {% %} parsing
    private function gettag($buf, $tag, $offset = 0)
    {
        $tago = "{" . $tag;
        $tagc = $tag . "}";
        $res = array();
        $o1 = strpos($buf, $tago, $offset);
        if ($o1 > 0) {
            $c1 = strpos($buf, $tagc, $offset);
            $res["start"] = $o1; // first { - tag start
            $res["end"] = $c1; // last }  - tag close
            $res["offset"] = $c1 + 2; // offset from template first char to tag close. =END+2 (end %} but 2chars for %} OR MORE)
            $r = trim(substr($buf, $o1 + 2, $c1 - $o1 - 2));
            //$res["tag"] = $r; // tag {% body %}
            $res['tagp'] = explode(' ', $r);
            return $res;
        } else
            return false;
    }

    // {{ }} parsing
    private function getvars($buf, $offset = 0)
    {
        $tago = '{{';
        $tagc = '}}';
        $res = array();
        $o1 = strpos($buf, $tago, $offset);
        if ($o1 > 0) {
            $c1 = strpos($buf, $tagc, $offset);
            $res["start"] = $o1; // first { - tag start
            $res["end"] = $c1; // last }  - tag close
            $res["offset"] = $c1 + 2; // offset from template first char to tag close. =END+2 (end %} but 2chars for %} OR MORE)
            $r = trim(substr($buf, $o1 + 2, $c1 - $o1 - 2));
            $res['varp'] = $r;
            $res['vara'] = explode('.', $r);
            return $res;
        } else
            return false;
    }

    private function eachvar()
    {
        while ($v = $this->getvars($this->template, $this->voffset)) {
            $this->vars[] = $v;
            $this->voffset = $v['offset'];
        }
    }

    private function eachtag()
    {
        while ($o = $this->gettag($this->template, "%", $this->offset)) {
            $this->tags[] = $o;
            $this->offset = $o['offset'];
        }
    }

    private function findclosetag($closetag, $from)
    {
        for ($i = $from; $i < count($this->tags); $i++) {
            $tag = $this->tags[$i];
            if ($tag['tagp'][0] == 'end' && $tag['tagp'][1] == $closetag)
                return $i;
        }
        return false;
    }

    /*
      function exectag()
      {
        // SWITCH {% COMMAND
        if ( $cl[0] == "list" )	// {% first command
        {
          if ( $cl[1] == "last" ) // list last news
            $datatype = $cl[2];
          else
            $datatype = $cl[1]; // list news
        }
        //println($datatype,2);
      }
    */

}

?>