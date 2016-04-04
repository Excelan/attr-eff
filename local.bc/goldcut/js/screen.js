/*
every screen(app) routed by url (/screen/params)
head menu icons (icon, title, )
main - list or screen show item
list items (item template(std or html)) item on click, table row on click
*/

/// !!! CSS HAVE TO BE ON TOP IN HEAD. Before JS

function GCScreenArea()
{
	
}
GCScreenArea.prototype.preloadTemplate = function(){
	
}
GCScreenArea.prototype.init = function(){
	
}
GCScreenArea.prototype.deinit = function(){
	
}

/**
TODO overscreen help tips on buttons
*/
function GCScreen(container, parentscreen, o)
{
    //console.log('new screen', container, parentscreen, o)
    // addsub: return new GCScreen(this.area.dom, this, o);
	this.level = 1;
	this.parentscreen = null;
	if (parentscreen) 
	{
		this.parentscreen = parentscreen;
		this.parentscreen.child = this;
	}
	if (!o)
	{
		this.asidewidth = 200;
		this.headheight = 42;
	}
	else
	{
		this.asidewidth = o.asidewidth;
		this.headheight = o.headheight;
	}
	// get size of parentscreen or of viewport
	if (container)
	{
		this.container = container;
		//this.width = container.clientWidth; // TODO univ getElementWidth
		//this.height = container.clientHeight;
        this.width = getElementWidth(container);
        this.height = getElementHeight(container);
        //console.log(this.width, this.height); // !!!
        //setStyle(container, 'background', 'red')
        //this.width = 1098;
        //this.height = 480;
	}
	else
	{
		this.container = document.getElementsByTagName('body')[0];
		this.width = getActualViewportSize().x;
		this.height = getActualViewportSize().y;
	}
	this.unusedwidth = this.width;
	this.unusedheight = this.height;
	this.usedleft = 0;
	this.usedright = 0;
	this.usedhead = 0;
	this.usedfoot = 0;
	
	this.screen = {};
	this.screen.dom = document.createElement("div");
	this.screen.dom.className += ' screen';
	this.container.appendChild(this.screen.dom);
	this.container.className += ' haschildscreen';
	this.screen.dom.style.top = 0+'px'; // container..offsetTop
	this.screen.dom.style.left = 0+'px'; // container..offsetLeft
	//this.screen.dom.style.zIndex = 2;
	
	this.area = {};
	this.area.dom = document.createElement("div");
	
	this.lsides = this.lsides ? this.lsides : [];
	this.rsides = this.rsides ? this.rsides : [];
	
	this.baseX = this.container.offsetLeft;
	this.baseY = this.container.offsetTop;
	//console.log('UNUSED HEIGHT, this.baseX, this.baseY', this.unusedheight, this.baseX, this.baseY);
}

GCScreen.prototype.addSubScreen = function(o) {
//    console.log('add sub')
//    console.log(this.area.dom)
    return new GCScreen(this.area.dom, this, o);
}




// utils
GCScreen.prototype.renderCallbackPM = function(lside, renderCallback) {
    if (!renderCallback)
    {}
	else if (typeof renderCallback == 'function')
		lside.renderCallback = renderCallback;
	else if (typeof renderCallback == 'string')
		lside.renderCallback = this.reusehtml.bind(renderCallback);
	else 
		console.log('Unsupported callback in addLSide()');
	return lside;
}
GCScreen.prototype.paramAggregate = function(param) {
	var ag = 0;
	ag += this[param];
	if (!this.parentscreen) return ag;
	parentscreen = this.parentscreen;
	var deep = 7;
	while (deep > 0)
	{
		deep--;
		ag += parentscreen[param];
		if (!parentscreen.parentscreen) break;
		parentscreen = parentscreen.parentscreen;
	}
	return ag;
}
GCScreen.prototype.reusehtml = function(containerdom)
{
    //console.log(this)
	var reusedomid = id(this);
	if (!containerdom) alert('no containerdom ' + containerdom);
	if (!reusedomid) alert('no reusedomid ' + this);
    if (!containerdom || !reusedomid) throw "Screen reusehtml failed";
	containerdom.innerHTML = reusedomid.innerHTML;
    var idt = reusedomid.id;
	reusedomid.parentNode.removeChild(reusedomid);
    containerdom.id = idt;
}
// build
GCScreen.prototype.addHeader = function(renderCallback, cls, id) {
	this.header = {};
	this.header = GCScreen.prototype.renderCallbackPM(this.header, renderCallback);
	this.header.dom = document.createElement("header");
	if (cls) addClass(this.header.dom, cls);
    if (id) this.header.dom.id = id;
}
GCScreen.prototype.addLSide = function(renderCallback, cls, id) {
	var lside = {};
	lside = GCScreen.prototype.renderCallbackPM(lside, renderCallback);
	lside.dom = document.createElement("aside");
	addClass(lside.dom, 'left');
	if (cls) addClass(lside.dom, cls);
    if (id) lside.dom.id = id;
	this.lsides.push(lside);
}
GCScreen.prototype.addRSide = function(renderCallback, cls, id) {
	var rside = {};
	rside = GCScreen.prototype.renderCallbackPM(rside, renderCallback);
	rside.dom = document.createElement("aside");
	addClass(rside.dom, 'right');
	if (cls) addClass(rside.dom, cls);
    if (id) rside.dom.id = id;
	this.rsides.push(rside);
	return rside.dom;
}

GCScreen.prototype.useStaticContent = function(domid) {
	this.area.usestatic = domid;
}
GCScreen.prototype.addExcusiveContent = function(renderCallback) {} // exclusive browser default scrollbale (non div with overflow)
GCScreen.prototype.setAreaRenderCallback = function(renderCallback) 
{
	this.area.renderCallback = renderCallback;
}

// render
GCScreen.prototype.render = function(sname)
{
    //console.log('render start ' + sname);
	// render header
	if (this.header && this.header.dom)
	{
		this.header.dom.style.position = "fixed";
		this.header.dom.style.width = this.unusedwidth + "px";
		this.header.dom.style.height = this.headheight + 'px';
		this.header.dom.style.top = this.baseY + 0 + 'px';
		this.header.dom.style.left = this.baseX + 0 + 'px';
		if (this.header.renderCallback) this.header.renderCallback(this.header.dom);
		this.screen.dom.appendChild(this.header.dom);
		this.unusedheight -= this.headheight;
		this.usedhead += this.headheight;
	}
	// render footer
	// TODO
	// render left sidebars
	var lsidesallwidth = 0;
	for (var i=0; i < this.lsides.length; i++)
	{
		//console.log('L', i, this.lsides[i])
		var lside = this.lsides[i];
		lside.dom.style.position = "fixed";
		lside.dom.style.width = this.asidewidth + "px";
		lside.dom.style.height = this.unusedheight + "px"; // - parseInt(this.header.dom.style.height)
		this.unusedwidth -= this.asidewidth;
		lside.dom.style.top = this.baseY + this.usedhead + 'px'; // parseInt(this.header.dom.style.height)
		lside.dom.style.left = this.baseX + lsidesallwidth + 'px';
		addClass(lside.dom, 'left'+(i+1));
		addClass(lside.dom, 'deep'+this.paramAggregate('level'));
		if (lside.renderCallback) lside.renderCallback(lside.dom);
		this.screen.dom.appendChild(lside.dom);
		lsidesallwidth += parseInt(lside.dom.style.width);
		this.usedleft += this.asidewidth;
	}
	// render right sidebars
	// render left sidebars
	var rsidesallwidth = 0;
	for (var i=0; i < this.rsides.length; i++)
	{
		//console.log('R', i, this.rsides[i])
		var rside = this.rsides[i];
		rside.dom.style.position = "fixed";
		rside.dom.style.width = this.asidewidth + "px";
		rside.dom.style.height = this.unusedheight + "px"; // - parseInt(this.header.dom.style.height)
		rside.dom.style.top = this.baseY + this.usedhead   + 'px'; // parseInt(this.header.dom.style.height)
		rside.dom.style.left = this.baseX - rsidesallwidth + (this.width - this.asidewidth)   + 'px'; // this.unusedwidth
		this.unusedwidth -= this.asidewidth;
		//rside.dom.style.left = '400px';
		//console.log('RW', rside.dom.style.left, this.unusedwidth, rsidesallwidth)
		addClass(rside.dom, 'right'+(i+1));
		if (rside.renderCallback) rside.renderCallback(rside.dom);
		this.screen.dom.appendChild(rside.dom);
		rsidesallwidth += parseInt(rside.dom.style.width);
		this.usedright += this.asidewidth;
	}
	// render CONTENT area (area can be end content or place for subscreen)
	this.area.dom.className += ' area';
	this.area.dom.style.overflowX = 'hidden';
	this.area.dom.style.overflowY = 'auto';
	//console.log(this.unusedwidth, '@', this.baseX, this.usedleft, this.usedright)
	//this.area.dom.style.width = this.baseX + this.unusedwidth - this.usedright + "px";
	this.area.dom.style.width = (this.width - this.usedleft - this.usedright) + "px";
	this.area.dom.style.height = this.baseY + this.unusedheight + "px";
	//this.area.dom.style.background = "olive";
	this.area.dom.style.top = this.baseY + this.headheight + 'px'; // parseInt(this.header.dom.style.height)
	this.area.dom.style.left = this.baseX + lsidesallwidth + 'px';
	if (this.area.renderCallback) this.area.renderCallback(this.area.dom);
	else if (this.area.usestatic) 
	{
		/**
		id('main').style.marginLeft = this.paramAggregate('usedleft')+'px';
		id('main').style.marginRight = this.paramAggregate('usedright')+'px';
		id('main').style.marginTop = this.paramAggregate('usedhead')+'px';
		id('main').style.marginBottom = this.paramAggregate('usedfoot')+'px';
		*/
	}
	this.screen.dom.appendChild(this.area.dom); // used for child append anyway
	if (this.area.usestatic)
	{
		// #area > #main - for content padding 
		var maindiv = document.createElement("div");
		maindiv.setAttribute('id','main');
		this.area.dom.appendChild(maindiv);
		GCScreen.prototype.reusehtml.call(this.area.usestatic, maindiv)
	}
	//console.log('this.area.style.height', this.area.dom.style.height);
	
	// recursive call render deep
	/**
	TODO calc each screen size without render
	var level = 1;
	if (!this.child) return level;
	child = this.child;
	while (1)
	{
		level++;
		child.render();
		if (!child.child) break;
		child = child.child;
	}
	console.log('render', level);
	return level;
	*/
    //console.log('render end ' + sname);
}






// run once
/*
 click event = action_ class prefix
 touch, over, tap?
 div.frame > ul.scope_newslist > a.action_select | a.action_delete | button.action_edit
 scope_newslist/action_select(this.urn)
 action_selectnews(this.urn)
 MAKE SURE CLICKABLE IS <li><a class='action_' href='some'> NOT <li class='action_'><a href='some'>
 */
GCScreen.prototype.onclick = function()
{
    //console.log('init onclick')
    //console.log(this.screen.dom)
    Event.add(this.screen.dom, "click", function(e) {
        var dpath = new DomPath2(e.target);
        //console.log('CLICK')
        for (var i=0;i<dpath.dompath[0].classes.length;i++)
        {
            var domclass = dpath.dompath[0].classes[i];
            if (domclass.indexOf('action_') === 0)
            {
                if (window[domclass])
                {
                    var urn = dpath.dompath[0].dom.getAttribute('data-urn')
                    if (urn)
                        window[domclass].call(e,urn);
                    else
                        window[domclass].call(e);
                }
                else
                    console.log(domclass + ' found but no has action function')
            }
        }
        //console.log(dpath.dompath); // dpath.dompath[0].dom
    })

    // TODO LISTING
    /*
     Event.add(id('listing'), "click", function(e) {
     var dpath = new DomPath2(e.target);
     //console.log(dpath.dompath[0], dpath.dompath[0].dom.getAttribute('data-urn'));
     if (dpath.dompath[0].tag == 'BUTTON' && dpath.dompath[0].classes[0] == 'addProductTo') {
     var hostURN = dpath.dompath[0].dom.getAttribute('data-urn');
     console.log(hostURN);
     window.location.hash = 'add/product/'+hostURN;
     }
     if (dpath.dompath[0].tag == 'BUTTON' && dpath.dompath[0].classes[0] == 'cloneProductFrom') {
     var hostURN = dpath.dompath[0].dom.getAttribute('data-urn');
     console.log(hostURN);
     window.location.hash = 'add/product/'+hostURN+'/clone';
     }
     });
     */

}