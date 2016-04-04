<?php
/**
datarow decorator
RPC
 * API
 * CAN ASK CONTROLLER FROM UNIT TESTS!
 *  Scenario of gate to next gate
 * NO ACCESS TO ENV, GLOBALS, EXTERNAL CLASSES OR FUNCTIONS! ip, userid are provided. Access to actions
 * parameters: json
 * universal node.js gate: reroute to http port (any lang server), tcp port, websocket, script
 * gate vs manager: Controller vs Model. Doing vs Storing Done
 *  Delivery calculate is controller (business logic) not Model (product data)
 * gate vs app(screen): Controller vs Screen
 * gate vs mq: 2 way/gate/news/like, pub sub on.news.like +gate can be attached to mq. MQ save state between cons
 * gate vs db procedure vs action: 1 return generated data + signals  2 return stored entity data + related entities  3 return stored single entity dataset.
 *  Action can be db procedure
 *  DB PROC / simple lang chat with db
 *      ACTION: pay, like, register, activate - batch of insert, update, delete
 *      QUERY: NAMED LOAD QUERY with context, params
 *  send action/query to gate(db gate +ws gate) vs gate as rpc vs gate as form/scenario/screens/chat flow
 * gate vs global functions: Gate::ask('delivery/calculate', json(product, zone)) +ip, userid, subdomain, env(gate defined asked params)
 * gate: route to next screen, non db procedure call - call other gates,
 * php gates: folder, file per gate
 *  route to class/method, class/constructor,
 * unit test: selftest() in gate class
 * gate vs unix pipe: {email: test@test.com} | member/register | member/activate | social.recommendfriends | email.sendinvite
 *  {email: test@test.com} | register/step1 > {name: Tester} | register/step2
 * func, validate data, config min data, exceptions - func, selftest
 *
 * /ns/gate
 *      in/out connection to app. ns/deppns/action. << DATA, SIGNALS
 *      remote (RESULT1,RESULT2) = PROCEDURECALL(PARAM1, PARAM2)
 * {message.action}
 *      db only?
 *
 * Gate return routing info (universal web, #js, ios, android routing), errors localized, signals(reverse action, not reply to action), data(urn lists, rows etc)
 * Manager.Action return error code/uri without text
 *
 * member/register(email,password etc)
 * game/match/start, match/join(gameid)
 * chat/newmessage VS chat/connectinout + message.action = newmessage
 * PER GATE MANAGERS?
 *  database-gate/rdbms-manager/create-action
 *  database-gate/list-manager/add-action
 *  database-gate/graph-manager/newedge-action
 *  member-gate/user-manager/register-action
 *  member-gate/session-manager/userfromhash-action
 *  chat-gate/conversation-manager/newmessage-action
 *  billing-gate/service-manager/change_pack-action
 *  billing-gate/service-manager/invitesys-managedNS/useinvite-action
 *  delivery-gate/api-manager/calculate-action (NON URN!)
 *
 * STORED PROCEDURE
 * ACTION AS DB PROCEDURE
 * /ecommerce/cart/put as plv8?
 * /member/user/register as plv8? xml config to json config param
 *
 * APP/DOMAIN:
 *      config (app config, provide entity), cron tasks, wrbac groups, group permissions to entity
 *      gates, mq listeners
 *      email templates
 *      web apps, web layouts, web widgets
 *      mobile?
 *          + per app xml config, widgets xml config
 *
 * SYSTEM APPS?
 * OVERLAY?
 *  local config for system Member gate
 *  local LMemeber web app, widgets
 *
 * Filter GATE - action LIST members, then action database
 * FOREACH APPLY GATE/MANAGER/ACTION/ ()
*/

class GateCommandDataException extends \GateException
{
}


// протокол работы вернуть по gate->history()
// внешний универсальный лог
// пример системы гейтов php, python, scala
class Gate
{

    protected $ns;
    protected $gatename;
    protected $env;
    protected $data;
    //protected $command;
    //protected $response;
    //private $requestFormat;
    private $responseFormat;
    private $requestOK;
    private $responseOK;

    function __construct($env, $data)
    {
        $this->env = $env;
        $this->data = $data;

        $filepath = BASE_DIR.'/gates'.$env['uri'].'.xml';
        if (file_exists($filepath))
        {
            $doc = new DOMDocument();
            $doc->load($filepath);
            if (!$doc->documentElement) throw new Exception("Error in gate xml config file " . $filepath);
            $this->ns = $doc->documentElement->getAttribute('ns');
            $this->gatename = $doc->documentElement->getAttribute('name');
	        $ga = explode('/',$data->gate);
	        $gateClass = $ga[count($ga)-1];
	        if (ENV == 'TEST' && $this->gatename != $gateClass) throw new Exception("Not equal gate name in XML {$this->gatename} ne {$gateClass}"); // TODO
            $requestFormat = $doc->getElementsByTagName('request')->item(0);
            if (!$requestFormat) throw new Exception("No request block in gate xml config $this->ns $this->gate");
	        $specDiff = $this->recCheck($requestFormat, $data, 0, array(), 'TOPLEVEL', 'request');
	        if (count($specDiff))
		        throw new Exception("Over specification in gate {$this->ns}/{$this->gatename} request {$data->gate}: ".anyToString($specDiff));
            $this->responseFormat = $doc->getElementsByTagName('response')->item(0);
            if (!$this->responseFormat) throw new Exception("No response block in gate xml config $this->ns $this->gate");
        }
        else
            Log::error("Gate xml not exists for {$env['uri']}", 'gates');
    }

    function recCheck($requestFormat, $data, $level, $stack, $topname, $rr='')
    {
	    $specDiff = [];
        $checkedElement = 0;
        $level++;
        $thisLevelSpec = [];
        foreach($requestFormat->childNodes as $n)
        {
            if ($n->nodeType == 1)
            {
                $checkedElement++;
                $name = $n->getAttribute('name');
                $type = $n->getAttribute('type');
                $req = $n->getAttribute('required');
                $required = txt2boolean($req);
	            array_push($thisLevelSpec, $name);
                $multiple = txt2boolean($n->getAttribute('multiple'));
                $tag = $n->tagName;
                array_push($stack, $name);
                //println("$name $type $req", $level);
                if (is_array($data)) $dataItem = $data[$name];
                else if (get_class($data) == 'Message') $dataItem = $data->$name;
                else if (is_null($data)) $dataItem = null;
                else
                {
                    printlnd($data,1,TERM_RED);
                    throw new \Exception("Gate {$this->ns}/{$this->gatename} $rr object and structs can be only array or Message for key ".anyToString($stack));
                }
                if ($required)
                {
                    $base = ($rr == 'request') ? 470 : 520;
                    if ($dataItem === null || $dataItem === "") throw new GateCommandDataException("Under specification: $name is required for gate {$this->ns}/{$this->gatename} $rr in data {$level} `{$topname}` ".anyToString($data) , $base+$checkedElement);
                    //else println($data->$name,$level,TERM_GREEN);
                }
                //else println($data->$name,$level,TERM_VIOLET);
                // struct
                if ($type == 'struct' && $multiple === false)
                {
                    if ($dataItem != null)
                    {
	                    $scc = $this->recCheck($n, $dataItem, $level, $stack, $name, $rr);
	                    if (count($scc)) $specDiff[$name] = $scc;
                    }
                }
                // array of struct
                if ($type == 'struct' && $multiple === true) {
                    $minimum = (int) $n->getAttribute('minimum');
                    $maximum = (int) $n->getAttribute('maximum');
                    if ($required === true && $minimum == 0) $minimum = 1;
                    $counter = 0;
                    if ($dataItem != null)
                    {
                        if (is_array($dataItem))
                            $dataitemconverted = $dataItem;
                        else
                            $dataitemconverted = $dataItem->toArray();
                        foreach ($dataitemconverted as $key => $dataItem)
                        {
                            $counter++;
                            if ($counter > $maximum && $maximum > 0) throw new \Exception("Under specification: Gate {$this->ns}/{$this->gatename} $rr key $name over maximum $maximum elements " . anyToString($stack));
                            $scc = $this->recCheck($n, $dataItem, $level, $stack, $name, $rr);
	                        if (count($scc)) $specDiff[$name] = $scc;
                        }
                    }
                    if ($counter < $minimum)
                    {
                        if ($counter == 0 && $required === false)
                        {
                        } // если элементы необязательны, то их или не должно быть вовсе или должен быть минимум
                        else
                            throw new \Exception("Under specification: Gate {$this->ns}/{$this->gatename} $rr key $name below minimum $minimum elements " . anyToString($stack));
                    }
                }
            }
        }
	    if (is_array($data)) $datacheck = array_keys($data);
	    else if (get_class($data) == 'Message') $datacheck = array_keys($data->toArray());
	    else
	    {
		    throw new Exception("Specification violation: gate {$this->ns}/{$this->gatename} $rr non array or message");
	    }
	    //unset($datacheck['gate']);
//	    println($level);
//	    printlnd($data);
	    //println($datacheck);
//	    println($thisLevelSpec,2);
	    $specDiff1 = array_values(array_diff($datacheck, $thisLevelSpec, ['gate']));
	    $specDiff = array_merge($specDiff, $specDiff1);
	    if (count($specDiff1))
	    {
		    //printlnd($specDiff, 2, TERM_YELLOW);
		    //printlnd($data, 3, TERM_GRAY);
		    //printlnd($datacheck, 3, TERM_GRAY);
	    }
	    return $specDiff;
    }

    function checkResponse($response)
    {
	    $specDiff = $this->recCheck($this->responseFormat, $response,  0, array(), 'TOPLEVEL', 'response');
	    if (count($specDiff))
		    throw new Exception("Over specification in gate {$this->ns}/{$this->gatename} response {$data->gate}: ".anyToString($specDiff));

    }

    // NODE.JS realization
    // php simple rpc http server (legacy logic)
    // php action/query entity server + can reuse 1 connection (actions as procedures can go direct from nodejs to pgsql, queries too)
    // !php is still app/screen/views http server!

    /*
    public static function send(Message $m)
    {

    }
    */

    public static function routable($URI)
    {
        if (GATES_ENABLED !== true) return null;
        if ($GLOBALS['CONFIG']['GATEROUTING'][$URI])
        {
            if ($GLOBALS['CONFIG']['GATEROUTING'][$URI]['type'] == 'internal') {
               return true;
            }
            elseif ($GLOBALS['CONFIG']['GATEROUTING'][$URI]['type'] == 'external') {
                if (EXTERNAL_GATES_ENABLED === true)
                    return true;
                else
                    return null;
            }
        }
        else return false;
    }

    public static function route($URI, $data)
    {
        if (!$GLOBALS['CONFIG']['GATEROUTING'][$URI]) throw new Exception("No route $URI to gate in gates table");

        if ($GLOBALS['CONFIG']['GATEROUTING'][$URI]['type'] == 'internal')
        {
            try {
                $result = GateRequest::dispatch($URI, $data);
                if ($result !== null)
                {
                    return $result;
                }
            }
            catch (Exception $e)
            {
                $error = GCException::ghostBuster($e, $URI);
                throw $e;
            }
        }
        elseif ($GLOBALS['CONFIG']['GATEROUTING'][$URI]['type'] == 'external') // if (EXTERNAL_GATES_ENABLED === true)
        {
            $result = GateExternalRequest::dispatch($URI, $data, $GLOBALS['CONFIG']['GATEROUTING'][$URI]['host'], $GLOBALS['CONFIG']['GATEROUTING'][$URI]['port']);
            if ($result !== null)
            {
                return $result;
            }
            else
            {
                // ??????????????
                return null;
            }
        }
        else
        {
            throw new Exception("Gate locality type not defined");
        }
        //
    }

    /*
     *
    if (GATES_ENABLED === true)
    {
        try {
            $result = GateRequest::dispatch($URI, $_POST);
            if ($result !== null)
            {
                header("Content-type: application/json");
                print $result; // TODO json???
                exit(1);
            }
        }
        catch (Exception $e)
        {
            $error = GCException::ghostBuster($e, $URI);
            exit(1);
        }
    }
    if (EXTERNAL_GATES_ENABLED === true)
    {
        $result = GateExternalRequest::dispatch($URI, $_POST);
        if ($result !== null)
        {
            header("Content-type: application/json");
            print json_encode($result);
            exit(1);
        }
    }
     */



}

?>