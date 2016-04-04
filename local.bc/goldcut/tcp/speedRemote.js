var gtcpclient = require('./gctcpclient.js');
var remoteQuery = gtcpclient.remoteQuery;
var getRand = gtcpclient.getRand;

var onerror = function(er) { console.log("| !!! ", er) };
var randbase = getRand(10,90) * 1000;
var i=0;

var interval = setInterval(function(){
	
	var ib = randbase + i++;

	if (i >= 1000) {
		clearInterval(interval);
		return;
	}

    remoteQuery({action: 'create', urn: 'urn-product-'+ib, rankProduct: 1, title: "title"+ib, category: 42}).then(
        function(resp) {
            console.log("|< ", resp);
        },onerror
    )
}, 1)