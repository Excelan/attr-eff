<?php

/**
All Elements	*	//*
All P Elements	p	//p
All Child Elements	p > *	//p/*
Element By ID	#foo		//*[@id='foo']
Element By Class	.foo		//*[contains(@class,'foo')] 1
Element With Attribute	*[title]		//*[@title]
First Child of All P	p > *:first-child	//p/*[0]
All P with an A child	Not possible	//p[a]
Next Element	p + *	//p/following-sibling::*[0]
 */

function domInnerHTML($element)
{
    $innerHTML = "";
    $children = $element->childNodes;
    foreach ($children as $child)
    {
        $tmp_dom = new DOMDocument("1.0","UTF-8");
        $tmp_dom->appendChild($tmp_dom->importNode($child, true));
        $innerHTML .= trim($tmp_dom->saveHTML()); // saveXML!
    }
    return $innerHTML;
}

function node2doc($n)
{
    $t = new DOMDocument("1.0","UTF-8");
    $t->appendChild($t->importNode($n, true));
    return $t;
    //$originDocNode->appendChild($originDoc->importNode($tmpDoc->documentElement, true));
}

/**
TODO <p class='title'>Title <span class='time'>WIPED!</span></p>
TODO join('. ', arrayofvals) make html logic
 */
function recursiveKeyDataFragmentRenderer(DOMDocument $f, $d, $k = null, $pk = null)
{
    $debug = false;
    if ($debug) print "<blockquote>";
    if ($debug) print "<h3>recursiveKeyDataFragmentRenderer $k</h3>";
    if ($k && is_array($d)) $datalocal = $d[$k];
    else if ($k && is_object($d)) $datalocal = $d->$k;
    else $datalocal = $d;
    if (is_array($datalocal))
    {
        if ($debug) print "<font color='red'>is_array(datalocal) $k</font>\n";
        $els = getListContainer($f, $k);
        if ($els->length == 1)
        {
            $group = getListGroupper($f, $k);
            if ($group->length)
            {
                $groupped = true;
                $groupsProcessed = 0;
                $g = $group->item(0);
                $gp = $g->parentNode;
                $groupBy = $g->getAttribute('data-groupby');
                $skipFirstGroup = ($g->getAttribute('data-skipfirstgroupheader') == 'yes') ? true : false;
                $gp->removeChild($g);
            }
            $e = $els->item(0);
            if (!$k) $e->removeAttribute('data-list');
            if ($debug) echo "[A:". $e->nodeName. ' @ ' . $e->getAttribute('class')  . "]\n";
            $pn = $e->parentNode;
            // REMOVE LIST-ORIGINAL-CONTAINER FROM MAIN DOC
            $pn->removeChild($e);
            if ($debug) echo '<font color=silver size=-1>'.htmlspecialchars($f->saveXML($f->documentElement)).'</font>';
            // EACH LIST DATA
            foreach ($datalocal as $v)
            {
                if ($groupped)
                {
                    $groupCriteriaValue = $v->$groupBy;
                    if ($groupCriteriaValue != $prevGroupCriteriaValue)
                    {
                        $groupsProcessed++;
                        if (!($groupsProcessed == 1 && $skipFirstGroup))
                        {
                            $fg = node2doc($g);
                            queryReplaceKeyInFragment($fg, 'grouptitle', $groupCriteriaValue); // add data-prepend=text data-append=text
                            $gp->appendChild($f->importNode($fg->documentElement, true));
                        }
                    }
                    $prevGroupCriteriaValue = $groupCriteriaValue;
                }
                // CREATE TEMP DOC LIST ELEMENT AS CLONE OF LIST CONTAINER ORIGINAL
                $t = new DOMDocument("1.0","UTF-8");
                $t->formatOutput = true;
                $t->appendChild($t->importNode($e, true));
                $t->firstChild->setAttribute('data-item',++$k);
                if ($debug) echo '<font color=green>'.htmlspecialchars($t->saveXML($t->documentElement)).'</font>';
                // DATA TO TEMP DOC LIST ELEMENT
                recursiveKeyDataFragmentRenderer($t, $v);
                // APPEND LIST ELEMENT TO MAIN DOC
                if ($debug) print "<font color=red>APPEND TO MAIN</font>";
                if ($debug) echo '<font color=olive>'.htmlspecialchars($t->saveXML($t->documentElement)).'</font>';
                //if ($groupped) $pn->appendChild($g);
                $pn->appendChild($f->importNode($t->documentElement, true));
                if ($debug) echo '<font color=silver>'.htmlspecialchars($f->saveXML($f->documentElement)).'</font>';
            }
        }
        else
        {
            if ($debug) print debugDom($f);
            if ($els->length > 1)
            {
                if ($debug)
                {
                    foreach ($els as $e) echo "[ER:". $e->nodeName. ' @ ' . $e->getAttribute('class') . "]\n";
                    throw new Exception("[data-list=$k] in DOM found more then one");
                }
            }
            else
            {
                if ($debug) throw new Exception("[data-list=$k] in DOM not found");
            }
        }
    } // DEEP: {}
    else if (is_object($datalocal) && $k) { // на первом проходе данные являются объектом, а нам нужны вложенные объекты
        // FOCUS ON PART OF DOM NAMED AS OBJECT KEY ENTRY (.., image: {..}, ..) <news><_image_><img>
        $els = getElementsByAny($f, $k);
        if ($debug) print "<font color='orange'>DEEP is_object(datalocal) && k $k elsCount: {$els->length}</font>\n";
        if ($els->length)
        {
            $e = $els->item(0);
            if ($debug) echo "[++". $e->nodeName. ' @ ' . $e->getAttribute('class') . "]\n";
            $cwn = node2doc($e); // newDomDocumentFromNode or node2doc
        }
        else
        {
            $cwn = $f;
        }
        foreach ($datalocal as $kk => $v)
        {
            if ($debug) print("<i>D:$kk</i>, \n");
            recursiveKeyDataFragmentRenderer($cwn, $datalocal, $kk, $k);
            if ($els->length)
            {
                if ($debug) echo '<font color=brown>'.htmlspecialchars($cwn->saveXML($cwn->documentElement)).'</font>';
                if ($debug) echo '<font color=gold>'.htmlspecialchars($f->saveXML($e)).'</font>';
                //$e->parentNode->replaceChild($f->importNode($cwn->documentElement, true), $e);
            }
        }
        if ($els->length) $e->parentNode->replaceChild($f->importNode($cwn->documentElement, true), $e);
        //else $e->parentNode->replaceChild($f->importNode($cwn->documentElement, true), $e);
    }
    else if (is_object($datalocal) && !$k) {
        if ($debug) print("ROOT OBJECT is_object(\$datalocal) && \$k\n");
        //var_dump($datalocal);
        foreach ($datalocal as $kr => $v)
        {
            if ($debug) print("<i>r:$kr</i>, \n");
            recursiveKeyDataFragmentRenderer($f, $datalocal, $kr, $k);
        }
    }
    else // final object for keys replace {title: etc}
    {
        if ($debug) print "FINAL KEY $k of $pk <br>\n";
        //var_dump($k, $f, $datalocal, $pk);
        if ($datalocal !== null)
            $replacedCount = queryReplaceKeyInFragment($f, $k, $datalocal, $pk);
        else
            queryRemoveElementByKeyInFragment($f, $k);
    }
    if ($debug) print "</blockquote>";
}

function queryRemoveElementByKeyInFragment($f, $k)
{
    $els = getElementsByAny($f, $k);
    foreach ($els as $e)
    {
        if ($e->hasAttribute($k))
            $e->removeAttribute($k);
        else
            $e->parentNode->removeChild($e);
    }
    return $els->length;
}

// [data-selector="k"] > setAttribute | innerhtml
// [attr] > setAttribute
// .class > innerhtml
// #id > innerhtml
// + <tagname match?
function queryReplaceKeyInFragment($f, $k, $value, $pk)
{
    $debug = false;
    //if ($debug) print "<b>queryReplaceKeyInFragment $k of PK($pk) </b> value: "; //var_dump($f, $k, $value, $pk);
    //if ($debug) var_dump($k,$value);
    $els = getElementsByAny($f, $k); // getElementsByClassname($f, $k, $pk)
    foreach ($els as $e)
    {
        $writeTo = $e->getAttribute('data-write');
        //if ($debug) var_dump($e->hasAttribute($k));
        $writeAttrib = $k;
        if ($writeTo) $writeAttrib = $writeTo;
        if ($debug) echo "[". $e->nodeName. " @ $k " . $writeTo . ':' . $writeAttrib . "]\n";
        if ($e->hasAttribute($writeAttrib))
            $e->setAttribute($writeAttrib, $value);
        else
            innerHTML($e, $value);
    }
    return $els->length;
}

function getElementById(DOMDocument $doc, $id)
{
    $xpath = new DOMXPath($doc);
    $node = $xpath->query("//*[@id='$id']");
    //         var_dump($loggeduserDom->item(0));
    if ($node->length);
    return $node->item(0);
}

/**
[data-selector=K]
class='K'
id=K
 */
function getElementsByAny(DOMDocument $doc, $k)
{
    $xpath = new DOMXPath($doc);
    $classname = $k;
    $containsClassXPath = "//*[contains( normalize-space( @class ), ' $classname ' ) or substring( normalize-space( @class ), 1, string-length( '$classname' ) + 1 ) = '$classname ' or substring( normalize-space( @class ), string-length( @class ) - string-length( '$classname' ) ) = ' $classname' or @class = '$classname']";
    $nodes = $xpath->query("//*[@data-selector='$k']|//*[@$k]|$containsClassXPath|//*[@id='$k']");
    return $nodes;
}
// xpath examples
//*[@id='foo']
//*[@title]
//div[@id='part2']/a[3]
//div[@id='part2']/a[3]/@href
//div[not(@id)]
//div[not(@id)] | //div[@id='part1']
//tr[2]/td[2]/p[2]
function getListContainer(DOMDocument $doc, $k=null)
{
    $xpath = new DOMXPath($doc);
    if (!$k) $k = 'root';
    $q = "//*[@data-list='$k']";
    //if ($k) $q = "//*[@data-list='$k']";
    //else $q = "//*[@data-list]";
    $nodes = $xpath->query($q);
    return $nodes;
}
function getListGroupper(DOMDocument $doc, $k=null)
{
    $xpath = new DOMXPath($doc);
    if (!$k) $k = 'root';
    $q = "//*[@data-grouplist='$k']";
    $nodes = $xpath->query($q);
    return $nodes;
}
function getPlaceholders(DOMDocument $doc)
{
    $xpath = new DOMXPath($doc);
    $q = "//*[@data-placeholder]";
    $nodes = $xpath->query($q);
    return $nodes;
}
/**
function getElementsByClassname( DOMDocument $doc, $classname, $pk = null )
{
$xpath = new DOMXPath( $doc );
// XPath 2.0
// $nodes = $xpath->query( "//*[count( index-of( tokenize( @class, '\s+' ), '$classname' ) ) = 1]" );
// XPath 1.0
if ($pk) $searchIn = "*[contains(@class,'$pk')]/";
else $searchIn = '';
$nodes = $xpath->query( "//$searchIn*[contains( normalize-space( @class ), ' $classname ' ) or substring( normalize-space( @class ), 1, string-length( '$classname' ) + 1 ) = '$classname ' or substring( normalize-space( @class ), string-length( @class ) - string-length( '$classname' ) ) = ' $classname' or @class = '$classname']" );
return $nodes;
}
 */
function innerHTML($node, $html)
{
    $debug = false;
    // тк нет .innerHTML = html
    $f = $node->ownerDocument;
    $fr = $f->createDocumentFragment();
    $fr->appendXML($html);
    $node->nodeValue = '';
    $node->appendChild($fr);
    if ($debug)
    {
        global $gi;
        $gi++;
        $node->setAttribute('P',$gi);
    }
}


function debugDom($d)
{
    return htmlspecialchars($d->saveXML($d->documentElement));
}

function debugDomElement($el, $forhtml=true)
{
    $data = $el->ownerDocument->saveXML($el);
    if ($forhtml)
        return htmlspecialchars($data);
    else
        return $data;
}
function domGetNodeInnerHtml($node)
{
    $innerHTML= '';
    $children = $node->childNodes;
    foreach ($children as $child)
    {
        $innerHTML .= $child->ownerDocument->saveXML($child);
    }
    return $innerHTML;
}

function cerrarTag($tag, $xml){
    $indice = 0;
    while ($indice< strlen($xml)){
        $pos = strpos($xml, "<$tag ", $indice);
        if ($pos){
            $posCierre = strpos($xml, ">", $pos);
            if ($xml[$posCierre-1] == "/"){
                $xml = substr_replace($xml, "></$tag>", $posCierre-1, 2);
            }
            $indice = $posCierre;
        }
        else break;
    }
    return $xml;
}
/**
saveHTML - no html5 tags (nav etc)
saveXML - problems with selfclosed <iframe * />
LIBXML_NOEMPTYTAG - imframe ok but </img>
 */
function renderGCtemplate($path, $ds)
{
    if ($ds instanceof DataSet || $ds instanceof DataRow || $ds instanceof Message) throw new Exception("DataSet, DataRow, Message as template data are not supported");
    $pxhtml = realpath($path.".xhtml");
    $phtml = realpath($path.".html");
    $useXMLtemplate = file_exists($pxhtml);
    $d = new DOMDocument;
    if ($useXMLtemplate)
        $d->loadXML(file_get_contents($pxhtml));
    else
        $d->loadHTML(file_get_contents($phtml));
    if (!$d) throw new Exception("loadXML error on $pxhtml $phtml");
    recursiveKeyDataFragmentRenderer($d, $ds);
    //var_dump($path,$useXMLtemplate, $d->saveHTML());
    if ($useXMLtemplate)
        $html = $d->saveHTML();
//        $html = $d->saveXML($d->documentElement); // , LIBXML_NOEMPTYTAG
    else
//        $html = $d->saveXML($d->documentElement->firstChild->firstChild); // , LIBXML_NOEMPTYTAG
        $html = $d->saveHTML($d->documentElement->firstChild->firstChild); // , LIBXML_NOEMPTYTAG
    $html = cerrarTag("iframe", $html);
    return $html;
}

?>