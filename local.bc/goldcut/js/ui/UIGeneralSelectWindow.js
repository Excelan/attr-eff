function UIGeneralSelectWindow(opts)
{
    var richtype = opts.richtype;
    this.richtype = richtype;
    var metadata = null;
    var dataFull = null;
    var dataFilteredSorted = null;
    var selected = null;
    var multipleSelectionAllowed = false;
    var html = this.buildHTML(richtype)
}

UIGeneralSelectWindow.prototype.loadMetadataAndData = function()
{
    /*
    return new Promise(function(resolve, reject) {
        var data = {
            metadata: {
                fields: [
                    {name: "title", title: "Title", type: "string"}, {name: "category", title: "Category", type: "unit"}, {name: "count", title: "Count", type: "integer"}, {name: "ftotal", title: "Total", type: "integer"}
                ]
            },
            data: [
                {urn: 'urn:a:b:c:1', title: "Abc", category: {"urn-category-1": "Cat title 1"}, count: 12, ftotal: 100},
                {urn: 'urn:a:b:c:2', title: "Jkl", category: {"urn-category-2": "Cat title 2"}, count: 15, ftotal: 1},
                {urn: 'urn:a:b:c:3', title: "Okq", category: {"urn-category-1": "Cat title 1"}, count: 17, ftotal: 90},
            ]
        }
        resolve(data);
    });
    */
    var that = this;
    return new Promise(function(resolve, reject) {
        ajax('/uiselectdata', function(datametadata) {
            resolve(datametadata);
        }, {'onError': function(er) { reject(er) }} , 'POST', { 'richtype': that.richtype, 'mpe': id('mpe').value });
    });
};

UIGeneralSelectWindow.prototype.buildHTML = function()
{
    // DATA
    var dataPromise = this.loadMetadataAndData();
    // HTML FULL
    var df = document.createDocumentFragment();
    var header = document.createElement('header');
    this.buildHTMLHeader(header);
    df.appendChild(header);
    var main = document.createElement('main');
    this.buildHTMLMain(main, dataPromise);
    //dataVC.setDataPromise(dataPromise)
    df.appendChild(main);
    var footer = document.createElement('footer');
    df.appendChild(footer);
    this.html = df;
    // header - fields with filter by, search
    // data table - DOMAIN POLYMORPH - call BuildClassTypeSelector (field type-titles, data[])
    // footer - select confirm
}

UIGeneralSelectWindow.prototype.buildHTMLHeader = function(header)
{
    /**
    var el = document.createElement('h3');
    el.appendChild(document.createTextNode('Header'))
    header.appendChild(el);
     */
}

// TODO change only data, same headers (ajax search, local filter/sort)
// load md, reload d
// filter d
UIGeneralSelectWindow.prototype.buildHTMLMain = function(main, dataPromise)
{
    dataPromise.then(function(md) {
    {
        //console.log(md);
        if (md.data[0].length == 0)
        {
            main.innerHTML = '<h1>NO DATA</h1>';
            return;
        }

        // TODO check
        if (!( (md.metadata.fields.length+1) == Object.keys(md.data[0]).length || (md.metadata.fields.length+2) == Object.keys(md.data[0]).length ))
        {
            console.log('md.metadata.fields.length', md.metadata.fields.length);
            console.log('Object.keys(md.data[0]).length', Object.keys(md.data[0]).length);
            throw "Metadata field count have to be == data keys - 1 (1 for urn)";
        }
        // Save data as this.dataFilteredSorted
        this.dataFull = md.data;
        this.dataFilteredSorted = md.data;
        // FILTERS FOR TABLE
        var div = document.createElement('div');
        div.classList.add('filterselectors');
        // EACH
        //console.log(md.metadata.fields);

        try {
            for (var i = 0; i < md.metadata.fields.length; i++) {
                //console.log('I', i);
                var fieldmeta = md.metadata.fields[i];

                if (fieldmeta.type == 'unit') {
                    //console.log(fieldmeta);
                    var cache = {};
                    var filtersDiv = document.createElement('div');
                    filtersDiv.classList.add('filterselector');
                    filtersDiv.classList.add('BLK');
                    filtersDiv.classList.add('hide');
                    filtersDiv.setAttribute('data-for', fieldmeta.name);
                    div.appendChild(filtersDiv);
                    for (var j = 0; j < this.dataFilteredSorted.length; j++) {
                        //console.log('J',j);
                        var dr = this.dataFilteredSorted[j];
                        if (dr[fieldmeta.name] == null || dr[fieldmeta.name] == undefined) continue; // !!!
                        var key = Object.keys(dr[fieldmeta.name])[0];
                        if (key == null || key == undefined) continue; // !!!
                        if (!cache[key]) {
                            cache[key] = true;
                            cellData = dr[fieldmeta.name][key];
                            var p = document.createElement('p');
                            p.classList.add('TOF');
                            var a = document.createElement('a');
                            a.setAttribute('data-id', key); // TODO id?
                            a.setAttribute('data-for', fieldmeta.name);
                            a.setAttribute('data-filtercontext', '/');
                            a.setAttribute('href', '#filterby' + fieldmeta.name);
                            p.appendChild(a);
                            a.appendChild(document.createTextNode(cellData));
                            filtersDiv.appendChild(p);
                        }
                    }
                    //var filtersDiv = document.createElement('div');
                    main.appendChild(filtersDiv);
                } // type == unit

            }
        }
        catch (e)
        {
            console.log("Metadata build error", e);
            //console.log(printStackTrace());
            throw e;
        }


        try {
            // TABLE
            var table = document.createElement('table');
            table.classList.add('uigeneralselect');
            //console.log(md);
            // TODO on 1st run build table head
            var trh = document.createElement('tr');

            // Table HEAD
            for (var i = 0; i < md.metadata.fields.length; i++) {
                var fieldmeta = md.metadata.fields[i];
                //console.log(fieldmeta);
                var th = document.createElement('th');
                th.appendChild(document.createTextNode(fieldmeta.title));
                th.setAttribute('data-field', fieldmeta.name);
                th.setAttribute('data-distinct', fieldmeta.name);
                if (fieldmeta.type == 'unit') {
                    th.classList.add("allowFilterSelect");
                    //th.classList.add("allowFilterSelectSelected");
                }
                trh.appendChild(th);
            }
            table.appendChild(trh);

            // TABLE body render from dataFilteredSorted
            var tbody = document.createElement('tbody');
            table.appendChild(tbody);
            // fill with data from model
            for (var i = 0; i < this.dataFilteredSorted.length; i++) {
                var dr = this.dataFilteredSorted[i];
                //console.log(dr);
                var tr = document.createElement('tr');
                tr.setAttribute('data-urn', dr['urn']);
                tr.setAttribute('data-title', (dr['_extended'] ? dr['_extended'] : ( dr['title'] ? dr['title'] : dr['urn'])));
                for (var j = 0; j < md.metadata.fields.length; j++) {
                    var fieldmeta = md.metadata.fields[j];
                    var td = document.createElement('td');
                    var cellData;
                    // text / html cell view type
                    // data type (simple (string, int), compound (money/curr, unit etc))
                    // decorators - simple data with inline sparkline, percent bar, color bg
                    if (fieldmeta.type == 'unit') {
                        //console.log(dr[fieldmeta.name]);
                        try {
                            if (dr[fieldmeta.name] == null) continue;
                            var key = Object.keys(dr[fieldmeta.name])[0];
                            cellData = dr[fieldmeta.name][key];
                            tr.setAttribute('data-' + fieldmeta.name, key);
                        }
                        catch (e) {
                            console.log(e)
                        }
                    }
                    else {
                        cellData = dr[fieldmeta.name];
                    }
                    td.appendChild(document.createTextNode(cellData));
                    td.setAttribute('data-name', fieldmeta.name);
                    tr.appendChild(td);
                }
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);
            main.appendChild(table);
        }
        catch (e)
        {
            console.log("Data build error (metadata build done)", e);
            console.log("Data loaded", md);
            throw e;
        }

        // selected button return
        var selectSubmit = document.createElement('input');
        selectSubmit.setAttribute('type','button');
        selectSubmit.setAttribute('id','returnSelection');
        selectSubmit.setAttribute('disabled','disabled');
        hide(selectSubmit);
        selectSubmit.setAttribute('value','Подтвердить выбор');
        main.appendChild(selectSubmit);

        // main on click select
        Event.add(main, "click", function (e) {
            var dpath = new DomPath2(e.target);
            if (dpath.testNodesOnPath({'tag':'td'}, {'tag':'tr'}))
            {
                var rowValue = dpath.getNodeBy({'tag':'tr'}).getAttribute("data-urn");
                var rowTitle = dpath.getNodeBy({'tag':'tr'}).getAttribute("data-title");
                //console.log(rowValue);
                var trs = tbody.querySelectorAll('tr');
                [].forEach.call(trs, function(tr) {
                    tr.classList.remove('selected');
                });
                selectSubmit.removeAttribute('disabled');
                show(selectSubmit);
                dpath.getNodeBy({'tag':'tr'}).classList.add('selected');
                //main.param = {rowValue: rowTitle};
                main.param = {};
                //main.param[rowValue] = rowTitle;
                main.param.urn = rowValue; // RETURN RICH SELECT VALUE
                main.param.title = rowTitle;
            }
            else
            {
                //console.log("MISS");
            }
        });

        returnSelectionInit(selectSubmit, main);

        // !!! TODO
        filterSelectorInit();
        filterSelector2Init(tbody);
    }}).catch(function(error) {
        console.log('DATA+METADATA LOAD FROM SERVER ERROR');
        console.log(error);
    });
}

function returnSelectionInit(el, main)
{
    Event.add(el, "click", function (e) {
        //console.log('SELECTED FOR RETURN');
        //console.log(main);
        //console.log(main.param);
        //console.log(main.bridge);
        main.bridge.setValue(main.param); // TODO
        main.onComplete();
        //console.log(el);
    });
}

// data-call param in window opener
function UIGeneralSelectWindowReturn(data, openedWindow)
{
    //console.log('UIGeneralSelectWindowReturn');
    // console.log(this); // div.class=icon
    // console.log(data);
    // console.log(openedWindow);
    openedWindow.querySelector('main').bridge = this.bridge; //.param = 'Nothing selected';
    openedWindow.querySelector('main').onComplete = function() { toggleClass(openedWindow, 'hide') };
    //this.bridge.setValue('X');
}


// filter again (over same filtered data)
UIGeneralSelectWindow.prototype.filterData = function(byField, equalsTo)
{
    // TODO filter
    this.dataFilteredSorted = this.dataFull;
}

UIGeneralSelectWindow.prototype.sortData = function(byField, direction)
{
    // TODO sort
    this.dataFilteredSorted = this.dataFilteredSorted;
}

UIGeneralSelectWindow.prototype.setSelected = function(sel)
{
    this.selected = sel;
}

UIGeneralSelectWindow.prototype.getDom = function()
{
    return this.html;
}



// init Show filters on click
function filterSelectorInit() {
    //console.log('filterSelectorInit');
    var fs = document.querySelectorAll('.allowFilterSelect');
    //console.log(fs.length)
    for (var i = 0; i < fs.length; i++) {
        var fsel = fs[i];
        Event.add(fsel, "click", function (e) {
            //console.log("filterSelectorInit CLICK");
            element = e.target;
            var dg = element.getAttribute('data-distinct');
            //console.log(element);
            var bodyRect = document.body.getBoundingClientRect();
            var elemRect = element.getBoundingClientRect();
            var offsetX = elemRect.left - bodyRect.left;
            var offsetY = elemRect.top - bodyRect.top;
            //console.log(offsetX, offsetY);
            var field = element.getAttribute('data-field');
            //console.log(field);
            var selectorHtml = document.querySelector('[data-for='+field+']');
            //console.log(selectorHtml);
            toggleClass(selectorHtml, 'hide');
            setStyle(selectorHtml, 'left', offsetX - 132, 'px'); // TODO
            setStyle(selectorHtml, 'top', elemRect.height + 10, 'px');
            //setStyle(selectorHtml, 'top', offsetY + elemRect.height, 'px');
            setStyle(selectorHtml, 'width', elemRect.width - 38, 'px');
        });
    }
}
GC.ONLOAD.push(filterSelectorInit);

// init Select filter by
function filterSelector2Init(tbody) {
    //console.log("filterSelector2Init");
    var fs = document.querySelectorAll('.filterselector a');
    for (var i = 0; i < fs.length; i++) {
        var fsel = fs[i];
        Event.add(fsel, "click", function (e) {
            //console.log("filterSelector2Init CLICK");
            e.preventDefault();
            element = e.target;
            var dg = element.getAttribute('data-id');
            var datafor = element.getAttribute('data-for');
            //
            var selectorHtml = document.querySelector('[data-for='+datafor+']');
            toggleClass(selectorHtml, 'hide');
            //
            var filtercontext = element.getAttribute('data-filtercontext');
            //console.log(dg, datafor);
            var filterby = JSON.parse(readCookie('filterby'+filtercontext));
            if (filterby) filters = filterby;
            else filters = {};
            filters[datafor] = dg;

            //console.log(filters);
            //console.log(tbody);
            var trs = tbody.querySelectorAll('tr:not([data-'+datafor+'="'+dg+'"])');
            for (var i = 0; i < trs.length; i++) {
                //console.log('hide', trs[i]);
                trs[i].style.display = 'none';
            }
            var trs = tbody.querySelectorAll('tr[data-'+datafor+'="'+dg+'"]');
            for (var i = 0; i < trs.length; i++) {
                //console.log('show',trs[i]);
                trs[i].style.display = '';
            }

            createCookie('filterby'+filtercontext, JSON.stringify(filters));
            //fadeScreen();
            //window.location.reload();
        });
    }
}
GC.ONLOAD.push(filterSelector2Init);

// clear filter button
GC.ONLOAD.push(function() {
    var clearfilters = id('clearfilters');
    if (!clearfilters) return;
    Event.add(clearfilters, "click", function (e) {
        e.preventDefault();
        var filtercontext = e.target.getAttribute('data-filtercontext');
        eraseCookie('filterby'+filtercontext);
        fadeScreen();
        window.location.reload();
    });
});
