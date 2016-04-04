var GC = {};
GC.state = {};
GC.state.online = undefined;
GC.cache = {};
GC.database = null;
GC.SCREEN = {};
GC.CALLBACKS = {};
GC.ONLOAD = [];
GC.fn = {};

// -- AJAX
/**
 processor(json, returnControlTo) - recieved data processor
 onStart - fn runned before request
 onDone - runnded on ajax recieved reply but before JSON parse
 returnControlTo - callback passed to processor which will be runned by processor on done
 http://habrahabr.ru/post/120917/ XHR2, FormData
 http://dev.opera.com/articles/view/xhr2/
 Content-type text/xml или application/octet-stream Ю $HTTP_RAW_POST_DATA

 OPTIONS before POST - if POST is used to send request data with a Content-Type other than application/x-www-form-urlencoded, multipart/form-data, or text/plain, e.g. if the POST request sends an XML payload to the server using application/xml or text/xml, then the request is preflighted. setRequestHeader('Content-Type', 'application/xml');  <?xml version="1.0"?><person><name>Arun</name></person>
 Access-Control-Max-Age gives the value in seconds for how long the response to the preflight request can be cached for without sending another preflight request
 Important note: when responding to a credentialed request,  server must specify a domain, and cannot use wild carding
 */
function ajax(url, dataProcessor, opts, method, params) {
    var deferred = when.defer();
    if (!opts) opts = {};
    if (opts['onStart']) opts['onStart']();
    var xhr;
    if (window.ActiveXObject)
        xhr = new ActiveXObject("Microsoft.XMLHTTP"); // IE 5!!, 6!
    else if (window.XMLHttpRequest)
        xhr = new XMLHttpRequest();
    else
        alert("AJAX not supported");
    if (!method) method = "GET";
    if (method == 'GET') url = makeHREF(url, params);
    var qs = null;
    if (method == 'POST') qs = makeQS(params);
    xhr.open(method, url, true);
    if (opts['withCredentials']) xhr.withCredentials = true; // send cookies
    if (opts['noCache']) {
        xhr.setRequestHeader("Cache-Control", "no-cache");
        xhr.setRequestHeader("Pragma", "no-cache");
    }
    if (method == 'POST') xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8"); // ; charset=UTF-8
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                if (xhr.responseText != null) {
                    //console.log(xhr.responseText);	// console.log(xhr.responseXML);
                    //if (!xhr.responseText) throw 'no xhr.responseText';
                    //if (!xhr.responseXML) throw 'no xhr.responseXML';
                    if (opts['onDone']) opts['onDone']();
                    if (opts['responseType'] == 'XML') {
                        var jsObject = xhr.responseXML;
                    }
                    else if (opts['responseType'] == 'plain') {
                        var jsObject = xhr.responseText;
                    }
                    else {
                        try {
                            var jsObject = JSON.parse(xhr.responseText);
                        }
                        catch (e) {
                            console.log('Cant parse JSON (exception):', e);
                            console.log("Text in response:", xhr.responseText);
                            //if (window['printStackTrace']) console.log(printStackTrace());
                            if (opts['onError']) opts['onError'](0, 'JSON PARSE ERROR ' + xhr.responseText);
                        }
                    }
                    var returnControlTo = opts['returnControlTo'] ? opts['returnControlTo'] : null;
                    dataProcessor(jsObject, returnControlTo); // check dataProcessor is FN
                    deferred.resolve(jsObject);
                }
                else
                    return false;
            }
            else {
                if (opts['onError']) {
                    var errorMessage;
                    try {
                        var jsObject = JSON.parse(xhr.responseText);
                        errorMessage = jsObject.text;
                    }
                    catch (e) {
                        errorMessage = xhr.responseText;
                    }
                    opts['onError'](xhr.status, errorMessage);
                }
                else console.log("No opts['onError']. Error code: " + xhr.status + ", error: " + xhr.statusText);
            }
        }
    }
    xhr.send(qs);
    return deferred.promise;
}


// -- DOM PATH

function DomPath(domel) {
    this.el = domel;
    this.init = function (el) {
        var max = 20, i = 0;
        var uppath = [];
        var p = el;
        uppath.push({'tag': p.tagName, 'id': p.id, 'classes': p.className.split(' '), 'dom': p});
        while (p.parentNode && p.parentNode.tagName) {
            i++;
            if (i == max) break;
            //console.log(p.tagName, p.id, p.className);
            p = p.parentNode;
            uppath.push({'tag': p.tagName, 'id': p.id, 'classes': p.className.split(' '), 'dom': p});
        }
        //console.log(uppath);
        this.dompath = uppath;
    };
    this.init(this.el);
    this.internalFunc = function () {
    };
    this.checkTagIdClass = function (elO, of) {
        if (typeof(of) == "undefined") {
            return false;
        }

        var hf1 = true, hf2 = true, hf3 = true, testForDataAttrib = true;

        if (typeof(of.tag) != "undefined") {
            //console.log(elO, of);
            if (elO.tag != of.tag.toUpperCase())
                hf1 = false;
        }

        if (typeof(of.id) != "undefined") {
            if (elO.id != of.id)
                hf2 = false;
        }

        //IE 8 error on .class
        if (typeof(of["class"]) != "undefined") {
            //console.log(of.class, elO.classes.indexOf(of.class));
            if (elO.classes.indexOf(of["class"]) >= 0)
                hf3 = true;
            else
                hf3 = false;
        }

        if (typeof(of.hasdataattrib) != "undefined") {
            //console.log(elO);
            if (!elO.dom.getAttribute(of.hasdataattrib))
                testForDataAttrib = false;
        }

        //console.log(hf1, hf2, hf3);
        if (hf1 && hf2 && hf3 && testForDataAttrib)
            return true;
        else
            return false;
    }
}
// is element of {tag, id, class} inside element of {tag, id, class} (ex: <a in <nav)
DomPath.prototype.testNodesOnPath = function (t, of) {
    var hasT = false, hasOf = false;
    for (var i = 0; i < this.dompath.length; i++) {
        //if (this.dompath[i].tag == t.tag.toUpperCase()) hasT = true;
        if (!hasT && this.checkTagIdClass(this.dompath[i], t)) hasT = true;
        if (hasT) hasOf = this.checkTagIdClass(this.dompath[i], of);
        //console.log(this.dompath[i], this.checkTagIdClass(this.dompath[i], t), hasT, hasOf, 1);
        if (hasT && hasOf) return true;
    }
    //console.log('testNodesOnPath', t, of, hasT, hasOf, 2);
    //if (hasT && hasOf) return true;
    //else
    return false;
};
// param: {tag, id, class}
DomPath.prototype.getNodeBy = function (param) {
    for (var i = 0; i < this.dompath.length; i++) {
        //console.log('+', this.dompath[i], param);
        if (this.checkTagIdClass(this.dompath[i], param)) return this.dompath[i].dom; //console.log('OK'); //
        //console.log(this.checkTagIdClass(this.dompath[i], param));
    }
};
DomPath2 = DomPath;

// -- SELECTORS

// TODO hasclass(ARRAY of classes (and, or))
function filterTags(nodeList, filter) {
    var filtered = [].filter.call(nodeList, function (node) {
        return node.nodeType == 1 && node.tagName.toLowerCase() == filter.tag && hasClass(node, filter["class"]);
        // return element.parentNode == filter.parent;
    });
    return filtered;
    /*
     [].filter.call(ul.querySelectorAll("li"), function(element){
     return element.parentNode == ul;
     });
     [].filter.call(ul.childNodes, function(node) {
     return node.nodeType == 1 && node.tagName.toLowerCase() == 'li';
     });*/
}

function eventCoord(e) {
    // Crossplatform offsetX/Y. IE, Fx, Opera, Safari tested
    if (!e) e = event;
    if (e.target) targ = e.target;
    else if (e.srcElement) targ = e.srcElement;
    if (targ.nodeType == 3) /* defeat Safari bug */ targ = targ.parentNode;
    if (e.pageX == null) { /* IE case */
        var d = (document.documentElement && document.documentElement.scrollLeft != null) ?
            document.documentElement : document.body;
        ex = e.clientX + d.scrollLeft;
        ey = e.clientY + d.scrollTop;
    } else {
        ex = e.pageX;
        ey = e.pageY;
    }
    if (targ.offsetParent) {
        do {
            ex -= targ.offsetLeft;
            ey -= targ.offsetTop;
        } while (targ = targ.offsetParent);
    }
    return {x: ex, y: ey};
}

function makeQS(arr) {
    var s = "";
    for (var e in arr) {
        s += "&" + e + "=" + encodeURIComponent(arr[e]); // JSON.stringify(
    }
    return s.substring(1);
}

function makeHREF(url, arr) {
    if (arr)
        return url + "?" + makeQS(arr);
    else return url;
}

// -- node values

function prependChildToParent(newChild, parent) {
    parent.insertBefore(newChild, parent.firstChild);
}

function nodeSetText(wn, value) {
    if (typeof(wn.innerText) != 'undefined')
        wn.innerText = value;
    else
        wn.textContent = value;
}
function setText(o, t) {
    if (!o) return 'no DomEl for set text:' + t;
    if (typeof(o.innerText) != 'undefined') {
        o.innerText = t;
    } else {
        o.textContent = t;
    }
}
function getText(o, t) {
    if (typeof(o.innerText) != 'undefined') {
        return o.innerText;
    } else {
        return o.textContent;
    }
}
function getElemText(node) {
    return node.text || node.textContent || (function (node) {
            var _result = "";
            if (node == null) {
                return _result;
            }
            var childrens = node.childNodes;
            var i = 0;
            while (i < childrens.length) {
                var child = childrens.item(i);
                switch (child.nodeType) {
                    case 5: // ENTITY_REFERENCE_NODE
                        _result += arguments.callee(child);
                        break;
                    case 4: // CDATA_SECTION_NODE
                        _result += child.nodeValue;
                        break;
                }
                i++;
            }
            return _result;
        }(node));
}

// dom class, dim, scroll

function unsetClassBelowContextPath(context, className) {
    var childs = context.querySelectorAll('.' + className);
    for (var i = 0; i < childs.length; i++) {
        var c = childs[i];
        removeClass(c, className);
    }
}


var util = {

    // Finds the absolute position of an element on a page
    findPos: function (obj) {
        var curleft = curtop = 0;
        if (obj.offsetParent) {
            do {
                curleft += obj.offsetLeft;
                curtop += obj.offsetTop;
            } while (obj = obj.offsetParent);
        }
        return [curleft, curtop];
    },

    // Finds the scroll position of a page
    getPageScroll: function () {
        var xScroll, yScroll;
        if (self.pageYOffset) {
            yScroll = self.pageYOffset;
            xScroll = self.pageXOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {
            yScroll = document.documentElement.scrollTop;
            xScroll = document.documentElement.scrollLeft;
        } else if (document.body) {// all other Explorers
            yScroll = document.body.scrollTop;
            xScroll = document.body.scrollLeft;
        }
        return [xScroll, yScroll]
    },

    // Finds the position of an element relative to the viewport.
    findPosRelativeToViewport: function (obj) {
        var objPos = this.findPos(obj)
        var scroll = this.getPageScroll()
        return [objPos[0] - scroll[0], objPos[1] - scroll[1]]
    }

}

getElementWidth = function (el) {
    if (typeof el.clip !== "undefined") {
        return el.clip.width;
    } else {
        if (el.style.pixelWidth) {
            return el.style.pixelWidth;
        } else {
            return el.offsetWidth;
        }
    }
};

getElementHeight = function (el) {
    if (typeof el.clip !== "undefined") {
        return el.clip.height;
    } else {
        if (el.style.pixelHeight) {
            return el.style.pixelHeight;
        } else {
            return el.offsetHeight;
        }
    }
};

function getDocHeight() {
    var D = document;
    return Math.max(
        Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
        Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
        Math.max(D.body.clientHeight, D.documentElement.clientHeight)
    );
}

function getViewportSize() {
    var w = window, d = document, e = d.documentElement, g = d.getElementsByTagName('body')[0], x = w.innerWidth || e.clientWidth || g.clientWidth, y = w.innerHeight || e.clientHeight || g.clientHeight;
    return {x: x, y: y};
}
function getActualViewportSize() {
    var w = window, d = document, e = d.documentElement, g = d.getElementsByTagName('body')[0], x = w.innerWidth || e.clientWidth || g.clientWidth, y = w.innerHeight || e.clientHeight || g.clientHeight;
    return {x: g.clientWidth, y: g.clientHeight};
}

function id(sid) {
    return document.getElementById(sid);
}

// by default All or First?
function bycss(selector, nth, ctxEl) {
    // if ctxEl && nonlegacybrowser - use ctxEl.querySelectorAll
    if (!ctxEl) ctxEl = document;
    if (nth == 1)
        return ctxEl.querySelector(selector);
    else if (nth > 1)
        return ctxEl.querySelectorAll(selector)[--nth];
    else if (is_array(nth)) {
        // TODO
        // bycss(".sliderImgItem",[2,5],ctx); from nth 2 to 5
    }
    else
        return ctxEl.querySelectorAll(selector);
}

function addClass(el, cls) {
    // r.classList.add
    if (el === null) throw 'addClass() on null: ' + el + ' ' + cls;
    if (typeof(el) == 'undefined') throw 'addClass() on undefined element';
    var c = el.className.split(' ');
    for (var i = 0; i < c.length; i++) {
        if (c[i] == cls) return;
    }
    c.push(cls);
    el.className = c.join(' ');
}

function removeClass(el, cls) {
    try {
        var c = el.className.split(' ');
        for (var i = 0; i < c.length; i++) {
            if (c[i] == cls) c.splice(i--, 1);
        }
        el.className = c.join(' ');
    }
    catch (e) {
        console.log('DOM element not found', el, cls);
        console.log(e);
    }
}

function hasClass(el, cls) {
    if (el === null) {
        console.log(printStackTrace());
        throw 'hasClass() on null: ' + el + ' ' + cls;
    }
    if (typeof(el) == 'undefined') throw 'hasClass() on undefined element';
    if (typeof(el.className) == 'undefined') return false;
    for (var c = el.className.split(' '), i = c.length - 1; i >= 0; i--) {
        if (c[i] == cls) return true;
    }
    return false;
}

function toggleClass(btn, cls) {
    if (!hasClass(btn, cls))
        addClass(btn, cls);
    else
        removeClass(btn, cls);
}

function getStyle(el, style) {
    //if(!document.getElementById) return;
    if (!el) {
        console.log(printStackTrace());
        throw 'No el for setstyle';
    }
    var value = el.style[toCamelCase(style)];
    if (!value)
        if (document.defaultView)
            value = document.defaultView.
                getComputedStyle(el, "").getPropertyValue(style);
        else if (el.currentStyle)
            value = el.currentStyle[toCamelCase(style)];
    return value;
}

function setStyle(el, style, value, units) {
    if (!el) {
        console.log(printStackTrace());
        throw 'No el for setstyle';
    }
    if (typeof el == 'object') x = el;
    if (typeof el == 'string') x = document.getElementById(el);
    if (units) value += units;
    x.style[toCamelCase(style)] = value;
}

function toCamelCase(sInput) {
    var oStringList = sInput.split('-');
    if (oStringList.length == 1)
        return oStringList[0];
    var ret = sInput.indexOf("-") == 0 ?
    oStringList[0].charAt(0).toUpperCase() + oStringList[0].substring(1) : oStringList[0];
    for (var i = 1, len = oStringList.length; i < len; i++) {
        var s = oStringList[i];
        ret += s.charAt(0).toUpperCase() + s.substring(1)
    }
    return ret;
}

function getStyleProp(el, styleProp) {
    return getStyle(el, styleProp);
}

function elWidth(el, newval) {
    if (!newval)
        return parseInt(getStyleProp(el, 'width'));
    else
        setStyle(el, 'width', newval, 'px');
}
function elHeight(el, newval) {
    if (!newval)
        return parseInt(getStyleProp(el, 'height'));
    else
        setStyle(el, 'height', newval, 'px');
}
function getAttributes(domel) {
    var attrs = {};
    for (var i = 0; i < domel.attributes.length; i++)
    {
        attrs[domel.attributes[i].name] = domel.attributes[i].value;
    }
    return attrs;
}

// DOM
function removeElement(el)
{
    el.parentNode.removeChild(el)
}

// -- WINDOWS

function centerit(el) {
    //console.log('el h',elHeight(el))
    //console.log(window.innerHeight)
    //console.log(document.body.scrollTop)
    //console.log('res', window.innerHeight + document.body.scrollTop)
    //console.log(el.offsetWidth)
    //console.log(el.clientWidth)
    //console.log(window.innerWidth)
    //console.log(window.innerHeight)
    setStyle(el, 'position', 'absolute');
    setStyle(el, 'left', (window.innerWidth - elWidth(el)) / 2, 'px');
    setStyle(el, 'top', (document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop) + (window.innerHeight - elHeight(el)) / 2, 'px');
}

function heightAlmostFull(el) {
    setStyle(el, 'height', window.innerHeight - 150, 'px');
}

function hide(el) {
    // TODO add restore old state
    var currDisplay = getStyleProp(el, 'display');
    if (currDisplay != 'none') setStyle(el, 'display', 'none');
}
function hide2(el) {
    var currDisplay = getStyleProp(el, 'visibility');
    if (currDisplay != 'hidden') setStyle(el, 'visibility', 'hidden');
}


function show(el) {
    // TODO add restore old state
    var currDisplay = getStyleProp(el, 'display');
    if (currDisplay == 'none') setStyle(el, 'display', 'block');
}
function show2(el) {
    var currDisplay = getStyleProp(el, 'visibility');
    if (currDisplay == 'hidden') setStyle(el, 'visibility', 'visible');
}


// -- COOKIE

function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else var expires = "";
    document.cookie = name + "=" + escape(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}


// -- Equal columns

function EQWC() {
    //if (RegExp(" Mobile/").test(navigator.userAgent)) return;
    var ws = document.querySelectorAll(".EQWC .widget");
    var elH = new ArrayExtended();
    for (var i = 0; i < ws.length; i++)
        elH.push(elHeight(ws[i]));
    var maxH = elH.max();
    for (var i = 0; i < ws.length; i++)
        elHeight(ws[i], maxH);
}
function EQWC2() {
    //if (RegExp(" Mobile/").test(navigator.userAgent)) return;
    var ws = document.querySelectorAll(".EQWC2 .widget");
    var elH = new ArrayExtended();
    for (var i = 0; i < ws.length; i++)
        elH.push(elHeight(ws[i]));
    var maxH = elH.max();
    for (var i = 0; i < ws.length; i++)
        elHeight(ws[i], maxH);
}


// -- EVENTS
// Event.add(slider, "click", function(e) { alert("Hi") })
Event = (function () {
    var guid = 0

    function fixEvent(event) {
        event = event || window.event

        if (event.isFixed) {
            return event
        }
        event.isFixed = true

        event.preventDefault = event.preventDefault || function () {
                this.returnValue = false
            }
        event.stopPropagation = event.stopPropagaton || function () {
                this.cancelBubble = true
            }

        if (!event.target) {
            event.target = event.srcElement
        }

        if (!event.relatedTarget && event.fromElement) {
            event.relatedTarget = event.fromElement == event.target ? event.toElement : event.fromElement;
        }

        if (event.pageX == null && event.clientX != null) {
            var html = document.documentElement, body = document.body;
            event.pageX = event.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
            event.pageY = event.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
        }

        if (!event.which && event.button) {
            event.which = (event.button & 1 ? 1 : ( event.button & 2 ? 3 : ( event.button & 4 ? 2 : 0 ) ));
        }

        return event
    }

    /*  this = element */
    function commonHandle(event) {
        event = fixEvent(event)

        var handlers = this.events[event.type]

        for (var g in handlers) {
            var handler = handlers[g]

            var ret = handler.call(this, event)
            if (ret === false) {
                event.preventDefault()
                event.stopPropagation()
            }
        }
    }

    return {
        add: function (elem, type, handler) {
            if (!elem) {
                if (window['printStackTrace']) console.log(printStackTrace());
                throw 'No domel provided for Event.add. May be early call before dom loaded';
            }
            if (elem.setInterval && ( elem != window && !elem.frameElement )) {
                elem = window;
            }

            if (!handler.guid) {
                handler.guid = ++guid
            }

            if (!elem.events) {
                elem.events = {}
                elem.handle = function (event) {
                    if (typeof Event !== "undefined") {
                        return commonHandle.call(elem, event)
                    }
                }
            }

            if (!elem.events[type]) {
                elem.events[type] = {}

                if (elem.addEventListener)
                    elem.addEventListener(type, elem.handle, false)
                else if (elem.attachEvent)
                    elem.attachEvent("on" + type, elem.handle)
            }

            elem.events[type][handler.guid] = handler
        },

        remove: function (elem, type, handler) {
            var handlers = elem.events && elem.events[type]

            if (!handlers) return

            delete handlers[handler.guid]

            for (var any in handlers) return
            if (elem.removeEventListener)
                elem.removeEventListener(type, elem.handle, false)
            else if (elem.detachEvent)
                elem.detachEvent("on" + type, elem.handle)

            delete elem.events[type]


            for (var any in elem.events) return
            try {
                delete elem.handle
                delete elem.events
            } catch (e) { // IE
                elem.removeAttribute("handle")
                elem.removeAttribute("events")
            }
        }
    }
}())


pageloaded = false;
// dom ready - html loaded
// onload - images loaded
window.onload = function () {
    if (!pageloaded) {
        pageloaded = true;
        init2();
    }
};

if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", function () {
        if (!pageloaded) {
            pageloaded = true;
            init1();
        }
    }, false);
}

function init1() {
    //console.log('init1 domready');
    if (window['gcmainrun']) gcmainrun();
}

function init2() {
    //console.log('init2 onload');
    if (window['gcmainrun']) gcmainrun();
//    if (typeof main == 'function') main();
}

function gcmainrun() {
    for (var i = 0; i < GC.ONLOAD.length; i++) {
        GC.ONLOAD[i].call();
    }
}
