/**
 * var template = '<div class="BLK list BRIN" style="width:100%" id=""><div style="width:50px" class="FL minute">time. timebonus</div><div style="width:50px" class="FL icon image"><img data-key="image-src" src=""></div><div class="FL description">Text</div></div>';
 var imgSRC = '/img/action/'+action+'.png';
 var data = [{"minute": minbon, "description": text, "image": {'src': imgSRC } }];

 var frag = createFrag(template);
 renderFrag(frag, data);

 var domid = id('place');
 //domid.innerHTML = '';
 if (domid.firstChild)
 domid.insertBefore(frag, domid.firstChild);
 else
 domid.appendChild(frag);
 //domid.replaceChild(frag);
 */

/*
 Event.add(id('place'), "click", function(e){
 var processor = e.target.getAttribute('data-proc');
 //console.log(e.target, processor, window[processor]);
 if (window[processor]) window[processor](e.target);
 return false;
 });
 var d = {};
 d.id = i.id;
 d.avatar = {'src':i.profile_picture};
 d.author = {};
 d.author.username = ('@' + i.username).parseUsername().emoji(); // i.user.full_name
 d.author.userabout = i.full_name.emoji();
 d.author.bio = i.bio.emoji();
 d.author.website = i.website.emoji();
 d.counts = {};
 d.counts.followed_by = i.counts.followed_by;
 d.counts.follows = i.counts.follows;
 d.counts.media = i.counts.media;

 var userHeadFrag = createFrag(userHeadFragHTML);
 //console.log(userHeadFrag);
 renderFrag(userHeadFrag, d);
 var el = id('head');
 el.appendChild(userHeadFrag);

 Event.add(id('switchfollowuser'), "click", function(e){
 var processor = e.target.getAttribute('data-proc');
 console.log(e.target, processor);
 if (window[processor]) window[processor](e.target);
 return false;
 });
 window.likeswitch = function(e){
 var fid = e.getAttribute('data-id');
 var isliked = e.getAttribute('data-isliked');
 console.log('LIKE', fid, isliked);
 if (isliked == 'yes')
 {
 console.log('YES, LIKED. DISLIKE!');
 e.setAttribute('data-isliked','no');
 ajax('http://'+ajaxhost+':'+ajaxport+'/like', onLikeConfirmed, {}, 'POST', {'token': token, 'fid': fid, 'like': 'no'});
 }
 else {
 console.log('NO, NOT LIKED. I LIKE!');
 e.setAttribute('data-isliked','yes');
 ajax('http://'+ajaxhost+':'+ajaxport+'/like', onLikeConfirmed, {}, 'POST', {'token': token, 'fid': fid, 'like': 'yes'});
 }
 }
 window.swfollow = function(e){
 var fid = e.getAttribute('data-id');
 var isliked = e.getAttribute('data-follow');
 console.log('FOLLOW', fid, isliked);
 if (isliked == 'yes')
 {
 e.setAttribute('data-follow','no');
 ajax('http://'+ajaxhost+':'+ajaxport+'/follow', onLikeConfirmed, {}, 'POST', {'token': token, 'uid': fid, 'follow': 'no'});
 }
 else {
 e.setAttribute('data-follow','yes');
 ajax('http://'+ajaxhost+':'+ajaxport+'/follow', onLikeConfirmed, {}, 'POST', {'token': token, 'uid': fid, 'follow': 'yes'});
 }
 }
 */

function createFragFromElement(domel) {
    var frag = createFrag(domel.innerHTML);
//    domel.parentNode.removeChild(domel);
    return frag;
}

function createFrag(html) {
    var frag = document.createDocumentFragment();
    var temp = document.createElement('div');
    temp.innerHTML = html;
    while (temp.firstChild) {
        frag.appendChild(temp.firstChild);
    }
    return frag;
}
/**
 var r = m[0].cloneNode(true);
 */

function matchingNodeForKey(f, k) {
    //console.log('MD', k, f);
    var m = f.querySelectorAll('.' + k);
    if (m.length) return m[0];
    m = f.querySelectorAll('[' + k + ']');
    if (m.length) return m[0];
    m = f.querySelectorAll('#' + k);
    if (m.length) return m[0];
    //if (!cwn) cwn = f;
    //return f;
}

/**
 1 attr data-NAME > :setAttr NAME (data-)
 <figure class="image"><img data-key="image-src" src="" | {"title":"TITLECAlistScreen,last","image":{"src":"/path.jpg","alt":""}}

 2 .NAME > :setText
 3 [ATTR] > :setAttr
 4 #NAME > setText
 replace In parentNode??
 */
function queryReplaceKeyInFragment(f, k, value, pk) {
    if (!f) throw 'No fragment for key replacement';
    var wn, dk;

    if (pk)
        dk = pk + '-' + k;
    else
        dk = k;

    //console.log('DK', dk, k);
    //var dkk = '[data-'+k+']'; // [data-selector=src]
    //var dkk = '[data-selector="' + k + '"]';
    var dkk = '[data-write-' + k + ']';
    var dkki = 'data-' + k;
    //console.log('DK1', pk, dk, dkk, k);
    var m = f.querySelectorAll(dkk); // if <img src=* is in root level and overlays another imgs // '[data-key='+dk+']'
    for (var i = 0; i < m.length; i++) {
        wn = m[i];
        var dwrite = wn.getAttribute('data-write-' + k);
        //console.log(k, 'data-write-'+k+'=', dwrite);
        if (wn.getAttribute(k) != null) {
            //console.log('has direct attrib'); // ?????????????? used?
            wn.setAttribute(k, value);
        }
        else if (dwrite != null) {
            //console.log('has data-write-k ('+'data-write-'+k+') = '+dwrite);
            wn.setAttribute(dwrite, value);
        }
        else {
            //console.log('no direct attrib, no data-write-k ('+'data-write'+k+'). set .innerText');
            wn.innerText = value;
            //wn.setAttribute(dkki, value);
        }
        //wn.removeAttribute('data-selector');
        if (dwrite != null) wn.removeAttribute('data-write-' + k);
        if (i == m.length - 1) return;
    }

    var dkk = '[data-' + k + ']';
    var m = f.querySelectorAll(dkk);
    for (var i = 0; i < m.length; i++) {
        wn = m[i];
        var an = 'data-' + k;
        wn.setAttribute(an, value);
        if (i == m.length - 1) return;
    }

    //console.log('DK2', dk, k, '.'+k);
    var m = f.querySelectorAll('.' + k); // for match on base level when f is target itself
    for (var i = 0; i < m.length; i++) {
        wn = m[i];
        //nodeSetText(wn, value);
        wn.innerHTML = value;
        if (i == m.length - 1) return;
    }

    //console.log('DK3', dk, k, '['+k+']');
    m = f.querySelectorAll('[' + k + ']');
    for (var i = 0; i < m.length; i++) {
        wn = m[i];
        wn.setAttribute(k, value);
        //if (k == 'id') return;
        if (i == m.length - 1) return;
    }

    //console.log('DK4', dk, k, '#'+k);
    m = f.querySelectorAll('#' + k);
    for (var i = 0; i < m.length; i++) {
        wn = m[i];
        //nodeSetText(wn, value);
        wn.innerHTML = value;
        if (i == m.length - 1) return;
    }
    //console.log(f, '+', k, '+', value, 'replaced: ', wn);
    // WHERE IT USED????
    if (!wn && f.parentNode) {
        //console.log('^^^ replace in f.parentNode', f, f.parentNode);
        queryReplaceKeyInFragment(f.parentNode, k, value);
    }
    if (!wn && !f.parentNode) {
        //console.log('!wn, !f.parentNode');
        if (hasClass(f, k)) {
            wn = f;
            nodeSetText(wn, value);
        }
        //console.log(k, value, f, typeof(f.getAttribute));
        if (f.getAttribute && f.getAttribute(k)) { // if not found in upper
            wn = f;
            wn.setAttribute(k, value);
        }
    }
    /*
     if (wn !== undefined)
     {
     wn.classList.add("X_"+k);
     console.log(wn, '!== undefined');
     }
     */
    return wn;
}

/**
 k
 k.k.*
 [k]
 k:[k]
 k:[k:k] ?
 k:[k:[k]] ?
 */
function recursiveKeyDataFragmentRenderer(f, d, k, pk) // , prepath
{
    //console.log('@', f, d, k, pk);
    if (!f) {
        //console.log(k, d);
        throw 'No fragment for match';
    }
    var datalocal;
    if (k) datalocal = d[k];
    else datalocal = d;
    //console.log('datalocal, d, k: ', datalocal, d, k);
    if (datalocal instanceof Array) // whole data as [] or {somekey: []
    {
        //console.log('ARRAY K', k);
        /**
         var sc = ".list";
         if (k) sc += '.'+k;
         */
        var listname;
        if (!k) listname = 'root';
        else listname = k;
        sc = "[data-list='" + listname + "']";
        var m = f.querySelectorAll(sc);
        if (m.length) {
            var pn = m[0].parentNode;
            for (var i = 0; i < datalocal.length; i++) {
                //console.log('ARRAY' + i);
                var r = m[0].cloneNode(true);
                //pn.appendChild(r);
                r.className += ' __list' + i;
                if (datalocal[i]['_level']) {
                    addClass(r, "level_" + datalocal[i]['_level']);
                }
                //console.log(datalocal[i]);
                recursiveKeyDataFragmentRenderer(r, datalocal[i]);
                pn.appendChild(r);
            }
            pn.removeChild(m[0]);
        }
        else if (typeof k == 'undefined') {
            var e = 'key undefined (' + k + '). Pk: ' + pk;
            console.log(e);
        }
        else {
            var e = 'key: ' + k + ' is array(?) but no one css path "' + sc + '" found';
            //console.log(e);
            //throw e;
        }
    } // DEEP: {}
    else if (typeof(datalocal) == 'object' && k) { // на первом проходе данные являются объектом, а нам нужны вложенные объекты
        var cwn = matchingNodeForKey(f, k); // node width name of inner container (<news><_image_><img>)
        if (!cwn) cwn = f;
        //console.log('DEEP', k, f.id, cwn.id, f.innerHTML, cwn.innerHTML);
        //console.log('OBJECT, container work node, f, k', datalocal, cwn, f, k, f.id);
        for (var kk in datalocal) {
            //console.log('--', k, kk, '==', datalocal[kk], '++',  cwn);
            if (cwn) recursiveKeyDataFragmentRenderer(cwn, datalocal, kk, k); //, prepathlocal
        }
    }
    else if (typeof(datalocal) == 'object' && !k) {
        //console.log('ROOT OBJECT', datalocal, f);
        for (var kr in datalocal)
            wn = recursiveKeyDataFragmentRenderer(f, datalocal, kr, k)
    }
    else // final object for keys replace {title: etc}
    {
        //console.log('FINAL KEY', k, f, datalocal, pk);
        var wn = queryReplaceKeyInFragment(f, k, datalocal, pk);
        //console.log(k,f);
    }

}

function renderFrag(f, d) {
    if (!f) throw 'No fragment in renderFrag';
    if (!d) throw 'No data in renderFrag';
    recursiveKeyDataFragmentRenderer(f, d);
}

