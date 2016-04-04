// url1 >> url2 >> url2#some >> url3
// current scenario - success complete (awaited url callback | scenario last action done) \ catch exceptions
// wait event

// init page, check for http errors
// chain start/finish scenarios
function Coordinator(onpage, statuscode)
{
	// start/chain new scenario on 1 new url 2 new hash
	//
    if (statuscode !== 200)
	{
        console.log('Scenario fail for', onpage.url, statuscode);
		this.error = true
    }
	else
	{
		this.error = false
		this.onpage = onpage
		this.onpage.injectJs('phjsinject.js');
	}
	this.url = onpage.url
}
Coordinator.prototype.notify = function(){}


function Scenario(onpage, statuscode)
{
    if (statuscode !== 200)
	{
        console.log('Scenario fail for', onpage.url, statuscode);
		this.error = true
    }
	else
	{
		this.error = false
		this.onpage = onpage
		this.onpage.injectJs('phjsinject.js'); // INJECT
	}
	this.url = onpage.url
}
Scenario.prototype.screenshot = function(){}
Scenario.prototype.onNavigationRequested = function(){}
Scenario.prototype.onAjaxReceived = function(){
	// onResource vs gcNotify
	// from ajax returned o.redirect url. Check before redirect?
}
Scenario.prototype.onUrlHashChanged = function(){}
Scenario.prototype.onFragmentPresented = function(){}
Scenario.prototype.run = function(){
	// actions: click, form fill, wait for fragment presented,
	console.log("$$$ RUN (try click a.link503 on page)", this.url)
	if (this.error) {
		console.log("$$$ CANCEL", statuscode)
		return;
	}
    //page.uploadFile('input[name=image]', fname);
    this.onpage.evaluate(function () {
        //document.querySelector('input[name=nickname]').value = 'phantom';
        var a = document.querySelector('a.link503');
		//console.log(a);
		mouseclick(a);
		//document.querySelector('form').submit();
    });
}
