var page = require('webpage').create();
var system = require('system');
//
var phs = require('./phantomsys');
var scenario = require('./scenario');
var when = require('../../../lib/js/when/when.js');

setTimeout(function() { phs.exit(0); }, 1000);

page.settings.webSecurity = false;
page.viewportSize = {
  width: 1280,
  height: 800
};

var fname;
var scenario = null;

page.onNavigationRequested = function(url, type, willNavigate, mainframe) {
  console.log('>>> Navigate to: ' + url + ' ^ type' + type);
  lastUrl = url
}

page.onUrlChanged = function(targetUrl) {
  console.log('| NEW URL: ' + targetUrl);
};

page.onLoadStarted = function() {
  var currentUrl = page.evaluate(function() {
    return window.location.href;
  });
  console.log('< load started. ' + currentUrl + ' will gone...');
};

var lastStatusCode = undefined;
page.onResourceReceived = function(resource) {
  if (resource.url == lastUrl) {
    lastStatusCode = resource.status;
  }
};

page.onLoadFinished = function(status) {
  console.log('Status: ', status, lastStatusCode, '(on load finished)');
	scenario = new Scenario(page, lastStatusCode); // <<<<<<<<<<<<<<<<<<<<<<
	scenario.run()
};

// -----------------

var initialurl = "http://local.bc/test/ok.php"
lastUrl = initialurl

var allScenarios = [
	{enter: 'http://local.bc/test/ok.php', commands: [{click: 'a.linkok'}], exit: 'http://local.bc/test/ok.php'},
	{enter: 'http://local.bc/test/ok.php', commands: [{click: 'a.link503'}], exit: 'http://local.bc/test/503.php'}
]
var extendedScenarios = [
	{enter: 'http://local.bc/test/pageForm.php', commands: [{form: {email: 'test@test.com'}}, {click: '#sendform'}], exit: '#form2'}
	// {enter: '#form2', commands: [{form: {'name': 'Tester'}}, {click: 'a#toform3'}], exit: '#form3'},
	// {enter: '#form3', commands: [{form: {email: 'test@test.com'}}, {click: 'a#topage2'}], exit: 'http://local.bc/test/503.php'}
]

page.open(initialurl, function (status)
{
	// check for errors
	// get script for url
    if (status !== 'success') {
        console.log('Unable to load the address!');
        phs.exit(1);
    }

});
