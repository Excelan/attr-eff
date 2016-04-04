// TODO Task N1 - reuse one TX for mult of CRUD commands
// TODO patch object (f1, f2, modified etc at once) vs patch sibgle key.path, kv


var onStorage = function(e)
{
    console.log('on store');
    console.log(e);
}
window.addEventListener("storage", onStorage, false);



window.indexedDB = window.indexedDB || window.webkitIndexedDB || window.mozIndexedDB || window.msIndexedDB;
if ('webkitIndexedDB' in window)
{
    window.IDBTransaction = window.webkitIDBTransaction;
    window.IDBKeyRange = window.webkitIDBKeyRange;
}

MYDB = {};
MYDB.onerror = function(e)
{
    console.log("Error");
    console.log(e);
};


var curuser = localStorage.getItem("GC.USER");
if (!curuser)
{
    curuser = GC.USER = getRandomArbitary(100000, 999000);
    localStorage.setItem("GC.USER", GC.USER);
    alert("Ваш код разведчика "+GC.USER);
}
else GC.USER = parseInt(curuser);




var readAll = function(db, store)
{
    var deferred = when.defer();
    var transaction = db.transaction([store], 'readonly');
    var objStore = transaction.objectStore(store);
    var cursorRequest = objStore.openCursor();
    var all = [];
    transaction.oncomplete = function(e) { deferred.resolve(all); };
    cursorRequest.onsuccess = function(e) {
        var result = e.target.result;
        if (!!result == false) { return; }
        all.push(result.value);
        result.continue();
    };
    cursorRequest.onerror = function () {
        deferred.reject(new Error('readAll failed'));
    };
    return deferred.promise;
};

var selectRangeByNonUniqKey = function(db, store, key, predicate)
{
    var deferred = when.defer();
    var transaction = db.transaction([store], 'readonly');
    var objStore = transaction.objectStore(store);
    //Match anything between "Bill" and "Donna", but not including "Donna"
    var index = objStore.index(key);
    var boundKeyRange = IDBKeyRange.bound(predicate.from, predicate.to, predicate.frominclude, predicate.toinclude);
    var cursorRequest = index.openCursor(boundKeyRange);
    var all = [];
    transaction.oncomplete = function(e) { deferred.resolve(all); };
    cursorRequest.onsuccess = function(e) {
        var result = e.target.result;
        if (!!result == false) { return; }
        all.push(result.value);
        result.continue();
    };
    cursorRequest.onerror = function () {
        deferred.reject(new Error('selectRangeByNonUniqKey failed'));
    };
    return deferred.promise;
};

var findByExactNonUniqKey = function(db, store, key, kv)
{
    var deferred = when.defer();
    //console.log('findByExactNonUniqKey', key);
    var tx = db.transaction([store], "readonly");
    var ostore = tx.objectStore(store);
    var index = ostore.index(key);
    var boundKeyRange = IDBKeyRange.only(kv);
    var cursorRequest = index.openCursor(boundKeyRange);
    var all = [];
    tx.oncomplete = function(e) { deferred.resolve(all); };
    cursorRequest.onsuccess = function(e) {
        var result = e.target.result;
        if (!!result == false) { return; }
        all.push(result.value);
        result.continue();
    };
    cursorRequest.onerror = function(e) {
        deferred.reject(new Error('findByExactNonUniqKey failed'));
    };
    return deferred.promise;
}

var loadByUniqKey = function(db, store, key, kv)
{
    var deferred = when.defer();
    //console.log('loadByUniqKey', key);
    var tx = db.transaction([store], "readonly");
    var ostore = tx.objectStore(store);
    var index = ostore.index(key);
    var request = index.get(kv); // if key is uniq or we get just first lower value
    request.onsuccess = function() {
        var matching = request.result;
        if (matching !== undefined) {
            console.log('loadByUniqKey FOUND', matching);
            deferred.resolve(matching);
        } else {
            console.log('loadByUniqKey NOT FOUND');
            deferred.resolve(null);
        }
    };
    request.onerror = function(e) {
        deferred.reject(new Error('loadByUniqKey failed'));
    };
    return deferred.promise;
}

var loadByPK = function(db, store, kv)
{
    var deferred = when.defer();
    //console.log('loadByPK', kv);
    var tx = db.transaction([store], "readonly");
    var ostore = tx.objectStore(store);
    var request = ostore.get(kv);
    request.onsuccess = function() {
        var matching = request.result;
        if (matching !== undefined) {
            deferred.resolve(matching);
        } else { // not found by pk
            deferred.resolve(null);
        }
    };
    request.onerror = function(e) {
        deferred.reject(new Error(request.error));
    };
    return deferred.promise;
}

var updateByPK = function(db, store, kv, selector, value)
{
    //
    var deferred = when.defer();
    //console.log('updateByPK', kv);
    var tx = db.transaction([store], "readwrite");
    var ostore = tx.objectStore(store);
    var loadrequest = ostore.get(kv);
    loadrequest.onsuccess = function()
    {
        var matching = loadrequest.result;
        if (matching !== undefined)
        {
            okv(matching, selector, value);
            okv(matching, 'modified', timestampNow());
            var request = ostore.put(matching);
            request.onsuccess = function(e) {
                // @ updated ok
                deferred.resolve(matching);
            };
            request.onerror = function () {
                deferred.reject(new Error(request.error));
            };
            request.onabort = function() {
                deferred.reject(new Error(request.error));
            };
        }
        else
        {
            console.log('FAIL updateByPK - PK NOT FOUND', kv);
            deferred.reject(new Error('Object for update not found'));
        }
    };
    loadrequest.onerror = function(e) {
        deferred.reject(new Error(loadrequest.error));
    };
    loadrequest.onabort = function() {
        // Otherwise the transaction will automatically abort due the failed request.
        console.log("Abort load pre update");
        deferred.reject(new Error(loadrequest.error));
    };
    return deferred.promise;
}

var create = function(db, store, obj)
{
    // add(error on PK same) vs put(can update)
    //console.log(store, obj);
    if (!obj) throw "CREATE UNDEFINED";
	var deferred = when.defer();
    var tx = db.transaction([store], 'readwrite');
    var ostore = tx.objectStore(store);
    var request = ostore.put(obj);
    // To determine if a transaction has completed successfully, listen to the transaction’s complete event rather than the success event of a particular request, because the transaction may still fail after the success event fires.
    request.onsuccess = function(e) {
        //console.log('create', 'onsuccess');
        // @
        deferred.resolve(obj);
    };
    request.onerror = function () {
        // The uniqueness constraint of the "some" index failed.
        // Could call request.preventDefault() to prevent the transaction from aborting.
        deferred.reject(new Error(request.error));
    };
    request.onabort = function() {
        // Otherwise the transaction will automatically abort due the failed request.
        console.log("Abort create");
         deferred.reject(new Error(request.error));
    };
    return deferred.promise;
};

var remove = function(db, store, kv)
{
	var deferred = when.defer();
    var trans = db.transaction([store], 'readwrite');
    var store = trans.objectStore(store);
    var request = store.delete(kv);
    request.onsuccess = function(e) {
        console.log('deleted',kv);
		deferred.resolve(kv)
    };
    request.onerror = function(e) {
        console.log("Error Deleting: ", e);
		deferred.reject(kv)
    };
	return deferred.promise;
};


// createDv, InitDb
var initDB = function(ver, stores) {
    var deferred = when.defer();

//    console.log('start initdb');
    var version = ver;
    var request = indexedDB.open('todotable', version);
    request.onblocked = function(event) {
        console.log('On Blocked');
    }
    request.onupgradeneeded = function(e)
    {
        console.log('Db Upgrade needed');

        var db = e.target.result;
        e.target.transaction.onerror = function () {
            deferred.reject(new Error('db upgrade failed'));
        };
        for (var i=0; i < stores.length; i++)
        {
            var store = stores[i];
            console.log('create', store);
            // delete exists
            if (db.objectStoreNames.contains(store))
            {
                db.deleteObjectStore(store);
                console.log('Non Blocked upgrade - table exists, recreate!');
            }
            // re/create table
            var ostore = db.createObjectStore(store, { keyPath: 'urn' });
            // create Indexes
            ostore.createIndex("modified", "modified", {unique: false});
            ostore.createIndex("created", "created", {unique: false});
            ostore.createIndex("needsync", "needsync", {unique: false});
            ostore.createIndex("lastsync", "lastsync", {unique: false});

			// TODO NON UNIVERSAL INDEXES
            if (store == 'product') ostore.createIndex("manufacturer", "manufacturer", {unique: false});

            // Initial data (we are already in transaction)
            //store.put({});
        }
    };
    // Handle successful datastore access.
    request.onsuccess = function(e) {
        // Get a reference to the DB.
//        console.log('open', 'onsuccess');
        db = e.target.result; // request.result;
        db.onversionchange = function(event) {
            db.close();
            console.log(event);
            alert("A new version of this page is ready. Please reload ALL TABS!");
        }
        // @
        GC.database = db;
        deferred.resolve(db);
    };
    request.onerror = function () {
        deferred.reject(new Error('db init failed'));
    };
    GC.database = deferred.promise;
    return deferred.promise;
};



var updateObjectInStore = function(urn, selector, value)
{
    var deferred = when.defer();
    var urn = new URN(urn);
    updateByPK(GC.database, urn.entity, urn.urn, selector, value).then(function(loaded)
    {
        deferred.resolve(loaded);
    });
    return deferred.promise;
}


var db;




// REMOTE DB SYNC
var replaceDataInIndexedDB = function(remoteall) // urn, state, data
{
	//return;
	
	
	
	console.log(":replaceDataInIndexedDB")
	if (remoteall.length == 0) {
		console.log("remote data blank, dont replace existent data")
	}
	var removePromises = [];
	var createPromises = [];
	// read local data
	readAll(GC.database, 'product').then(function(alls)
    {
		alls.forEach(function(o){ 
			removePromises.push( 
				remove(GC.database, 'product', o.urn) 
			)
		});
		// we need "all" promoses[] - функция перейдет в следующий then намного раньше того, 
		// как все локальные записи будут удалены (и мы начнем создавать новые раньше, чем удалены старые)
    }).then(function(){
		// после того, как все старые данные удалены (каждое удаление было отельным promise со своим then, 
		// then.all срабатывает когда все promise в массиве наступили)
		when.all(removePromises).then(function(){
			remoteall.data.forEach(function(o){ 
				o.needsync = 0;
				o.lastsync = timestampNow();
				//console.log(o)
				createPromises.push( 
					create(GC.database, 'product', o) 
				)
			});
		}).then(function(){ 
			// когда все promise на создание полученных с удаленного сервера записей исполнены, обновить UI
			when.all(createPromises).then(function(){ 
				console.log("Data recreated"); 
				listProducts(); 
				listManufacturers();
		 	}) 
		})
		
	}).then(function(){ 
		// здесь данные еще не удалены и еще не созданы!!!
	})
	
}


var sendToServer = function(o) {
	console.log("sendToServer", 0);
    try {
		// TODO CREATE NEW / UPDATE REMOTE
        o.action = "update"; //"sync";
        //ajax('/goldcut/upload/buffer.php', function(so){ if (so.status == 'ok') markAsSynced(o) }, {}, 'POST', o);
        GC.WS.send(JSON.stringify(o));
        //console.log('after send');
    }
    catch (e) {console.log(e)}
}

var markAsSynced = function(o) {
    console.log('+ SERVER SYNC REPLY (markAsSynced)',o);
    var urn = new URN(o.urn);
    updateByPK(GC.database, urn.entity, urn.urn, 'needsync', 0).then(function(){ updateByPK(GC.database, urn.entity, urn.urn, 'lastsync', timestampNow()) });
	//on ACK from server
    //updateByPK(GC.database, 'product', urn, 'lastsync', timestampNow());
	//remove synced
    //alls.map(function(i){ return {'urn': i.urn, 'modified':timestampNow()-i.modified, 'title': (i.title ? i.title.substr(0,10) : null) } }).forEach(function(et){ remove(GC.database,'product',et.urn) })
}

var markAsSyncFailed = function(o) {
    console.log('- SERVER SYNC REPLY (markAsSyncFailed)',o);
    var urn = new URN(o.urn);
    updateByPK(GC.database, urn.entity, urn.urn, 'needsync', 0).then(function(){ updateByPK(GC.database, urn.entity, urn.urn, 'lastsync', timestampNow()) });
}


// INTERVAL SYNC
// synced, ready to sync(offline with needsync: 1), drafts
// ?Two phase model - no drafts - sync immediate each new object, then place to wokflow (needsend: 1)

setInterval(function(){
// setTimeout(function(){

    //return;

    //readAll(GC.database, 'product').then(function(alls)
	findByExactNonUniqKey(GC.database, 'product', 'needsync', 1).then(function(alls)
    {
        if (alls.length > 0) console.log('needed sync products', alls.length);
        //console.log(alls.map(function(i){ return {'m': Math.round((timestampNow()-i.modified)/1000), 'title': (i.title ? i.title.substr(0,10) : null) } }));
		alls.forEach(sendToServer);
        //alls.forEach(markAsSynced);
    });

    findByExactNonUniqKey(GC.database, 'manufacturer', 'needsync', 1).then(function(alls)
    //readAll(GC.database, 'manufacturer').then(function(alls)
    {
        if (alls.length > 0) console.log('needed sync manufacturer', alls.length);
        alls.forEach(sendToServer);
    });


}, 2000);



