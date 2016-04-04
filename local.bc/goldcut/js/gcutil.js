/**
 * CORS
 http://www.nczonline.net/blog/2010/05/25/cross-domain-ajax-with-cross-origin-resource-sharing/

 Access-Control-Allow-Origin: http://www.nczonline.net
 Access-Control-Allow-Methods: POST, GET
 Access-Control-Allow-Headers: NCZ
 Access-Control-Max-Age: 1728000

 Firefox 3.5+, Safari 4+, and Chrome all support preflighted requests; Internet Explorer 8 does not.
 To do the same in Internet Explorer 8, you’ll need to use the XDomainRequest object in the same manner:
 The XMLHttpRequest object in Firefox, Safari, and Chrome has similar enough interfaces to the IE XDomainRequest object that this pattern works fairly well. The common interface properties/methods are:

 req Origin: http://www.nczonline.net
 resp Access-Control-Allow-Origin: http://www.nczonline.net
 */
/**
 html5 cross domain postPessage()
 ie8 XDomainRequest
 */
function createCORSRequest(method, url) {
    var xhr = new XMLHttpRequest();
    if ("withCredentials" in xhr) {
        xhr.open(method, url, true);
    } else if (typeof XDomainRequest != "undefined") {
        xhr = new XDomainRequest();
        xhr.open(method, url);
    } else {
        xhr = null;
    }
    return xhr;
}
/*
 var request = createCORSRequest("get", "http://www.nczonline.net/");
 if (request){
 request.onload = function(){
 //do something with request.responseText
 };
 request.send();
 }

 */


function isAndroid() {
    var ua = navigator.userAgent.toLowerCase();
    var isA = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
    if (isA) return true;
    else return false;
}

function is_array(a) {
    return typeof(a) == 'object' && (a instanceof Array);
}
function isArray(obj) {
    return (typeof obj !== 'undefined' &&
    obj && obj.constructor === Array);
}
function in_array(obj, arr) {
    return (arr.indexOf(obj) != -1);
}


/** Функция для перевода на лат и обратно на рус. Если с английского на русский, то передаём вторым параметром true.
 var txt = "Съешь ещё этих мягких французских булок, да выпей же чаю!";
 alert(transliterate(txt));
 alert(transliterate(transliterate(txt), true));
 */

var transliterate = (function () {
        var
            rus = "щ   ш  ч  ц  ю  я  ё  ж  ъ  ы  э  а б в г д е з и й к л м н о п р с т у ф х ь ї".split(/ +/g),
            eng = "shh sh ch cz yu ya yo zh `` y' e` a b v g d e z i j k l m n o p r s t u f x ` i".split(/ +/g)
            ;
        return function (text, engToRus) {
            var x;
            for (x = 0; x < rus.length; x++) {
                text = text.split(engToRus ? eng[x] : rus[x]).join(engToRus ? rus[x] : eng[x]);
                text = text.split(engToRus ? eng[x].toUpperCase() : rus[x].toUpperCase()).join(engToRus ? rus[x].toUpperCase() : eng[x].toUpperCase());
            }
            return text;
        }
    })();


function bytesToSize(bytes, precision) {
    var kilobyte = 1024;
    var megabyte = kilobyte * 1024;
    var gigabyte = megabyte * 1024;
    var terabyte = gigabyte * 1024;

    if ((bytes == 0)) return "&mdash;";

    if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' <span class="fsizechar">B</span>';

    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' <span class="fsizechar">KB</span>';

    } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return (bytes / megabyte).toFixed(precision) + ' <span class="fsizechar">MB</span>';

    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return (bytes / gigabyte).toFixed(precision) + ' <span class="fsizechar">GB</span>';

    } else if (bytes >= terabyte) {
        return (bytes / terabyte).toFixed(precision) + ' <span class="fsizechar">TB</span>';

    } else {
        return bytes + ' <span class="fsizechar">B</span>';
    }
}

// -- STRING UTILS

function trim(str) {
    var str = str.replace(/^\s\s*/, ''), ws = /\s/, i = str.length;
    while (ws.test(str.charAt(--i)));
    return str.slice(0, i + 1);
}
function ltrim(s) {
    var ptrn = /\s*((\S+\s*)*)/;
    return s.replace(ptrn, "$1");
}
function rtrim(s) {
    var ptrn = /((\s*\S+)*)\s*/;
    return s.replace(ptrn, "$1");
}


String.prototype.emoji = function () {
    if (!window['ioNull']) {
        console.log('Emoji.js not included');
        return this;
    }
    return ioNull.emoji.parse(this);
}

String.prototype.parseURL = function () {
    return this.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+/g, function (url) {
        return url.link(url);
    });
};
String.prototype.parseHashtag = function () {
    return this.replace(/[#]+[A-Za-z0-9-_]+/g, function (t) {
        //var tag = t.replace("#","%23")
        var tag = t.replace("#", "");
        return t.link("#tag/" + tag);
    });
};
String.prototype.parseUsername = function () {
    return this.replace(/[@]+[A-Za-z0-9-_]+/g, function (u) {
        var username = u.replace("@", "");
        //var username = u;
        return u.link("#user/" + username);
    });
};
String.prototype.forceUsername = function () {
    return this.link("#user/" + this);
};


/**
 for (var key in some_array) {
 var val = some_array [key];
 alert (key+' = '+val);
 }
 The purpose of the Array.prototype.map method is to create a new array with the results of calling the callback function on every array element.
 The purpose of the Array.prototype.forEach method is to iterate over an array, executing the provided callback function once per array element.
 The purpose of the for...in statement is to enumerate object properties.
 I think that the for...in statement should be avoided to traverse any array-like1 object, where the real purpose is iterate over numeric indexes and not enumerate the object properties (even knowing that those indexes are properties).
 Reasons to avoid for...in to iterate array-like objects:
 Iterates over inherited user-defined properties in addition to the array elements, if you use a library like MooTools for example, which extend the Array.prototype object, you will see all those extended properties.
 The order of iteration is arbitrary, the elements may not be visited in numeric order.
 */


// array [x.y] transpose
function transposeArray(array) {
    var transposedArray = array[0].map(function (col, i) {
        return array.map(function (row) {
            return row[i]
        })
    });
    return transposedArray;
}

var createRange = function (n) {
    return Array.apply(null, new Array(n)).map(function (empty, index) {
        return index + 1;
    });
};

Number.prototype.formatMoney = function (c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    //parseFloat(n) == parseFloat(i)
    var cn = (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    if (cn = '.00') cn = '';
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + cn;
};


var makeCRCTable = function () {
    var c;
    var crcTable = [];
    for (var n = 0; n < 256; n++) {
        c = n;
        for (var k = 0; k < 8; k++) {
            c = ((c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1));
        }
        crcTable[n] = c;
    }
    return crcTable;
}

var crc32 = function (str) {
    var crcTable = window.crcTable || (window.crcTable = makeCRCTable());
    var crc = 0 ^ (-1);

    for (var i = 0; i < str.length; i++) {
        crc = (crc >>> 8) ^ crcTable[(crc ^ str.charCodeAt(i)) & 0xFF];
    }

    return (crc ^ (-1)) >>> 0;
};



