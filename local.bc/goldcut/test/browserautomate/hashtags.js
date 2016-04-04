var system = require('system');

var webpage = require('webpage').create();
webpage.viewportSize = { width: 1280, height: 800 };
webpage.scrollPosition = { top: 0, left: 0 };

var userid = system.args[1];
var profileUrl = "http://www.twitter.com/" + userid;

webpage.open(profileUrl, function(status) {
 if (status === 'fail') {
  console.error('webpage did not open successfully');
  phantom.exit(1);
 }
 var i = 0,
 top,
 queryFn = function() {
  return document.body.scrollHeight;
 };
 setInterval(function() {
  top = webpage.evaluate(queryFn);
  i++;

  webpage.scrollPosition = { top: top + 1, left: 0 };

  if (i >= 5) {
   var twitter = webpage.evaluate(function () {
    var twitter = [];
    forEach = Array.prototype.forEach;
    var tweets = document.querySelectorAll('[data-query-source="hashtag_click"]');
    forEach.call(tweets, function(el) {
     twitter.push(el.innerText);
    });
    return twitter;
   });

   twitter.forEach(function(t) {
    console.log(t);
   });

   phantom.exit();
  }
}, 3000);
});
