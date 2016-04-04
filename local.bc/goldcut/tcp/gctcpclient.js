// doc http://nodejs.org/api/net.html

var net = require('net');
var when = require('../../lib/js/when/when.js');

//console.log(when)

var HOST = '127.0.0.1';
var PORT = 9900;

GC = {};
GC.REMOTEQUERYCALLBACKS = {};
GC.REMOTESERVER = connectToGoldcutServer(HOST, PORT);

function getRand(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function connectToGoldcutServer(HOST, PORT)
{
    var deferred = when.defer();

    var client = new net.Socket();
    client.setNoDelay(true);

    client.connect(PORT, HOST, function()
    {
        client.setNoDelay(true);
        console.log('CONNECTED TO GOLDCUT SERVER AT : ' + HOST + ':' + PORT);

        deferred.resolve(client);

    });

    var buf = '';
    var bufuse = 0;
    // data is what the server sent to this socket
    client.on('data', function(data) {
		console.log("<< "+data.toString())
        if (65536 == data.length)
        {
            buf += data;
            bufuse = 1;
        }
        else
        {
            buf += data;
            data = buf;
            buf = '';
            bufuse = 0;
        }
        if (bufuse == 0)
        {
            //console.log("ZERO: "+data.split("\0").length);
            //console.log('DATA LEN..: ' + data.length + ' ' + data.substring(0,42) + ' ... ' + data.substring((data.length-42)) );
            var chunks = data.split("\0");
            try {
                for (var i=0;i<chunks.length-1;i++)
                {
                    //console.log("X",chunks.length,chunks[i])
                    //if (chunks[i] == "") console.log("GOT ZERO");
                    //console.log(chunks[i]);
                    d = JSON.parse(chunks[i]);
					//console.log(d.seq, parseInt(d.seq), d.data)
                    onDataFromGoldcutServer(d.data, parseInt(d.seq));
                }
            }
            catch(e) {
                console.log(e)
            }
        }
        else
        {
            //console.log('BUF USE DATA LEN..: ' + data.length);
        }
    });

    client.on('close', function() {
        console.log('CONNECTION CLOSED BY REMOTE HOST');
        GC.REMOTESERVER = null
    });

    /*
    close connection by client - client.destroy();
    */

    return deferred.promise;
}





var onDataFromGoldcutServer = function(data, seq)
{
    //console.log(seq, data)
    //deferred.resolve(client);
    if (GC.REMOTEQUERYCALLBACKS[seq])
    {
        if (data.error)
            GC.REMOTEQUERYCALLBACKS[seq].reject(data)
        else
            GC.REMOTEQUERYCALLBACKS[seq].resolve(data)
    }
    else
        console.log("! NO CALLBACK FOR SEQ/DATA", seq, data);
}



function remoteQuery(q)
{
	//console.log(">R>", JSON.stringify(q));
    var deferred = when.defer();
    if (GC.REMOTESERVER === null)
    {
        console.log('Reopen Connection');
        GC.REMOTESERVER = connectToGoldcutServer(HOST, PORT);
        GC.REMOTESERVER.then(function(){
            remoteQuery(q).then(function(res){
                deferred.resolve(res);
            })
        });
    }
    else
    {
        GC.REMOTESERVER.then(
            function(client) {
                q.seq = getRand(1000,2147483647); // unique id of sent query
                GC.REMOTEQUERYCALLBACKS[q.seq] = deferred;

                console.log(">R> ", JSON.stringify(q));
                var qs;
                if (typeof(q) == 'object')
                    qs = JSON.stringify(q);
                else
                    qs = q;
                //console.log("i o to sent", qs);
                try {
                    client.write(qs+"\0");
                }
                catch (e)
                {
                    console.log("i e", e);
                    //GC.REMOTESERVER = connectToGoldcutServer(HOST, PORT)
                    //GC.REMOTESERVER.then(function(){remoteQuery(q)})
                }
            },
            function (er) {
                console.log("ERROR IN GC.REMOTESERVER", er)
            }
        )
    }
    return deferred.promise;
}


function serial(arr)
{
	var c = 0;
	var deferred = when.defer();
	var privserial = function(arr)
	{
		var fn = arr.shift();
		if (!fn) {
			deferred.resolve(c)
			return;
		}
		c++;
		var promise = fn.call()
		promise.then(function(fnres){
			privserial(arr)
		})
	}
	privserial(arr);
	return deferred.promise;
}

exports.remoteQuery = remoteQuery
exports.getRand = getRand
exports.serial = serial






