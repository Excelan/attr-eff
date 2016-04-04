<?php
/**
 * external gates
 * uri: /Registration/Step1, external: 4567 - port of ruby
 * uri: /Registration/Step2, external: 9090 - port of python
 * REROUTE POST DATA
 *
 * external mq
 * manual httpRequest('/Ext/Gateasmq')
 */
class GateExternalRequest
{

    static function dispatch($URI, $data, $HOST='localhost',$PORT=9090) // URI without trailing /
    {

        if (is_array($data)) $datajson = json_encode($data);
        else if (get_class($data) == 'Message')  $datajson = (string) $data;
        else $datajson = $data;

        Log::info($URI, 'gateexternalrequest');
        try {
            $result = httpRequest("http://{$HOST}:{$PORT}/{$URI}", array('json'=> $datajson));

            #Log::debug("<< {$result['totaltime']}  dns {$result['dnstime']} connect {$result['connecttime']} start {$result['starttransfertime']}", 'gateexternalrequest');
        }
        catch (Exception $e)
        {
            Log::error($e, 'gateexternalrequesterr');
            if ($e->getCode() == 7) throw new Exception("EXTERNAL GATES ENABLED but service is not listening on host/port ".$e->getMessage());
            return null;
        }
        // есть ответ от сервера, это может быть в том числе Internal Server Error
        if ($result['httpcode'] == 200)
        {
            Log::debug('<<200 '.json_encode($result['json']), 'gateexternalrequest');
//            println($result['data']);
            return $result['json'];
        }
        else {
            Log::error($result['httpcode'].' '.$result['data'], 'gateexternalrequesterr');
            Log::error($result['httpcode'].' '.$result['data'], 'gateexternalrequest');
            //return $result['data'];
            // TODO
            return null;
        }
    }
}

?>