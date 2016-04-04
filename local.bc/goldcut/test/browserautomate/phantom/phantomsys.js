
function exit(code) {
    if (page) page.close();
    setTimeout(function(){ phantom.exit(code); }, 0);
    phantom.onError = function(){};
    throw new Error('');
}


function serial(arr)
{
	var donecount = 0;
	var failedcount = 0;
	var deferred = when.defer();
	var privserial = function(arr)
	{
		var fn = arr.shift();
		if (!fn) {
			//console.log("PRE ALL RESOLVE");
			deferred.resolve({'resolved':donecount,'rejected':failedcount})
			//console.log("POST ALL RESOLVE");
			return;
		}
		var promise = fn.call()
		promise.then(
			function(fnres){
				donecount++;
				//console.log("NEXT OK");
				privserial(arr)
			},
			function(fnerr){
				failedcount++;
				//console.log("NEXT ERR");
				privserial(arr)
			}
		)
	}
	privserial(arr);
	return deferred.promise;
}


module.exports = {
    serial: serial,
    exit: exit
};



if (!Function.prototype.bind) {
  Function.prototype.bind = function(oThis) {
    if (typeof this !== 'function') {
      // closest thing possible to the ECMAScript 5
      // internal IsCallable function
      throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
    }

    var aArgs   = Array.prototype.slice.call(arguments, 1),
        fToBind = this,
        fNOP    = function() {},
        fBound  = function() {
          return fToBind.apply(this instanceof fNOP && oThis
                 ? this
                 : oThis,
                 aArgs.concat(Array.prototype.slice.call(arguments)));
        };

    fNOP.prototype = this.prototype;
    fBound.prototype = new fNOP();

    return fBound;
  };
}



/*

var useful = require('./phantomsys');
useful.a();
useful.b();



//settings apply only during the initial call to the page.open function. Subsequent modification of the settings object will not have any impact
page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';
javascriptEnabled
loadImages
resourceTimeout, onResourceTimeout

.close() Close the page and releases the memory heap associated with it. Do not use the page instance after calling this.
evaluateJavaScript(str) vs evaluate(function, arg1, arg2, ...) {object}

page.sendEvent('keypress', page.event.key.A, null, null, 0x02000000 | 0x08000000);
*/
