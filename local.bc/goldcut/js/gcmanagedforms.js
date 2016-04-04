/*
 <form action="/echopost" data-managedform="yes" data-onsuccess="alertresult1" data-onerror="alerterror">
 <input data-selector="text" type="text" name="text" value="123" required="required">
 <fieldset data-struct="personal">
 <legend>Personal information:</legend>
 <input data-selector="personal-sex" type="radio" name="sex" value="male" checked>Male
 <br>
 <input data-selector="personal-sex" type="radio" name="sex" value="female">Female
 <legend>Check it:</legend>
 <input data-selector="personal-more" type="checkbox" name="opt1" value="option1" checked>Opt1
 <br>
 <input data-selector="personal-more" type="checkbox" name="opt2" value="option2">Opt2
 </fieldset>
 <select data-selector="selectone" name="selectone">
 <option value="item1">Item1</option>
 <option value="item2">Item2</option>
 </select>
 <textarea data-selector="comment" name="comment" rows="5" cols="40">Textarea</textarea>
 <div data-multiplestruct="purchase">
 <div data-struct="purchase" data-array="item">
 <input data-selector="purchase-text" type="text" name="text" value="old1.1">
 </div>
 <div data-struct="purchase" data-array="item">
 <input data-selector="purchase-text" type="text" name="text" value="old1.2">
 <input data-selector="purchase-more" type="checkbox" name="opt1" value="option1inner">INNER OPT
 </div>
 </div>
 <input type="submit" value="Submit">
 </form>
 <form action="/System/GetUser" data-managedform="yes" data-onsuccess="alertresult2" data-onerror="alerterror">
 <input data-selector="options" type="text" name="options" value="options DATA">
 <input type="submit" value="Submit">
 </form>
 */
function recForm(form, context, onlymarked) {
    // console.log('>> ENTRY', context);
    //console.log(form);
    var changedData = {};
    // FIELDS selectors this level
    var inputs = form.querySelectorAll('[data-selector]');
    //console.log(inputs);

    // SELECTORS
    for (var i = 0; i < inputs.length; i++) {
        // if (onlymarked && inputs[i].getAttribute('data-mark') && inputs[i].getAttribute('data-mark').split(',').indexOf(onlymarked) == -1) continue;
        if (onlymarked && inputs[i].getAttribute('data-mark') != onlymarked) continue;
        var value = undefined;
        var selectors = inputs[i].getAttribute('data-selector').split('-');
        // SKIP NOT CURRENT LEVEL
        if (selectors.length > context.length + 1) { /*console.log('-skip',selectors);*/
            continue;
        }
        //else console.log(selectors);
        //console.log(inputs[i]);
        if (inputs[i].type == 'checkbox') {
            if (inputs[i].checked) {
                value = inputs[i].value;
                var locval = value;
                if (inputs[i].getAttribute('data-type') == 'boolean') locval = true;
                changedData[selectors[selectors.length - 1]] = locval; // 1
            } else {
                var locval = value;
                if (inputs[i].getAttribute('data-type') == 'boolean') {
                    locval = false;
                    changedData[selectors[selectors.length - 1]] = locval; // 1
                } else {
                    // не передавать параметр (если передавать, он будет = undefined)
                }
            }
        } else if (inputs[i].type == 'radio') {
            // TODO если ни один не выбран
            if (inputs[i].checked) {
                value = inputs[i].value;
                changedData[selectors[selectors.length - 1]] = value; // 1
            }
        } else if (inputs[i].type == 'select') {
            var selectedIndex = inputs[i].selectedIndex;
            value = inputs[i].options[selectedIndex].value;
            if (value != 'NULL')
              changedData[selectors[selectors.length - 1]] = value; // 1
        } else if (inputs[i].type == 'textarea' && hasClass(inputs[i], 'richtext')) {
          try {
            value = tinyMCE.get(inputs[i].getAttribute('id')).getContent();
            changedData[selectors[selectors.length - 1]] = value; // 1
          } catch (e) { console.log(e) }
        }
        else  // OTHER INPUTS
        {
            if (inputs[i].tagName == 'DIV')
            {
                //console.log(inputs[i]);
                value = inputs[i].innerHTML;
            }
            else {
                value = inputs[i].value;
            }

            if (inputs[i].getAttribute("data-multiple"))
            {
                if (!Array.isArray(changedData[selectors[selectors.length - 1]])) changedData[selectors[selectors.length - 1]] = [];
                changedData[selectors[selectors.length - 1]].push(value); // 1
            }
            else
                changedData[selectors[selectors.length - 1]] = value; // 1

        }
        //console.log(selectors.join('/'), '=', value);
    }

    // STRUCTS SINGLE (// data-struct), this level
    var oo = form.querySelectorAll('[data-struct]');
    for (var i = 0; i < oo.length; i++) {
        if (onlymarked && inputs[i].getAttribute('data-mark') != onlymarked) continue;
        if (oo[i].getAttribute('data-array') == 'item') continue; // skip array item structs
        ooa = oo[i].getAttribute('data-struct').split('-');
        if (ooa.length > context.length + 1) {
            //console.log('skip', ooa);
            continue;
        } // skip next deep level
        //else console.log(ooa, ooa.length, '--', context, context.length);
        //console.log('SS 1');
        //var d = recForm(oo[i], [ooa[0]]);
        var d = recForm(oo[i], ooa, onlymarked);
        //console.log('pre check',d)
        if (!Object.keys(d).length) continue;
        //console.log('AFTER check',d)
        changedData[ooa[context.length]] = d; // 1.1
    }

    // STRUCTS ARRAY (no singles, // data-multiplestruct / data-struct), this level

    var ooc = form.querySelectorAll('[data-multiplestruct]');
    for (var ic = 0; ic < ooc.length; ic++) {
        var oo = ooc[ic].querySelectorAll('[data-struct]');
        for (var i = 0; i < oo.length; i++) {
            //console.log(oo[i]);
            if (onlymarked && inputs[i].getAttribute('data-mark') != onlymarked) continue;
            ooa = oo[i].getAttribute('data-struct').split('-');
            if (ooa.length > context.length + 1) {
                //console.log('skip', ooa);
                continue;
            }
            if (oo[i].getAttribute('data-array') != 'item') throw 'Using multiplestruct without [data-array="item"] for inner structs';
            //console.log('SA[]');
            var d = recForm(oo[i], ooa, onlymarked);
            //console.log(d)
            if (!Object.keys(d).length) continue;
            if (!changedData[ooa[context.length]]) changedData[ooa[context.length]] = [];
            changedData[ooa[context.length]].push(d); // 1.1[]
        }
    }
    return changedData;
}

var alertresult = function(data) {
    //console.log('DEFAULT CALLBACK: AJAX RETURNED DATA (gcdom.js default. Set form[data-onsuccess], GC.CALLBACKS[formOnSuccess])');
    // TODO RELOAD IN PRODUCTION
    fadeScreen.call();

    if( data !== undefined && data['nextstage'] == 404){
        console.log(data);
        notification('BAD', data['text']);
        unFadeScreen();
        return;
    }

    notification('GOOD', 'Сохранение');
    setTimeout(function(){
        window.location.reload();
    },1000);

    //window.location.href = '/inbox';
    console.log(data);
};

var alerterror = function(errcode, errdata) {
    //console.log('DEFAULT CALLBACK ERROR: (gcdom.js default. Set form[data-onerror], GC.CALLBACKS[formOnError])');
    console.log(errcode);
    console.log(errdata);
};

// EXTERNAL FROM FORM SUBMIT IT (a[data-forform])
var managedFormExternalSubmitsProcessor = function() {
    var managedForms = document.querySelectorAll('a[data-forform]');
    for (var i = 0; i < managedForms.length; i++) {
        var but = managedForms[i];
        Event.add(but, "click", function(e) {
            e.preventDefault();
            var targetForm = this.getAttribute('data-forform');
            //console.log(targetForm);
            var extform = document.querySelector('form[data-formid="'+targetForm+'"]');
            //console.log(extform);
            //extform.submit();
            submitFN.bind(extform).call();
            //document.forms[0].submit();
        });
    }
}
GC.ONLOAD.push(managedFormExternalSubmitsProcessor);


//отправка всех форм с data-allform
var managedFormExternalSubmitsProcessorAll = function()
{
    var amanagedForms = document.querySelectorAll('a[data-allform]');
    for (var i = 0; i < amanagedForms.length; i++) {
        var but = amanagedForms[i];
        console.log('ALL FORM', but);
        Event.add(but, "click", function (e) {
            var managedForms = document.querySelectorAll('form[data-allform]');
            var c=0;
            function timedCount()
            {
                var extform = managedForms[c];
                submitFN.bind(extform).call();
                c=c+1;
                if(c < managedForms.length){
                    setTimeout(function(){
                        timedCount();
                    },250);
                }
            }
            timedCount();
        });
    }
};
GC.ONLOAD.push(managedFormExternalSubmitsProcessorAll);



var managedButtonsProcessor = function() {
    var managedForms = document.querySelectorAll('[data-managedbutton]');
    for (var i = 0; i < managedForms.length; i++) {
        var but = managedForms[i];
        Event.add(but, "click", function(e) {
            e.preventDefault();
            var actionGate = this.getAttribute('data-gate');
            var actionPayload;
            var objectPayload = {};
            if (this.getAttribute('data-payload')) {
                actionPayload = this.getAttribute('data-payload');
                if (this.getAttribute('data-format') == 'json') {
                    objectPayload = JSON.parse(actionPayload);
                } else {
                    var datakey = this.getAttribute('data-key')
                    objectPayload[datakey] = actionPayload;
                }
            } else if (this.getAttribute('data-valuereference')) {
                var vr = this.getAttribute('data-valuereference');
                var vrel = document.querySelector('[data-selector="' + vr + '"]');
                objectPayload[vr] = vrel.value;
            } else {
                throw new Exception('No data-payload or data-valuereference');
            }
            console.log(objectPayload)

            var formOnSuccess = this.getAttribute('data-onsuccess');
            var formOnError = this.getAttribute('data-onerror');
            var postdata = {
                'json': JSON.stringify(objectPayload)
            };
            var alertresultReal = GC.CALLBACKS[formOnSuccess] ? GC.CALLBACKS[formOnSuccess] : alertresult;
            var alerterrorReal = GC.CALLBACKS[formOnError] ? GC.CALLBACKS[formOnError] : alerterror;

            console.log('BUTTON CLICK');
            //console.log(postdata);

            ajax(actionGate, alertresultReal, {
                'onError': function(e, d) {
                    unFadeScreen.call();
                    alerterrorReal.call(this, e, d, changedData)
                },
                'onStart': fadeScreen,
                'onDone': unFadeScreen
            }, 'POST', postdata);


        });
    }
}
GC.ONLOAD.push(managedButtonsProcessor);


var managedFormsProcessor = function() {
    var managedForms = document.querySelectorAll('[data-managedform]');
    for (var i = 0; i < managedForms.length; i++) {
        var form = managedForms[i];
        var inputs = form.querySelectorAll('input[type="submit"]');
        for (var j = 0; j < inputs.length; j++) {
            var input = inputs[j];
            //var marked = input.getAttribute('data-mark');
            //if (!marked) continue;
            Event.add(input, "click", function(e) {
                //console.log('CLICK');
                //e.preventDefault();
                var origin = this.form.action;
                var marked = this.getAttribute('data-mark');
                var altGate = this.getAttribute('data-gate');
                var altOnSuccess = this.getAttribute('data-onsuccess');
                var altOnError = this.getAttribute('data-onerror');
                if (marked) {
                    //console.log('SET Marked', marked)
                    // set temp alt
                    this.form.action = altGate;
                    var originOnSuccess = this.form.getAttribute('data-onsuccess');
                    var originOnError = this.form.getAttribute('data-onerror');
                    this.form.setAttribute('data-onsuccess', altOnSuccess);
                    this.form.setAttribute('data-onerror', altOnError);
                    // set marked
                    this.form.setAttribute('data-mark', marked);
                    // set origin gate, on_
                    this.form.setAttribute('data-origin', origin);
                    if (altOnSuccess) {
                        this.form.setAttribute('data-originOnSuccess', originOnSuccess);
                        //console.log('set originOnSuccess', this.form.getAttribute('data-originOnSuccess'));
                    }
                    if (altOnError) this.form.setAttribute('data-originOnError', originOnError);
                }
                //console.log(this.form.action);
            });
        }
    }

    var managedForms = document.querySelectorAll('[data-managedform]');
    for (var i = 0; i < managedForms.length; i++) {
        var form = managedForms[i];
        //var submit = form.querySelectorAll('input[type="submit"]');
        Event.add(form, "submit", submitFN);

    }
};
GC.ONLOAD.push(managedFormsProcessor);

// MAIN SUBMIT FORM
var submitFN = function(e)
{
    // TODO before submit callback
    //console.log('SUBMIT');
    if (e) e.preventDefault();

    var formAction = this.action;
    var formOnSuccess = this.getAttribute('data-onsuccess');
    var formOnError = this.getAttribute('data-onerror');
    var origin = this.getAttribute('data-origin');
    var marked = undefined;
    //console.log('Origin',origin)
    if (origin) {
        // restore
        // console.log('RESTORE from Marked')
        this.action = origin;
        // console.log('alt on s', this.getAttribute('data-altOnSuccess'))
        // console.log('main on s', this.getAttribute('data-onsuccess'))
        if (this.getAttribute('data-originOnSuccess'))
        {
            // console.log('C,O',this.getAttribute('data-onsuccess'), this.getAttribute('data-originOnSuccess'))
            this.setAttribute('data-onsuccess', this.getAttribute('data-originOnSuccess'));
        }
        else {
            // console.log('----NO')
        }
        if (this.getAttribute('data-originOnError')) this.setAttribute('data-onerror', this.getAttribute('data-originOnError'));
        var marked = this.getAttribute('data-mark');
        this.removeAttribute('data-mark');
        this.removeAttribute('data-origin');
        this.removeAttribute('data-originOnSuccess');
        this.removeAttribute('data-altOnError');
    }
    var formDebug = this.getAttribute('data-debug');
    var formGoTo = this.getAttribute('data-goto');


    var changedData = recForm(this, [], marked); // 0

    if (this.getAttribute('data-legacycontrol') == 'yes')
        var postdata = changedData;
    else
    {
        var postdata = { 'json': JSON.stringify(changedData) };
        if(id('ticketurn'))postdata.ticket = id('ticketurn').value;
    }

    var alertresultReal = GC.CALLBACKS[formOnSuccess] ? GC.CALLBACKS[formOnSuccess] : alertresult;

    if (formGoTo) {
        var oldresultfn = alertresultReal;
        alertresultReal = function(data) {
            fadeScreen.call();
            if( data !== undefined && data['nextstage'] == 1){

                console.log('Go to nextstage');
                notification('GOOD', 'Переход');
                actionCompleteStage();
            }else if( data !== undefined && data['nextstage'] == 404){
                notification('BAD', data['text']);
                unFadeScreen();
                return;
            }
            oldresultfn.call(this);
            setTimeout(function(){
                window.location.href = formGoTo;
            },1000);

            console.log(data);
        }
    }

    if (formDebug) {
        var formOnSuccessDebug = formOnSuccess;
        if (!GC.CALLBACKS[formOnSuccess]) {
            formOnSuccessDebug += '!';
            console.log('NO SUCCESS CALLBACK ' + formOnSuccess);
        }
        var formOnErrorDebug = formOnError;
        if (!GC.CALLBACKS[formOnError]) {
            formOnErrorDebug += '!';
            console.log('NO ERROR CALLBACK ' + formOnError);
        }
        var d = [formAction, formOnSuccessDebug, formOnErrorDebug];
        if (formGoTo) {
            d.push(formGoTo);
            console.log('GOTO ' + formGoTo);
        }
        console.log('GATE DEBUG ' + d.join(', '));
        console.log(changedData);
        if (id('debuggate')) id('debuggate').value = JSON.stringify(changedData);
        return; // !!!
    }

    var alerterrorReal = GC.CALLBACKS[formOnError] ? GC.CALLBACKS[formOnError] : alerterror;

    if (window.location.hash != '#noreload')
    {
      //alert(window.location.hash);
      ajax(formAction, alertresultReal, {
          'onError': function(e, d) {
              unFadeScreen.call();
              alerterrorReal.call(this, e, d, changedData)
          },
          'onStart': fadeScreen,
          'onDone': unFadeScreen
      }, 'POST', postdata);
    }
    else {
      console.log('SUBMIT');
      console.log(changedData);
    }

}



function getSelectedText() {
    var txt = '';
    if (window.getSelection) {
        txt = window.getSelection();
    } else if (document.getSelection) // FireFox
    {
        txt = document.getSelection();
    } else if (document.selection) // IE 6/7
    {
        txt = document.selection.createRange().text;
    } else return;
    return txt;
}

// -- radio button set, get, get title

function getCheckedTitle(radioObj) {
    if (!radioObj) return null;
    var radioLength = radioObj.length;
    if (radioLength == undefined)
        if (radioObj.checked)
            return radioObj.value;
        else
            return null;
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked || radioObj[i].selected) {
            if (document.all)
                return radioObj[i].innerText;
            else
                return radioObj[i].textContent;
        }
    }
    return null;
}

// for radio buttons / checkboxes
function getCheckedValue(radioObj) {
    if (!radioObj)
        return null;
    var radioLength = radioObj.length;
    if (radioLength == undefined)
        if (radioObj.checked)
            return radioObj.value;
        else
            return null;
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked || radioObj[i].selected) {
            return radioObj[i].value;
        }
    }
    return null;
}

function setCheckedValue(radioObj, newValue) {
    if (!radioObj) return;
    var radioLength = radioObj.length;
    if (radioLength == undefined) {
        radioObj.checked = (radioObj.value == newValue.toString());
        return;
    }
    for (var i = 0; i < radioLength; i++) {
        radioObj[i].checked = false;
        if (radioObj[i].value == newValue.toString()) {
            if (radioObj[i].selected != undefined) radioObj[i].selected = true;
            else if (radioObj[i].checked != undefined) radioObj[i].checked = true;
        }
    }
}
