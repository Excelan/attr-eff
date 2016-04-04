var WebSocketServer = require('ws').Server
var wss = new WebSocketServer({ port: 8010 }); // logger service port
var wssbus = new WebSocketServer({ port: 9010 }); // log bus
console.log("LOGGER service, bus created")

// BUS
var wsbus;
wssbus.on("connection", function(ws) {
  wsbus = ws
  console.log("on bus connection")
});

// SERVICE
wss.on("connection", function(ws) {
  console.log("on service connection")
  ws.on('message', function (message) {
    console.log('log service received: %s', message);
    wsbus.send("!!! "+message); // RESEND log to controller
  });
  ws.on("close", function() {
    console.log("service websocket connection close")
  });
});
/*
wss.broadcast = function(data) {
  for (var i in this.clients)
  this.clients[i].send(data);
};
*/
