var fadeScreen = function () {
    //console.log('FADE');
    if (!id('overlay')) { console.log("No dom id overlay"); return }
    removeClass(id('overlay'), 'hide');
}
var unFadeScreen = function () {
    //console.log('UNFADE');
    addClass(id('overlay'), 'hide');
}


function showOverlay() {
    el = document.getElementById("overlay");
    //el.style.visibility = (el.style.visibility == "visible") ? "hidden" : "visible";
    el.style.visibility = "visible";
}
function hideOverlay() {
    el = document.getElementById("overlay");
    el.style.visibility = "hidden";
    //el.style.visibility = (el.style.visibility == "visible") ? "hidden" : "visible";
}

function yellowHighlight(el) {
    addClass(el, 'animatedFast');
    setStyle(el, 'backgroundColor', 'yellow');
    window.setTimeout(function () {
        setStyle(el, 'backgroundColor', 'transparent')
    }, 500);
}


function showWithFx(el) {
    show2(el);
    margeNormal(el)
    window.setTimeout(function () {
        setStyle(el, 'opacity', '1.0')
    }, 200)
    // local for a site
    if (id('emailfield')) setStyle(id('emailfield'), 'height', '120px')
}

function hideWithFx(el) {
    setStyle(el, 'opacity', '0.0')
    if (id('emailfield')) {
        window.setTimeout(function () {
            setStyle(id('emailfield'), 'height', '0px')
        }, 200)
        window.setTimeout(function () {
            hide2(el)
        }, 255)
    }
}


//Блок всплывающей информации
function notification(stat, text){
    var notification = document.getElementById('notification');
    var content = notification.querySelector('.content');
    var nottext = notification.querySelector('.nottext');
    var notimg = notification.querySelector('img');

    nottext.innerHTML = text;
    notification.style.height = "70px";


    if(stat == "GOOD"){
        if(notification.classList.contains("notification_bad")){
            notification.classList.remove("notification_bad");
        }
        notification.classList.toggle("notification_good",true);

    }
    if(stat == "BAD"){
        if(notification.classList.contains("notification_good")){
            notification.classList.remove("notification_good");
        }
        notification.classList.toggle("notification_bad",true);
    }
    if(stat == "OTHER"){

        if(notification.classList.contains("notification_good")){
            notification.classList.remove("notification_good");
        }
        if(notification.classList.contains("notification_bad")){
            notification.classList.remove("notification_bad");
        }
        notification.classList.toggle("notification_other",true);
    }


    function hideAsH0() {
        notification.style.height = "0px";
    }

    Event.add(notimg, 'click', function (e) {
        hideAsH0();
    });

    setTimeout(hideAsH0, 7*1000);
}



function margeNormal(el) {
    setStyle(el, 'marginRight', '0')
}
function margeRight(el) {
    setStyle(el, 'marginRight', '100%')
}
function margeLeft(el) {
    setStyle(el, 'marginLeft', '100%')
}


function moveLeft(el, delta) {
    var cl = parseInt(getStyle(el, 'left'))
    setStyle(el, 'left', cl - delta, 'px');
}
function moveRight(el, delta) {
    var cl = parseInt(getStyle(el, 'left'))
    setStyle(el, 'left', cl + delta, 'px');
}







var openModalWindow = function(opener) {
    var el = null;
    el = opener.getAttribute('data-openwindow');
    var isdisabled = opener.getAttribute('data-openwindowdisabled');
    if (isdisabled == 'disabled') return;
    if (!id(el)) {
        //console.log('No dom for window data-openwindow=' + el);
        var windowDom = document.createElement('div');
        document.body.appendChild(windowDom);
        windowDom.id = el;
        windowDom.classList.add('uiwindow');
        //windowDom.classList.add('windowcenter');
        windowDom.classList.add('windowcenterraw');
        windowDom.classList.add('hide');
    }
    toggleClass(id(el), 'hide');
    if (!opener.getAttribute('data-legacy'))
        centerit(id(el));
    // window content static (inside html)
    // window content dynamic render by function (UIGeneralSelectWindow)
    // call fn on open
    var renderer;
    var renderedDom;
    if (rendererfn = opener.getAttribute('data-windowcontentrenderer'))
    {
        if (window[rendererfn])
        {
            //if (opener.ATTACH == undefined)
            if (true)
            {
                var opts = opener.dataset; // html5. dataset.listSize
                var renderer = new window[rendererfn](opts);
                opener.ATTACH = renderer;
                renderedDom = renderer.getDom();
                //console.log(renderedDom);
                id(el).innerHTML = '';
                id(el).appendChild(renderedDom);
            }
            else
            {
                console.log("WINDOW ALREADY BUILT");
            }
        }
        else throw "No window[renderer] fn"
    }
    var cb = null;
    cb = opener.getAttribute('data-call');
    if (GC.CALLBACKS[cb]) GC.CALLBACKS[cb].call(opener, opener.dataset, id(el));
    if (window[cb]) window[cb].call(opener, opener.dataset, id(el)); // console.log(window[cb]);
}

var modalWindowOpeners = function () {
    var managedForms = document.querySelectorAll('[data-openwindow]');
    for (var i = 0; i < managedForms.length; i++) {
        var windowOpener = managedForms[i];
        Event.add(windowOpener, "click", function (e) {
            e.preventDefault();
            //
            openModalWindow(e.target);
            //
        });
    }
    document.onkeydown = function (evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
            //console.log('a');
            var managedForms = document.querySelectorAll('[data-openwindow]');
            for (var i = 0; i < managedForms.length; i++) {
                var windowOpener = managedForms[i];
                var w = id(windowOpener.getAttribute('data-openwindow'));
                if (w) addClass(w, 'hide'); // TODO check w = null reason
            }
        }
    };
}
GC.ONLOAD.push(modalWindowOpeners);


var domRemobeByID = function () {
    Event.add(document.body, "click", function (e) {
        var id = e.target.getAttribute('data-removedom');
        var idp = e.target.parentNode.getAttribute('data-removedom');
        if (id) {
            e.preventDefault();
            domel = document.querySelector('[data-id="' + id + '"]');
            domel.parentNode.removeChild(domel);
        }
        if (idp) // click on a/img
        {
            e.preventDefault();
            domel = document.querySelector('[data-id="' + idp + '"]');
            domel.parentNode.removeChild(domel);
        }
    });
}


GC.ONLOAD.push(function () {
    if (document.querySelectorAll('body.withdommanager').length)
        GC.ONLOAD.push(domRemobeByID);
});

// <div class="FR"><a data-isliked="" data-id="" data-proc="likeswitch" href="#">LIKE</a></div>



// current tab active nav
GC.ONLOAD.push(function() {
    var fs = document.querySelectorAll('ul.nav li a');
    for (var i = 0; i < fs.length; i++) {
        var fsel = fs[i];
        try {
            var url = new Url(fsel.href); // TODO Url?
            if (url.path == window.location.pathname) addClass(fsel, 'current');
        }
        catch(e) {}
    }
});


/*

table styles
http://www.smashingmagazine.com/2008/08/top-10-css-table-designs/2/

table sorters
http://stackoverflow.com/questions/7558182/sort-a-table-fast-by-its-first-column-with-javascript-or-jquery
http://codereview.stackexchange.com/questions/37632/sorting-an-html-table-with-javascript
http://www.terrill.ca/sorting/
http://www.workingwith.me.uk/articles/scripting/standardista_table_sorting
http://www.javascripttoolbox.com/lib/table/
http://webfx.eae.net/dhtml/sortabletable/sortabletable.html
http://sandbox.scriptiny.com/table-sorter/index.php
http://www.kryogenix.org/code/browser/sorttable/
*/
