<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR);
set_time_limit(0);
ob_implicit_flush();
//header('Content-Type: text/plain;');
require dirname(__FILE__) . '/../boot.php';

ini_set('memory_limit', '256M');
println("php memory limit: ".ini_get("memory_limit"),1,TERM_VIOLET);

gc_enable();
//gc_disable();

/*
$fh = fopen(__FILE__, 'r');
if( ! flock($fh, LOCK_EX | LOCK_NB) )
{
    echo('WARN! Script is already running');
}
*/

$TCPKEEPSESSTIMEOUT = 90; // seconds

function sockwrite($socket, $st)
{
    $length = strlen($st);
    while (true) {
        $sent = socket_write($socket, $st, $length);
        // println($sent, 1, TERM_VIOLET); // ytes sent
        if ($sent === false) {
            break;
        }
        // Check if the entire message has been sented
        if ($sent < $length) {
            println("not all bytes sent in 1 write", 1, TERM_VIOLET);
            // If not sent the entire message.
            // Get the part of the message that has not yet been sented as message
            $st = substr($st, $sent);
            // Get the length of the not sented part
            $length -= $sent;
        } else {
            break;
        }
    }
}

$host = "127.0.0.1"; // HOST; //
$port = 9900;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($sock, $host, $port);
socket_set_option($sock, SOL_SOCKET, TCP_NODELAY, 1);
socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
socket_set_nonblock($sock);
socket_listen($sock, 10); // , backlog
$clients = array($sock);
$tarr = array(); // last tcp time per socket

while (true) {
    $read = $clients; // создаём копии
    if (count($tarr) > 0) foreach ($tarr as $ind => $tim) {
        if ((time() - $tim) > $TCPKEEPSESSTIMEOUT) { // check timeout
            socket_close($read[$ind + 1]);
            $key = array_search($read[$ind + 1], $clients);
            unset($clients[$key]);
            unset($iparr[$key - 1]);
            unset($tarr[$ind]);
            println("Disconnect client by timeout", 1, TERM_YELLOW);
            continue;
        }
    }

    $write = NULL;
    $except = NULL;
    if (socket_select($read, $write, $except, 2) < 1) // get ready sockets or continue
        continue;

    if (in_array($sock, $read)) { // new conns?
        $clients[] = $newsock = socket_accept($sock);
        socket_set_option($newsock, SOL_SOCKET, TCP_NODELAY, 1);
        //socket_write($newsock, "<OK>\n"); // first hello
        socket_getpeername($newsock, $ip);
        println("New connection from ip: {$ip}", 1, TERM_YELLOW);
        $key = array_search($sock, $read);
        $iparr[] = $ip;
        $tarr[] = time();
        unset($read[$key]);
        $read[] = $newsock;
        continue;
    }

    foreach ($read as $index => $read_sock) {
        $data = socket_read($read_sock, 65536);
        if ($data === false) {
            $key = array_search($read_sock, $clients);
            unset($clients[$key]);
            unset($iparr[$key - 1]);
            println("Drop client socket if he is lost", 1, TERM_RED);
            unset($tarr[$key - 1]);
            continue;
        }

        if (!empty($data)) {


            println($iparr[$index - 1] . "[$index] - $data", 1, TERM_YELLOW);

            $memfreed = gc_collect_cycles();
            if ($memfreed > 0) println("MEMORY CLEANED: gc: -{$memfreed} refs",1,TERM_GREEN);

            $tarr[$index - 1] = time();

            $response = array();
            $query = array();
            $qchunks = explode("\0", $data);
            //println($qchunks,1,TERM_BLUE);
            //println("COUNT QCHUNKS " . count($qchunks), 1, TERM_BLUE);
            //println($qchunks[0],1,TERM_BLUE);
            for ($i = 0; $i < count($qchunks)-1; $i++) {
                //println($i,1,TERM_GRAY);
                //println($qchunks[$i],1,TERM_GRAY);
                try {
                    $query[$i] = json_decode($qchunks[$i], true);
                } catch (Exception $e) {
                    println("query parse to json error: " . $e->getMessage(), 1, TERM_RED);
                }
            }


			//$memfreed = gc_collect_cycles();
            //if ($memfreed > 0) println("MEMORY CLEANED: gc: -{$memfreed} refs",1,TERM_GREEN);

            //println($query, 1, TERM_RED);
            for ($i = 0; $i < count($query); $i++) {
                try
                {
					Utils::startTimer('test_run');
	
                    $memcur = Utils::formatBytes(memory_get_usage(),2);
                    $memmax = Utils::formatBytes(memory_get_peak_usage(),2);
                    $free = '';

                    println("MEMORY USAGE: mem used: {$memcur}, max: {$memmax}",1,TERM_VIOLET);

                    println($query[$i], 1, TERM_BLUE);
                    $m = new Message($query[$i]);
                    $res = $m->deliver();
                    if ($res instanceof DataSet)
                        $rdata = $res->toJSON();
                    else
                        $rdata = (string) $res;
                    $rdata = UnicodeOp::decodeUnicodeString($rdata);
                    $datatosend = '{"data": ' . $rdata . ', "seq": ' . $query[$i]['seq'] . '}' . "\n\0";
                    sockwrite($read_sock, $datatosend); //socket_write($read_sock, $datatosend);

					$timed = Utils::reportTimer('test_run');
					println("Time run: ".$timed['time'],1,TERM_GREEN);

                } catch (Exception $e) {
                    println($e->getMessage(), 1, TERM_RED);
                    $err = json_encode(array("state" => "error", "seq" => $query[$i]['seq'], "data" => array("error"=>$e->getMessage())));
                    sockwrite($read_sock, $err . "\n\0");
                }
            }
        }

    }
}
socket_close($sock);

//flock($fh, LOCK_UN);
//fclose($fh);

/*
 * switch ($json['action']) {
                case "close":
                    socket_close($read_sock);
                    $key = array_search($read_sock, $clients);
                    unset($clients[$key]);
                    unset($iparr[$key - 1]);
                    echo "Self disconnected by client.\n";
                    unset($tarr[$key - 1]);
                    break;
                default:

				if ($data === "servershutdown") {
				                socket_close($sock);
				                break(2);
				            }
 */
?>