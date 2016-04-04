// BROWSER CONTROOLER
GC.ONLOAD.push(function(e)
{
  // TODO Command (Fn(params) with Promise). run chain of Commands.

  if (!GC.CLOUD || !GC.CLOUD['cloudlog']) return;

  var socket = new WebSocket("ws://"+document.location.hostname+":8000/"); // cloud controller port

  socket.onopen = function() {
    console.log("Соединение установлено.");
    socket.send("Привет");
  };

  socket.onclose = function(event) {
    if (event.wasClean) {
      console.log('Соединение закрыто чисто');
    } else {
      console.log('Обрыв соединения');
    }
    console.log('Код: ' + event.code + ' причина: ' + event.reason);
  };

  socket.onmessage = function(event) {
    console.log("Получены данные от central controller" + event.data);
  };

  socket.onerror = function(error) {
    console.log("Ошибка " + error.message);
  };



});
