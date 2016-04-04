//require.paths.push(fs.workingDirectory+'/modules');

require.globals.thisIsMyGlobalFunction = function() {
  return 'hello'
}

phantom.onError = function (msg, stack) {
    var msg = "\nScript Error: "+msg+"\n";
    if (stack && stack.length) {
        msg += "       Stack:\n";
        stack.forEach(function(t) {
            msg += '         -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function + ')' : '')+"\n";
        })
    }
    console.error(msg+"\n");
}


var page = require("webpage").create();

var startTime;
page.onLoadStarted = function () {
    startTime = new Date()
};
page.onLoadFinished = function (status) {
    if (status == "success") {
        var endTime = new Date()
        console.log('The page is loaded in '+ ((endTime - startTime)/1000)+ " seconds" );
    }
    else
        console.log("The loading has failed");
};

page.open("http://slimerjs.org")
    .then(function(status){

      var mainTitle = page.evaluate(function () {
          console.log('message from the web page');
          return document.querySelector("h1").textContent;
      });

         if (status == "success") {
             console.log("The title of the page is: "+ page.title);
             console.log('First title of the page is ' + mainTitle);
             //
             //wait(milliseconds)
             //
             page.viewportSize = { width:1024, height:768 };
             page.render('screenshot.png')
             //
             //phantom.addCookie(cookie);
         }
         else {
             console.log("Sorry, the page is not loaded");
         }
         page.close();
         phantom.exit();
    })
