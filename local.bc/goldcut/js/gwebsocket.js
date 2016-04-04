function writeToScreen(message) {
    console.log(message);
}

var domain = window.location.href.split("/")[2];

var wsUri = "ws://"+domain+":8023/";
var output;

function init() {
    websocket = new WebSocket(wsUri);
    GC.WS = websocket;
    websocket.onopen = function (evt) {
        onOpen(evt)
    };
    websocket.onclose = function (evt) {
        onClose(evt)
    };
    websocket.onmessage = function (evt) {
        onMessage(evt)
    };
    websocket.onerror = function (evt) {
        onError(evt)
    };
}
function onOpen(evt) {
    writeToScreen("CONNECTED");
    GC.WS.send(JSON.stringify({action: "connect", user: "urn-user-"+GC.USER}));
}
function onClose(evt) {
    writeToScreen("DISCONNECTED");
}
function onMessage(evt) {
    var message = evt.data
    //writeToScreen('<span style="color: blue;">RESPONSE: ' + evt.data + '</span>');
    //doSend('ECHO '+evt.data);
    try {
        rm = JSON.parse(message)
        console.log("<WS<", rm);
		// TODO state > MQ names
		try {
        	if (rm.state == 'synced') markAsSynced(rm) // gcstorage.js
			else if (rm.state == 'syncfailed') markAsSyncFailed(rm)
			else if (rm.state == 'online') console.log("WELLCOME TO NODE WSS")
			else if (rm.state == 'initdata') replaceDataInIndexedDB(rm)
			else console.log("UNKNOWN .STATE = " + rm.state)
			if (!rm.state) console.log("NO .STATE")
		}
		catch (e) {
			console.log(e);
	    }
    }
    catch (e) {
        console.log('WS onMessage error on data: ', message);
		console.log(e);
    }
}
function onError(evt) {
    writeToScreen('ERROR: ' + evt.data);
}

window.addEventListener("load", init, false);