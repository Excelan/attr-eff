function time() {
    var d = new Date();
    var n = d.getMilliseconds();
    return n;
}
function timestampNow() {
    return Math.round(new Date().getTime() / 1000);
}


// <abbr class="timeago" title="2008-07-17T09:24:17Z">July 17, 2008</abbr>
function timeAgo(time) {
    var ts = parseInt(time);
    //var date = new Date((time || "").replace(/-/g,"/").replace(/[TZ]/g," ")),
    // var ts = date.getTime();
    var now = (new Date()).getTime() / 1000;
    var diff = now - ts;
    var day_diff = Math.ceil(diff / 86400);
    if (isNaN(day_diff) || day_diff < 0) return 'time_shift';
    return day_diff == 0 && (
        diff < 60 && "Сейчас" ||
        diff < 120 && "Минуту назад" ||
        diff < 3600 && Math.floor(diff / 60) + " минут" ||
        diff < 7200 && "Час назад" ||
        diff < 86400 && Math.floor(diff / 3600) + " часов") ||
        day_diff == 1 && "Вчера" ||
        day_diff < 7 && day_diff + " дней назад" ||
        day_diff < 31 && Math.ceil(day_diff / 7) + " недели" ||
        day_diff > 31 && Math.ceil(day_diff / 31) + " месяцев";
}


function pad(n) {
    return n < 10 ? '0' + n : n.toString()
}

function dateFromUTC(dateAsString, ymdDelimiter) {
    if (!ymdDelimiter) ymdDelimiter = '-';
    var pattern = new RegExp("(\\d{4})" + ymdDelimiter + "(\\d{2})" + ymdDelimiter + "(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})");
    var parts = dateAsString.match(pattern);
    var ts = new Date(Date.UTC(
        parseInt(parts[1])
        , parseInt(parts[2], 10) - 1
        , parseInt(parts[3], 10)
        , parseInt(parts[4], 10)
        , parseInt(parts[5], 10)
        , parseInt(parts[6], 10)
        , 0
    ));
    var offset = new Date().getTimezoneOffset();
    return Math.round(ts / 1000) + offset * 60;
}

function convertDateToUTC(date) {
    return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds());
}

function currentTimestamp() {
    return Math.round(new Date().getTime() / 1000);
}

function GCDateToTimestamp(gc) {
    var ts = new Date(Date.UTC(
        parseInt(gc.year)
        , parseInt(gc.month, 10) - 1
        , parseInt(gc.day, 10)
        , parseInt(gc.h, 10)
        , parseInt(gc.m, 10)
        , parseInt(gc.s, 10)
        , 0
    ));
    var offset = new Date().getTimezoneOffset();
    return Math.round(ts / 1000) + offset * 60;
}

function YYYYMMDDHHMMtoTimestamp(dateh) {
    return dateFromUTC(dateh);
    return dateh;
}

function timestampToYYYYMMDDHHMMSS(ts) {
    ts = parseInt(ts);
    if (!ts) return null;
    var date = new Date(ts * 1000);
    var seconds = date.getSeconds();
    return timestampToYYYYMMDDHHMM(ts) + ':' + pad(seconds);
}

function timestampToYYYYMMDDHHMM(ts) {
    ts = parseInt(ts);
    if (!ts)  return null;
    var date = new Date(ts * 1000);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var seconds = date.getSeconds();
    var formattedDate = year + '-' + pad(month) + '-' + pad(day);
    var formattedTime = pad(hours) + ':' + pad(minutes);
    //var dateh = date.toUTCString();
    return formattedDate + ' ' + formattedTime;
}

function timestampToGCDate(ts) {
    ts = parseInt(ts);
    if (!ts) return null;
    var date = new Date(ts * 1000);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    var h = date.getHours();
    var m = date.getMinutes();
    var s = date.getSeconds();
    return {'year': year, 'month': pad(month), 'day': pad(day), 'h': pad(h), 'm': pad(m), 's': pad(s)};
}


function htmlDateTimeSelect(ts, fname) {
    /**
     right days per month on month select
     slider
     sync to now
     show date in server TZ not in client
     restore initial date before edit
     show changed delta
     in header to live clocks - server, client (if differ) + select clock as work time - server or client (display dates in php list, date selected)
     onchange ts datasource change selects

     http://www.tigir.com/javascript_select.htm
     */
    dateo = timestampToGCDate(ts);
    //console.log(ts, dateo);
    var fnamecal = fname + '_cal';
    var calYear = new Element('select', {'class': 'dateSelectYear dateSelect'});
    var maxyear = parseInt(new Date().getFullYear()) + 2;
    for (var i = 2000; i <= maxyear; i++) {
        jp = pad(i);
        var year = new Element('option', {'value': jp, 'text': jp}).inject(calYear);
        if (jp == dateo.year) year.selected = true;
    }
    var calMon = new Element('select', {'class': 'dateSelectMonth dateSelect'});
    for (var i = 1; i <= 12; i++) {
        jp = pad(i);
        var month = new Element('option', {'value': jp, 'text': jp}).inject(calMon);
        if (jp == dateo.month) month.selected = true;
    }
    var calDay = new Element('select', {'class': 'dateSelectDay dateSelect'});
    for (var i = 1; i <= 31; i++) {
        jp = pad(i);
        var day = new Element('option', {'value': jp, 'text': jp}).inject(calDay);
        if (jp == dateo.day) day.selected = true;
    }
    var calH = new Element('select', {'class': 'dateSelectH dateSelect'});
    for (var i = 0; i < 24; i++) {
        jp = pad(i);
        var h = new Element('option', {'value': jp, 'text': jp}).inject(calH);
        if (jp == dateo.h) h.selected = true;

    }
    var calM = new Element('select', {'class': 'dateSelectM dateSelect'});
    for (var i = 0; i < 60; i++) {
        jp = pad(i);
        var m = new Element('option', {'value': jp, 'text': jp}).inject(calM);
        if (jp == dateo.m) m.selected = true;
    }

    var calDiv = document.id(fnamecal);
    calDiv.adopt(calYear);
    calDiv.adopt(calMon);
    calDiv.adopt(calDay);
    calDiv.adopt(new Element('span', {'text': ' '}));
    calDiv.adopt(calH);
    calDiv.adopt(new Element('span', {'text': ':'}));
    calDiv.adopt(calM);

    $$('#' + fnamecal + ' .dateSelect').addEvent('change', function (o) {
        //console.log(o.target.selectedIndex);
        var calDiv = o.target.getParent();
        var tsds = document.id(calDiv.get('data-source'));
        //console.log('ts datasource',tsds);
        var dm = calDiv.getChildren('.dateSelectM')[0];
        //console.log('minSelect',dm.selectedIndex);
        var gcd = {
            'year': calDiv.getChildren('.dateSelectYear')[0].get('value').toInt(),
            'month': calDiv.getChildren('.dateSelectMonth')[0].selectedIndex + 1,
            'day': calDiv.getChildren('.dateSelectDay')[0].selectedIndex + 1,
            'h': calDiv.getChildren('.dateSelectH')[0].selectedIndex,
            'm': calDiv.getChildren('.dateSelectM')[0].selectedIndex,
            's': 0
        }
        //console.log(gcd);
        var newTS = GCDateToTimestamp(gcd);
        var timeChangedDelta = newTS - tsds.get('value');
        //console.log(newTS, timeChangedDelta);
        tsds.set('value', newTS);
        //console.log('ts datasource new ',tsds);
    });

}

/*
 Date.prototype.toUTCArray= function(){
 var D= this;
 return [D.getUTCFullYear(), D.getUTCMonth(), D.getUTCDate(), D.getUTCHours(),
 D.getUTCMinutes(), D.getUTCSeconds()];
 }

 Date.prototype.toISO= function(){
 var tem, A= this.toUTCArray(), i= 0;
 A[1]+= 1;
 while(i++<7){
 tem= A[i];
 if(tem<10) A[i]= '0'+tem;
 }
 return A.splice(0, 3).join('-')+'T'+A.join(':');
 }
 */


/*
 // @jstz.min.js
 console.log(timezone);
 var timezoneOffsetStr = timezone.offset(); // Standard UTC offset for timezone
 console.log(timezoneOffsetStr, timezoneName, timezoneIsDST);
 var timezone = jstz.determine_timezone();
 var timezoneName = timezone.name();
 var timezoneIsDST = timezone.dst();
 */

/*
 var hour = a.getUTCHours();
 var min = a.getUTCMinutes();
 var sec = a.getUTCSeconds();
 */



/*
 var today = new Date();
 console.log( today.getTimezoneOffset() );
 console.log(today.toGMTString());

 unix_timestamp = 1323118901;
 var date = new Date(unix_timestamp*1000);
 var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
 var year = date.getFullYear();
 //var month = months[date.getMonth()];
 var month = date.getMonth()+1;
 var day = date.getDate();
 var hours = date.getHours();
 var minutes = date.getMinutes();
 var seconds = date.getSeconds();
 var formattedTime = pad(hours) + ':' + pad(minutes);
 var formattedDate = year + '-' + pad(month) + '-' + pad(day);
 console.log(formattedDate);
 console.log(formattedTime);
 */
