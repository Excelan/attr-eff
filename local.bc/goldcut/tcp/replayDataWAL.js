var when = require('../../lib/js/when/when.js');
var gtcpclient = require('./gctcpclient.js');
var URN = require('./urn.js').URN;
var serial = require('./gctcpclient.js').serial;
var remoteQuery = gtcpclient.remoteQuery;
var getRand = gtcpclient.getRand;

var onerror = function(er) { console.log("| !!! ", er) };
var randbase = getRand(10,90) * 1000;
var i=0;

var processMessagesReplay = function(rea) {
    tourResolvers = []
	rea = rea.map(function(m) { 
		m.action = "create";
        // js time in milliseconds to seconds unix ts
		if (m.created > 2147483647) m.created = Math.round(m.created/1000); 
		if (m.modified > 2147483647) m.modified = Math.round(m.modified/1000);

        // legacy php (scala uses simple int ids and urn-entity-id vs id)
        if (m.user) m.user = "urn-user-" + m.user
        if (m.manufacturer) m.manufacturer = "urn-manufacturer-" + m.manufacturer
        var urn = new URN(m.urn);
        m.id = urn.uuid;
        // legacy

		return m; 
	});
	rea.forEach(function(m){
		//console.log(m)
        tourResolvers.push( function() {
            var deferred = when.defer();
            remoteQuery(m).then(
                function(resp) {
                    console.log("|< ", resp);
                    deferred.resolve(resp);
                },onerror
            )
            return deferred.promise;
        } )

	});

    serial(tourResolvers).then(function(serialcount){
        console.log("serial then", serialcount)
    })
}


var fs = require('fs');
var path = require("path");

var DIR = './websocket/WAL/'

var rea = []

fs.readdir(DIR, function (err, files) {
    if (err) throw err;
    files.map(function (file) {
        return path.join(DIR, file);
    }).filter(function (file) {
        return fs.statSync(file).isFile();
    }).forEach(function (file) {
        //console.log("%s (%s)", file, path.extname(file));
		rea.push(JSON.parse(fs.readFileSync(file, 'utf8')))
    });
	processMessagesReplay(rea)
});


/*
//var interval = setInterval(function(){
var interval = setTimeout(function(){
	
	var ib = randbase + i++;

	if (i >= 1000) {
		clearInterval(interval);
		return;
	}

    remoteQuery({action: 'create', urn: 'urn-product-'+ib, rankProduct: 1, title: "title"+ib, category: 42, "illustration.image": "IMAGE", "illustration.title": "IMGTITLE"}).then(
        function(resp) {
            console.log("|< ", resp);
        },onerror
    )
}, 10000)
*/

/*
fs.readdir(DIR, function (err, files) { 
  if (err) throw err;
   files.forEach( function (file) {
	console.log(file)
     fs.lstat(DIR+file, function(err, stats) {
       if (!err && stats.isDirectory()) {
         console.log("D"+file)
       }
       else{
        console.log(file)
      }
     });
   });
});
*/

/*
remoteQuery({action: 'select',urn: 'urn-product'}).then(
    function(resp) {
        console.log("<<<< ", resp);

    }, onerror
);
*/

//rand = getRand(1000,90000000)

/*
for (var i = 1; i <= 2000; i++)
	//ib = 11000 + i;
	remoteQuery({action: 'create',urn: 'urn-product-'+i, title: "ProdNODEJS"}).then(
	    function(resp) {
	        console.log("<<<< ", resp);
	    }, onerror
	)
*/