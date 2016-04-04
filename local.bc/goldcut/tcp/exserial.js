var serial = require('./gctcpclient.js').serial;
var when = require('../../lib/js/when/when.js');

var f1 = function(){
	console.log("a");
	var deferred = when.defer();
	setTimeout(function(){
	    console.log(0+1);
		deferred.resolve(1);
	}, 1 * 1500)
	return deferred.promise;
}

var f2 = function(){
	console.log("b");
	var deferred = when.defer();
	setTimeout(function(){
	    console.log(0+2);
		deferred.resolve(2);
	}, 1 * 1000)
	return deferred.promise;
}

var f3 = function(){
	console.log("c");
	var deferred = when.defer();
	setTimeout(function(){
	    console.log(0+3);
		deferred.resolve(3);
	}, 1 * 2050)
	return deferred.promise;
}

var f4 = function(){
	console.log("d");
	var deferred = when.defer();
	setTimeout(function(){
	    console.log(0+4);
		deferred.resolve(4);
	}, 1 * 500)
	return deferred.promise;
}

serial([f1,f2,f3, f4])