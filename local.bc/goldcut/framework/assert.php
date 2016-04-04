<?php

/**
main idea is to provide help on error
TODO add pendingFuctionality('some feature') - trace file, line
*/

class PendingException extends Exception {}
class AssertException extends Exception {}

function assertThrowsException($m, $Type, $comment=null)
{
	if (!$comment) $comment = callerFirstParam(); // = $m
	try 
	{
		$m->deliver();
	}
	catch (Exception $e)
	{
		dprintln("Throws ".$e->getMessage(). " / {$m->user} {$m->action} {$m->urn}", 1, TERM_GREEN);
		return true;
	}
	throw new AssertException("{$m} dont throw awaited exception");
}

function assertGateThrowsException($m, $Type, $comment=null)
{
	if (!$comment) $comment = callerFirstParam(); // = $m
	try
	{
		$m->send();
	}
	catch (Exception $e)
	{
		dprintln("Throws ".$e->getMessage(). " / {$m->user} {$m->action} {$m->urn}", 1, TERM_GREEN);
		return true;
	}
	throw new AssertException("{$m} dont throw awaited exception");
}

function assertFirstSmaller($first, $second, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $first < $second", 1, TERM_GREEN);
	if ($first < $second) return true;
	if ($first > $second) throw new AssertException("{$first} NOT SMALLER BUT GREATER {$second}");
	if ($first == $second) throw new AssertException("{$first} NOT SMALLER BUT EQUAL {$second}");
}

function assertArrayKeyExists(array $m, $selector, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment HAVE SELECTOR $selector", 1, TERM_GREEN);
	if (isset($m[$selector])) return true;
	throw new AssertException($m . " SHOULD HAVE $selector BUT HASNT");
}

function assertDataSetColumn(DataSet $ds, $selector, array $array, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	$column = $ds->getColumnArray($selector); // ,false,true
	dprintln("$comment $selector COLUMN EQUAL TO ".anyToString($array), 1, TERM_GREEN);
	if ($column == $array) return true;
	$diff = array_diff($array, $column);
	throw new AssertException($ds . " $comment $selector COLUMN NOT EQUAL TO ".anyToString($array)." DIFF IN ".anyToString($diff));
}

function assertSelectorExists($m, $selector, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment HAVE SELECTOR $selector", 1, TERM_GREEN);
	if (isset($m->$selector)) return true;
	throw new AssertException($m . " SHOULD HAVE $selector BUT HASNT");
}

function assertMessageWithoutError($m, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment MESSAGE WITHOUT ERROR", 1, TERM_GREEN);
	if (!$m->error) return true;
	throw new AssertException($m . ' HAVE ERROR BUT SHOULD NOT');
}
function assertMessageError($m, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment MESSAGE WITH ERROR", 1, TERM_GREEN);
	if ($m->error) return true;
	throw new AssertException($m . ' HAVENT ERROR BUT SHOULD');
}

function assertZero($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment === 0", 1, TERM_GREEN);
	if ($t === 0) return true;
	throw new AssertException($t . ' NOT ZERO');
}

function assertNull($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS NULL", 1, TERM_GREEN);
	if ($t === null) return true;
	throw new AssertException($t . ' NOT NULL');
}

function assertNotNull($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS NOT NULL", 1, TERM_GREEN);
	if ($t !== null) return true;
	throw new AssertException($t . ' IS NULL');
}

function assertEmpty($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment EMPTY (!{$t})", 1, TERM_GREEN, false);
	if (!$t) return true;
	throw new AssertException($t . ' NOT EMPTY');
}
function assertNotEmpty($t, $comment=null)
{
    if (!$comment) $comment = callerFirstParam();
    dprintln("$comment NOT EMPTY ({$t})", 1, TERM_GREEN, false);
    if ($t) return true;
    throw new AssertException($t . ' EMPTY BUT HAVE NOT TO BE');
}

function assertTrue($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS TRUE", 1, TERM_GREEN, false);
	if ($t === true) return true;
	throw new AssertException("$t $comment". ' NOT TRUE');
}

function assertNotTrue($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment NOT TRUE", 1, TERM_GREEN, false);
	if ($t !== true) return true;
	throw new AssertException($t . ' TRUE BUT HAVE NOT TO BE');
}

function assertFalse($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS FALSE", 1, TERM_GREEN, false);
	if ($t === false) return true;
	$t = print_r($t,true);
	throw new AssertException($t . ' IS NOT FALSE');
}

function assertNotFalse($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment NOT FALSE", 1, TERM_GREEN, false);
	if ($t !== false) return true;
	throw new AssertException($t . ' FALSE BUT HAVE NOT TO BE');
}

function assertExists($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS SET", 1, TERM_GREEN, false);
	if (isset($t)) return true;
	throw new AssertException($t . ' NOT EXISTS'); // ! risk on (string) t
}

function assertPositive($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS POSITIVE ($t > 0)", 1, TERM_GREEN, false);
	if ($t > 0) return true;
	throw new AssertException($t . ' NOT POSITIVE');
}

function assertNotPositive($t, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment IS POSITIVE !($t > 0)", 1, TERM_GREEN, false);
	if (!($t > 0)) return true;
	throw new AssertException($t . ' NOT POSITIVE');
}

function assertGreater($x, $y, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $x > $y", 1, TERM_GREEN, false);
	if (!is_numeric($x)) throw new AssertException("$x is not numeric");
	if (!is_numeric($y)) throw new AssertException("$y is not numeric");
	if ($x > $y) return true;
	throw new AssertException("$x NOT > $y");
}

function assertLt($x, $y, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $x < $y", 1, TERM_GREEN, false);
	if ($x < $y) return true;
	throw new AssertException("$x NOT < $y");
}
function assertEqual($x, $y, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	if (gettype($x) !== gettype($y)) $typesmismatch = ' ! TYPE MISMATCH '.gettype($x).', '.gettype($y);
	if ($x == $y) 
	{
		$testpassed = 1;
	}
	else
	{
		throw new AssertException("$x != $y NOT EQUAL".$typesmismatch);
	}
	if ($testpassed == 1)
	{
		dprintln("$comment == $y".$typesmismatch, 1, TERM_GREEN, false);
		return true;
	}
}

function assertStrictEqual($x, $y, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	if (gettype($x) !== gettype($y)) $typesmismatch = ' ! TYPE MISMATCH '.gettype($x).', '.gettype($y);
	if ($x === $y) 
	{
		$testpassed = 1;
	}
	elseif ($x == $y) throw new AssertException("$x !== $y NOT STRICT EQUAL BUT EQUAL ".$typesmismatch);
	if ($testpassed == 1)
	{
		dprintln("$comment === $y (strict equal)".$typesmismatch, 1, TERM_GREEN, false);
		return true;
	}
	else
		throw new AssertException("$x !== $y NOT STRICT EQUAL");
}

function assertNotEqual($x, $y, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	if (gettype($x) !== gettype($y)) $typesmismatch = ' ! TYPE MISMATCH '.gettype($x).', '.gettype($y);
	if ($x != $y) {
		dprintln("$comment NOT EQUAL $y".$typesmismatch, 1, TERM_GREEN, false);
		return true;
	}
	throw new AssertException("$x EQUAL $y BUT HAVE NOT TO BE");
}

function assertObjectsEqual($o1, $o2, $comment=null)
{
	if (is_string($o1)) $o1 = json_decode($o1);
	if (is_string($o2)) $o2 = json_decode($o2);
	if (is_array($o1)) $o1 = (object) ($o1);
	if (is_array($o2)) $o2 = (object) ($o2);
	// todo compare object keys. if no one similar keys - throw "Apples to Oranges"
	if (!$comment) $comment = callerFirstParam();
	$diff = array();
	$keys = array();
	foreach ($o2 as $k2 => $v2) {
		if ($o1->$k2 != $v2) array_push($diff, "$k2:{$o1->$k2} != {$v2}");
		else array_push($keys, "{$k2}:{$v2}");
	}
	if (count($diff)) throw new AssertException("Objects have difference in keys: ".join(', ',$diff).". Anyway: ".join(', ',$keys));
	dprintln("$comment object is equal to ref by keys: ".join(', ',$keys), 1, TERM_GREEN);
	return true;
}

function assertDataSetEmpty($dataset, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment dataset empty", 1, TERM_GREEN, false);
	$c = $dataset->count();
	if ($c == 0) return true;
	throw new AssertException("DATASET NOT EMPTY. IT HAS $c ROWS");
}

function assertDataSetNotEmpty($dataset, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment DATASET NOT EMPTY", 1, TERM_GREEN, false);
	if (!($dataset instanceof DataSet)) throw new AssertException("PARAM IS NOT DATASET");
	$c = $dataset->count();
	if ($c > 0) return true;
	throw new AssertException("DATASET EMPTY");
}

function assertDataSetSize($dataset, $size, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment DATASET SIZE IS $size", 1, TERM_GREEN, false);
	if ($dataset instanceof DataSet)
	{
		$c = $dataset->count();
		if ($c == $size) return true;
		throw new AssertException("DATASET SIZE MISMATCH. $c HAVE TO BE $size");
	}
	else throw new AssertException("DATASET SIZE ON NON DATASET!!!");
}

function assertEntityFullDataSetSize($entity, $size, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	$m = new Message();
	$m->urn = (string) $entity;
	$m->action = "load";
	$m->page = 1;
	$m->last = 2;
	$dataset = $m->deliver();
	$c = $dataset->total;
	dprintln("$comment DATASET SIZE $c IS $size", 1, TERM_GREEN, false);
	if ($c == $size) return true;
	throw new AssertException("ENTITY $entity FULL DATASET SIZE MISMATCH. $c HAVE TO BE $size");
}

function assertURN($urn, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $urn BE URN", 1, TERM_GREEN, false);
	if ($urn instanceof URN) return true;
	if (substr($urn,0,3) == 'urn') return true;
	throw new AssertException("$urn IS NOT URN");
}

function assertNotURN($urn, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $urn NOT URN", 1, TERM_GREEN, false);
	if ($urn instanceof URN) 
		throw new AssertException("$urn IS URN Instance. But have not to be");
	else
		return true;
	// TODO
	if (substr($urn,0,3) == 'urn')
		throw new AssertException("$urn IS URN String. But have not to be any type of urn");
	else
	 return true;
}



function assertEntityTranslated($entity, $lang_code, $comment='should be')
{
	dprintln("$comment translated to $lang_code", 1, TERM_GREEN);
	if ($entity->is_translated($lang_code)) return true;
	throw new AssertException("ENTITY:$entity->id NOT TRANSLATED TO $lang_code");
}

function assertEntityNotTranslated($entity, $lang_code, $comment='should be')
{
	dprintln("$comment not translated to $lang_code", 1, TERM_GREEN);
	if ( !$entity->is_translated($lang_code) ) return true;
	throw new AssertException("ENTITY:$entity->id HAVE NOT TO BE TRANSLATED TO $lang_code BUT IT IS");
}



function assertQueueOutgoingSize($queue_name, $qsize, $comment='should be')
{
	dprintln("$comment Queue Outgoing Size $qsize", 1, TERM_GREEN);
	if (Broker::recieve_queue_size($queue_name) == $qsize) return true;
	throw new AssertException("QUEUE $queue_name SHOULD HAVE SIZE $qsize. BUT IS HAS ". Broker::recieve_queue_size($queue_name));
}

function assertQueueOutgoingEmpty($queue_name, $comment='should be')
{
	dprintln("$comment Queue Outgoing Empty", 1, TERM_GREEN);
	if (Broker::recieve_queue_size($queue_name) == 0) return true;
	throw new AssertException("QUEUE $queue_name SHOULD BE EMPTY. BUT IS HAS ". Broker::recieve_queue_size($queue_name) . "MESSAGES IN");
}


function assertIsString($var, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $var IS STRING", 1, TERM_GREEN);
	if (is_string($var)) return true;
	throw new AssertException("$var IS NOT STRING");
}
function assertNotString($var, $comment=null)
{
	if (!$comment) $comment = callerFirstParam();
	dprintln("$comment $var IS NOT STRING", 1, TERM_GREEN);
	if (!is_string($var)) return true;
	throw new AssertException("$var SHOULD NOT BE OF TYPE STRING");
}

function assertIsA($o, $type, $comment='should be')
{
	dprintln("$comment is a $type", 1, TERM_GREEN);
	if ($o instanceof $type) return true;
	throw new AssertException(get_class($o) . " NOT OF TYPE $type");
}

function assertNotA($o, $type, $comment='should be')
{
	dprintln("$comment is not a $type", 1, TERM_GREEN);
	if (!$o instanceof $type) return true;
	throw new AssertException(get_class($o) . " SHOULD NOT BE OF TYPE $type");
}

function assertWritable($fullpath, $comment='should be')
{
	dprintln("$comment is writable $fullpath", 1, TERM_GREEN);
	if (!file_exists($fullpath))
		throw new AssertException("FILE [$fullpath] NOT EXISTS AND CANT BE WRITABLE");
	if (is_writable($fullpath))
		return true;
	throw new AssertException("FILE [$fullpath] IS NOT WRITABLE");
}

function assertFile($fullpath, $comment='should be')
{
	dprintln("$comment is file $fullpath", 1, TERM_GREEN);
	/**
	TODO we can check if folder up file exists to what level + limit level to base_dir
	 */
	if (!$fullpath)
		throw new AssertException("FILE [$fullpath] NOT EXISTS AND JUST EMPTY");
	if (file_exists($fullpath))
		return true;
	if (file_exists(BASE_DIR.$fullpath))
		return true;
	throw new AssertException("FILE [$fullpath] NOT EXISTS");
}

function assertImageSize($fullpath, $comment='should be')
{
	dprintln("$comment is image $fullpath", 1, TERM_GREEN);
	$refsize = getimagesize($fullpath);
	$refwidth = $refsize[0];
	$refheight = $refsize[1];
	if ($refwidth)
		return true;
	$refsize = getimagesize(BASE_DIR.$fullpath);
	$refwidth = $refsize[0];
	$refheight = $refsize[1];
	if ($refwidth)
		return true;
	throw new AssertException("IMAGE [$fullpath] HAVE NOT SIZE");
}

function assertStringInText($string, $text, $comment='should be found')
{
    $textpreview = mb_substr($text,0,16)."...";
    dprintln("$string found in text {$textpreview}", 1, TERM_GREEN);
    if (strpos($text, $string) === false) throw new AssertException("$string not found in text {$textpreview}");
    return true;
}

function assertStringBeforeStringInText($string1, $string2, $text, $comment='should be found')
{
    $textpreview = mb_substr($text,0,16)."...";
    dprintln("$string1 before $string2 found in text {$textpreview}", 1, TERM_GREEN);
    if (strpos($text, $string1) === false) throw new AssertException("First {$string1} not found in text {$textpreview}");
    if (strpos($text, $string2) === false) throw new AssertException("Second {$string2} not found in text {$textpreview}");
    if (strpos($text, $string1) > strpos($text, $string2)) throw new AssertException("Wrong string order. Second $string2 found before $string1 in text {$textpreview}");
    return true;
}

?>