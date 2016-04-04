// All js fn compat per browser
// http://kangax.github.com/es5-compat-table/


/**
 var infiniteScrollPreloadOffsetOriginal = infiniteScrollPreloadOffset = 2000;
 // INSIDE NEWELEMENTS ADDER
 if (pager === false) scrolleventlock = true;
 else scrolleventlock = false;
 if (data.data.length < 10) infiniteScrollPreloadOffset = 500;
 else infiniteScrollPreloadOffset = infiniteScrollPreloadOffsetOriginal;
 scrolleventlock = false;
 */

/**

 http://dmitrysoshnikov.com/ecmascript/es5-chapter-1-properties-and-property-descriptors/

 when.defer() to create a deferred object that has a promise for a value that will become available at some point in the future.
 https://github.com/cujojs/when/wiki/Examples

 _underscore

 Queue, Stack etc
 http://dev.opera.com/articles/view/javascript-array-extras-in-detail/


 base: ajax, cookie, templates, id/css, get/set style
 form validate
 mobile is, ops
 referer, from g/ya callbacks
 With promises lib
 WS, polling
 match patch
 selection
 audio, canvas, graph, uploads
 innerHTML http://habrahabr.ru/post/31413/
 ms dyn create table http://msdn.microsoft.com/en-us/library/ms532998.aspx

 */


/*
 window.onscroll = function(ev) {
 if (scrolleventlock) return;
 var scrolldiff = document.body.offsetHeight - (window.innerHeight + window.scrollY);
 //console.log(scrolldiff);
 if (scrolldiff <= infiniteScrollPreloadOffset) {
 console.log('END OF PAGE', pager);
 scrolleventlock = true;
 // what to paginate?
 if (currentScreenName == 'tag') myInstagram.getTagMedia(tag, null, pager);
 }
 };
 */


/*
 var head = document.getElementsByTagName('head')[0],
 script = document.createElement('script');
 script.src = url;
 head.appendChild(script);
 In older browsers that don't support the async attribute, parser-inserted scripts block the parser..."
 IE 6 and 7 do this, only allowing one script to be downloaded at a time and nothing else. IE 8 and Safari 4 allow multiple scripts to download in parallel, but block any other resources
 <script async src="http://third-party.com/resource.js"></script>
 The browser support for it is Firefox 3.6+, IE 10+, Chrome 2+, Safari 5+, iOS 5+, Android 3+. No Opera support yet.
 (function(d, t) {
 var g = d.createElement(t),
 s = d.getElementsByTagName(t)[0];
 g.src = '//third-party.com/resource.js';
 s.parentNode.insertBefore(g, s);
 }(document, 'script'));
 <script type="text/javascript">
 (function(){
 var bsa = document.createElement('script');
 bsa.type = 'text/javascript';
 bsa.async = true;
 bsa.src = '//s3.buysellads.com/ac/bsa.js';
 (document.getElementsByTagName('head')[0]||document.getElementsByTagName('body')[0]).appendChild(bsa);
 })();
 </script>
 //url - protocol-relative URL. This is a darn useful way to load the script from either HTTP or HTTPS depending on the page that requested it
 The defer attribute makes a solemn promise to the browser. It states that your JavaScript does not contain any document.write or DOM modification nastiness:
 <script src="file.js" defer></script> IE 4+
 While all deferred scripts are guaranteed to run in sequence, it’s difficult to determine when that will occur. In theory, it should happen after the DOM has completely loaded, shortly before the DOMContentLoaded event. In practice, it depends on the OS and browser, whether the script is cached, and what other scripts are doing at the time.

 */
function loadModule(path, name, cb, cthis, params) {
    //console.log('loadModule',path, name);
    function async_load() {
        var path = this[0];
        var name = this[1];
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.async = true;
        s.src = '/js/' + path + '/' + name + '.js';
        s.onload = s.onreadystatechange = function (s) {
            //console.log(path, name, 'ONLOAD script');
            GC[path][name].init();
            if (cb)
                cb.apply(cthis, params);
        };
        var x = document.getElementsByTagName('script')[0];
        x.parentNode.insertBefore(s, x);
    }

    async_load.bind([path, name]).call(null, cb); //
}


function loadData(datahash, cb, cthis, cbparams) {
    if (typeof(datahash) == 'object') throw 'Load datahash is not string';
    if (typeof(datahash) == 'undefined') throw 'Load datahash is EMPTY';
    //console.log('LOAD DATA', datahash, cbparams);

    //var dataList = [ {href: '/url1', title: 'TITLE1', sub: 'subtitle1'}, {href: '/url2', title: 'TITLE2', sub: 'subtitle2'}, {href: '/url21', title: 'TITLE21', sub: 'subtitle21'} ];
    //var dataRead = { title: 'TITLECA'+cbparams, image: {src: '/path.jpg', alt: 'Alt', imgtitle: 'Image Title411' }  };

    // ASYNC LOAD
    var xmlhttp;
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    } else {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "/" + cbparams[1] + ".json", true);
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var remoteObject = JSON.parse(xmlhttp.responseText);
            //console.log(remoteObject);
            GC.cache[datahash] = remoteObject;
            cb.apply(cthis, cbparams);
        }
    }
    xmlhttp.send();
}


/**
 this.onload = function () {
 var many = 0;
 JSONP("test.php?callback", function (a, b, c) {
 this.document.body.innerHTML += [
 a, b, ++many, c
 ].join(" ") + "<br />";
 });
 JSONP("test.php?callback", function (a, b, c) {
 this.document.body.innerHTML += [
 a, b, ++many, c
 ].join(" ") + "<br />";
 });
 };
 */
var JSONP = function (global) {
    // (C) WebReflection Essential - Mit Style
    // 216 bytes minified + gzipped via Google Closure Compiler
    function JSONP(uri, callback) {
        function JSONPResponse() {
            try {
                delete global[src]
            } catch (e) {
                // kinda forgot < IE9 existed
                // thanks @jdalton for the catch
                global[src] = null
            }
            documentElement.removeChild(script);
            callback.apply(this, arguments);
        }

        var
            src = prefix + id++,
            script = document.createElement("script")
            ;
        global[src] = JSONPResponse;
        documentElement.insertBefore(
            script,
            documentElement.lastChild
        ).src = uri + "=" + src;
    }

    var
        id = 0,
        prefix = "__JSONP__",
        document = global.document,
        documentElement = document.documentElement
        ;
    return JSONP;
}(this);


// https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/forEach
// IE 9+ (ie 8 need this)
if (!Array.prototype.forEach) {
    Array.prototype.forEach = function (fn, scope) {
        for (var i = 0, len = this.length; i < len; ++i) {
            fn.call(scope, this[i], i, this);
        }
    }
}
function ArrayExtended() {
}
ArrayExtended.prototype = Object.create(Array.prototype);
ArrayExtended.prototype.max = function () {
    var max = this[0];
    var len = this.length;
    for (var i = 1; i < len; i++) if (this[i] > max) max = this[i];
    return max;
}
ArrayExtended.prototype.min = function () {
    var min = this[0];
    var len = this.length;
    for (var i = 1; i < len; i++) if (this[i] < min) min = this[i];
    return min;
}


Report = function (ismulty) {
    this.meta = {}
    if (ismulty instanceof Array)
        this.chart = []
    else
        this.chart = {}
}


// IE7 support for querySelectorAll (no support for ".class1, .class2")
if (!document.querySelectorAll) {
    (function (d) {
        d = document, a = d.styleSheets[0] || d.createStyleSheet();
        d.querySelectorAll = function (e) {
            a.addRule(e, 'f:b');
            for (var l = d.all, b = 0, c = [], f = l.length; b < f; b++)l[b].currentStyle.f && c.push(l[b]);
            a.removeRule(0);
            return c
        }
    })()
    document.querySelector = function (q) {
        return document.querySelectorAll(q)[0];
    }
}
// IE7 support for querySelectorAll. (has support for ".class1, .class2")
/**
 (function(d, s) {
 if (!document.querySelectorAll) {
 d=document, s=d.createStyleSheet();
 d.querySelectorAll = function(r, c, i, j, a) {
 a=d.all, c=[], r = r.replace(/\[for\b/gi, '[htmlFor').split(',');
 for (i=r.length; i--;) {
 s.addRule(r[i], 'k:v');
 for (j=a.length; j--;) a[j].currentStyle.k && c.push(a[j]);
 s.removeRule(0);
 }
 return c;
 }
 }
 })()
 */


/**
 // OLD
 function setStyle(el, style, value, units) {
 if (typeof el == 'object') x = el;
 if (typeof el == 'string') x = document.getElementById(el);
 if (units) value += units;
 x.style[style] = value;
 }

 function getStyleProp(el, styleProp) {
 if (!el) throw 'getStyleProp on no element';
 var x;
 if (typeof el == 'object') x = el;
 if (typeof el == 'string') x = document.getElementById(el);
 //if (window.getComputedStyle)
 var y = document.defaultView.getComputedStyle(x, null);
 console.log(y);
 var yy = y.getPropertyValue(styleProp);
 console.log(yy);
 //else console.log('no window.getComputedStyle');
 return yy;
 }
 */



// ie 7-8
/**
 if (typeof document.defaultView == 'undefined')
 {
 document.defaultView = {};
 }
 if (typeof document.defaultView.getComputedStyle == 'undefined')
 {
 document.defaultView.getComputedStyle = function(element, pseudoElement)
 {
 return element.currentStyle;
 }
 console.log(document.defaultView.getComputedStyle);
 }
 */
/**
 if (!window.getComputedStyle)
 {
 window.getComputedStyle = function(el, pseudo) {
 this.el = el;
 this.getPropertyValue = function(prop) {
 var re = /(\-([a-z]){1})/g;
 if (prop == 'float') prop = 'styleFloat';
 if (re.test(prop)) {
 prop = prop.replace(re, function () {
 return arguments[2].toUpperCase();
 });
 }
 return el.currentStyle[prop] ? el.currentStyle[prop] : null;
 }
 return this;
 }
 }
 */


/**
 var readyStateCheckInterval = setInterval(function() {
    if (document.readyState === "complete") {
        init3();
        clearInterval(readyStateCheckInterval);
    }
}, 10);
 */

//EQWC();
//EQWC2();


/**
 // ie dom load simplest - move the code to the bottom of the page instead of using DOMReady event
 // ie6
 var readyStateCheckInterval = setInterval(function() {
 if (document.readyState === "complete") {
 init3();
 clearInterval(readyStateCheckInterval);
 }
 }, 10);

 // ie7 dom load
 if (document.all && !window.opera){ //Crude test for IE
 //Define a "blank" external JavaScript tag
 document.write('<script type="text/javascript" id="contentloadtag" defer="defer" src="javascript:void(0)"><\/script>')
 var contentloadtag=document.getElementById("contentloadtag")
 contentloadtag.onreadystatechange=function(){
 if (this.readyState=="complete")
 walkmydog()
 }
 }
 */

