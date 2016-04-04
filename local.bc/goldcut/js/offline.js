/*
 OFFLINE MODE
 TO DISABLE offline we need to DELETE manifest file (old clients will see file and use offline cache), set <html lang="ru" manifest="NONEXISTENT.appcache"> and allow client to see it (1-7-30 days) and only then we can remove htmltag manifest=""
 to renew cache we need to fs touch manifest file
 fiiles need http cache too -- no-cache for dynamic, must-revalidate for manifest file itself
 */

window.addEventListener('load', function(e) {
    // statea: UNCACHED, IDLE, CHECKING, DOWNLOADING, UPDATEREADY, OBSOLETE
    window.applicationCache.addEventListener('updateready', function(e) {
        if (window.applicationCache.status == window.applicationCache.UPDATEREADY) {
            // Browser downloaded a new app cache.
            // Swap it in and reload the page to get the new hotness.
            window.applicationCache.swapCache();
            if (confirm('A new version of this site is available. Reload window and get it?')) {
                window.location.reload();
            }
        } else {
            // Manifest didn't changed. Nothing new to server.
        }
    }, false);

}, false);


//handleCacheEvent = noop;
handleCacheEvent = function(s){console.log(":STATE", s.type)};
appCache = window.applicationCache;
// on first load + cached. not fired on reload cache and next visits
appCache.addEventListener('cached', handleCacheEvent, false);
// First event
appCache.addEventListener('checking', handleCacheEvent, false);
// An update was found. The browser is fetching resources (pre Progress)
appCache.addEventListener('downloading', handleCacheEvent, false);
// usual ready state (checking, noupdate). Fired AFTER page.onload. state.online will be ready later
appCache.addEventListener('noupdate', handleCacheEvent, false);
appCache.addEventListener('noupdate', function(){ GC.state.online = true; }, false);
// if manifest was removed. not cache will be disabled & removed
appCache.addEventListener('obsolete', handleCacheEvent, false);
// on each res fetch
appCache.addEventListener('progress', handleCacheEvent, false);
// resources have been newly redownloaded if we have updated manifest file (checking, downloading, progress)
appCache.addEventListener('updateready', handleCacheEvent, false);
// The manifest returns 404 or 410, the download failed,
appCache.addEventListener('error', function(er){ console.log(er); console.log('WE ARE OFFLINE'); GC.state.online = false; }, false);



/*
 IS ONLINE
 VS BAD NET/REQUEST TIMEOUTS
 the ajax call timouts and you receive error
 the ajax call returns success, but the msg is null
 the ajax call is not executed because browser decides so (may be this is when navigator.onLine becomes false after a while)
 WS in online
 ajax poll (ws fallback) fail
 ?if we online again - check in x2 intervals - 1min, 2min, 4 min, 8 min etc
 */
/*
 Works well in Firefox and Opera with the
 Work Offline option in the File menu.
 Pulling the ethernet cable doesn't seem to trigger it.
 Later Google Chrome and Safari seem to trigger it well
 IE Works in IE with the Work Offline option in the  File menu and pulling the ethernet cable. document.body.ononline = isOnline; document.body.onoffline = isOffline;
 */
// if(!(navigator.onLine)) chrome. works in wifi disabled. navigator.onLine is State. online, offline - ebents on state change
window.addEventListener("online", function(ol){console.log("GOT ONLINE",ol); GC.state.online = true; }, false); // chrome. works on wifi enable. IF STATE CHANGED. not if we online from page load
window.addEventListener("offline", function(ol){console.log("GOT OFFLINE",ol); GC.state.online = false; }, false); // chrome. works on wifi disable

