var gtcpclient = require('./gctcpclient.js');
var remoteQuery = gtcpclient.remoteQuery;
var getRand = gtcpclient.getRand;

var onerror = function(er) { console.log("| !!! ", er) };

remoteQuery({action: 'select', urn: 'urn-product'}).then(
    function(resp) {
        console.log("<<<< ", resp);
        //console.log("<<<< ", resp.length);
    }, onerror
);
