var serial = require('./gctcpclient.js').serial;
var when = require('../../lib/js/when/when.js');

var getTour = function(){
	number = this.number
	var deferred = when.defer();
	setTimeout(function(){
	    console.log(number);
		deferred.resolve(number);
	}, number * 100)
	return deferred.promise;
}

tourResolvers = []
for(var i=1;i<=5;i++)
	tourResolvers.push(getTour.bind({number: i}))

serial(tourResolvers).then(function(serialcount){
	console.log("serial then", serialcount)
})

console.log("next line after serial")