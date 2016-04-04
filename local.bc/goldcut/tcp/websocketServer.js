/*
var net = require('net');

var HOST = '127.0.0.1';
var PORT = 9900;

var tcpclient = new net.Socket();

// Add a 'data' event handler for the client socket
// data is what the server sent to this socket
tcpclient.on('data', function(data) {

    console.log('TCP DATA: '  + data);
    // Close the client socket completely
    //client.destroy();

});

// Add a 'close' event handler for the client socket
tcpclient.on('close', function() {
    console.log('CONN CLOSED');
});


tcpclient.connect(PORT, HOST, function()
{
    console.log('CONNECTED TO: ' + HOST + ':' + PORT);
    // Write a message to the socket as soon as the client is connected, the server will receive it as message from the client
    //client.write(str);

});

*/


// supervisor -w websocket -e js websocket/wss.js 
var gtcpclient = require('../goldcut/tcp/gctcpclient.js');
var remoteQuery = gtcpclient.remoteQuery;
var getRand = gtcpclient.getRand;

var onerror = function(er) { console.log("- !!! ", er) };




var fs = require('fs');


var WebSocketServer = require('ws').Server
var wss = new WebSocketServer({port: 8023});

wss.broadcast = function(data) {
    for(var i in this.clients)
        this.clients[i].send(data);
}

//var ws = new WebSocket('ws://echo.websocket.org/', {protocolVersion: 8, origin: 'http://websocket.org'});

wss.on('connection', function(ws) {
    
	console.log('on connection');

	ws.send(JSON.stringify({urn: "urn-server-1", state: "online"}));

	remoteQuery({action: "select", urn: "urn-product"}).then(
        function(resp) {
            console.log("+ got init data from scala", resp);
			ws.send(JSON.stringify({urn: "urn-product", state: "initdata", data: resp }));
        },
		function(resp) {
            console.log("- dont  got init data from scala", resp);
			ws.send(JSON.stringify({urn: rm.urn, state: "initdatafailed", ts: Math.round((new Date()).getTime() / 1000) }));
        }
    )

    /*
    var iid = setTimeout(function() {
        ws.send(JSON.stringify(process.memoryUsage()), function() {  });
        /// tcpclient.write("NODEJSSTAT: "+JSON.stringify(process.memoryUsage()));
    }, 500);
    */

    ws.on('message', function(message) {
		//console.log("WS ON M", message)
        var rm;
        try {
            rm = JSON.parse(message)
            //console.log(rm);
           	console.log("ACTION", rm.action);
            if (rm.action == 'create' || rm.action == 'update') // sync
            {
                //console.log("SYNC");
                var fname = "WAL/"+ rm.urn +"_new.json"
                fs.writeFile(fname, message, function(err) {
                    if(err)
                    {
                        console.log(err);
                    } else {
                        console.log("SAVED file: " + fname);
                        //ws.send(JSON.stringify({urn: rm.urn, state: "filesaved", ts: Math.round((new Date()).getTime() / 1000) }));
                    }
                });
				//tcpclient.write(message)
				remoteQuery(rm).then(
			        function(resp) {
			            console.log("+ (wss ws.onmessage/remotequery.then(resp))", resp);
						ws.send(JSON.stringify({urn: rm.urn, state: "synced", ts: Math.round((new Date()).getTime() / 1000) }));
			        },
					function(resp) {
			            console.log("- (wss ws.onmessage/remotequery.then(ERROR))", resp);
						ws.send(JSON.stringify({urn: rm.urn, state: "syncfailed", ts: Math.round((new Date()).getTime() / 1000) }));
			        }
			    )
				
            }
        }
        catch (e) {
            console.log('WSM ERROR', message, e);
        }
        /// ws.send(message.toUpperCase()); //, {mask: true}
        /// tcpclient.write("REROUTE: "+message);
    });
    /*
    ws.send('something', function(error) {
        // if error is null, the send has been completed,
        // otherwise the error object will indicate what failed.
    });
    */

    ws.on('open', function() {
        console.log('ON OPEN');
		ws.send(JSON.stringify({urn: "urn-server-2", state: "online", data: {no: "data"}}));
        //ws.send(Date.now().toString()); // , {mask: true}
    });

    ws.on('close', function() {
        console.log('on close');
        /// clearInterval(iid);
    });




});



