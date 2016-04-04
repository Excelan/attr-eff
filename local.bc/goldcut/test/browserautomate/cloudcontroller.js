// CENTRAL CONTROLLER

var WebSocketServer = require('ws').Server;
var WebSocket = require('ws');
var wss = new WebSocketServer({ port: 8000 }); // cloud controller port

// TODO Commands. on page 1 | on page 2 union

ws = new WebSocket('ws://localhost:9010/'); // log bus
ws.on('open', function() {

});
ws.on('message', function fromlogger(message) {
    // TODO check for waited
    // TODO resolve Promise
});

function remoteAction(ws, cmd)
{
  //
}

console.log("CLOUD CONTROLLER websocket server created")
wss.on("connection", function(ws)
{
  console.log("cloud controller on connection")

  // foreach command set
    // register in log server awaited state (and buffer all log records now)
    // send [command] to connection with browser
    // wait for state message from log server

  ws.on('message', function frombrowser(message) {
    // TODO ready? > run command
    var cmd = {gate: '/login', message: {email: 'max@attracti.com', password: '123'}};
    var waitfor = {state: 'loggedin', role: 'maxpost'}

    remoteAction(ws, cmd)
    // TODO Promise direct
    // TODO Promise after page load
    //ws.send(JSON.stringify(cmd));
  });

  ws.on("close", function() {
    console.log("cloud controller websocket connection close")
  })
})
