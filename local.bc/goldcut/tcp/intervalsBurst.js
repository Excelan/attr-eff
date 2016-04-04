var gtcpclient = require('./gctcpclient.js');
var remoteQuery = gtcpclient.remoteQuery;
var getRand = gtcpclient.getRand;

var onerror = function(er) { console.log("RESP ER", er) };

//remoteQuery({action: 'load',urn: 'urn-category'}, function(resp) { console.log("RESP C", resp) });
//remoteQuery({action: 'load',urn: 'urn-product'}, function(resp) { console.log("RESP P1", resp) });
//remoteQuery({action: 'load',urn: 'urn-product', limit: 1}, function(resp) { console.log("RESP P2", resp) });

remoteQuery({action: 'create', urn: 'urn-product', rankProduct: 1, productTitle: "MyProd"+getRand(10,99)}).then(
    function(resp) {
        console.log("RESP", resp);

    },onerror
).then(
    function(resp)
    {
        // in resp data from prev request
        remoteQuery({action: 'load', urn: 'urn-product', last: 3, order: 'created desc'}).then(
            function(respin) {
                console.log("RESP INLINE", respin);
            },onerror
        );
    }
);


remoteQuery({action: 'load', urn: 'urn-somex', last: 1}).then(
    function(respin) {
        console.log("RESP INLINE", respin);
    },onerror
);


setInterval(function(){
    console.log("every");
    remoteQuery({action: 'create', urn: 'urn-product', rankProduct: 1, productTitle: "MyProd"+getRand(10,99)}).then(
        function(resp) {
            console.log("+", resp);
        },onerror
    )
}, 1 * 1000)


setInterval(function(){
    console.log("every");
    remoteQuery({action: 'load', urn: 'urn-product', last: 100}).then(
        function(resp) {
            console.log("<", resp);
        },onerror
    )
}, 5 * 1000)


setTimeout(function(){
    console.log("7 sec elapses");
    remoteQuery({action: 'load', urn: 'urn-product', last: 2}).then(
        function(respin) {
            console.log("RESP TIMED 2", respin);
        },onerror
    );
}, 7000)





/*
 // VARIANT 1 - chaining
 remoteQuery({}).then(cbok, cber).then(cbok, cber).then(cbok, cber)...
 */

/*
 // VARIANT 2 - inlining
 remoteQuery({}).then(function(data) {
 remoteQuery({data.some}).then(function(ok) {
 ...
 }, cber)
 }, cber)
 */