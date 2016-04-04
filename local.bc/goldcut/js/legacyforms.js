/**
 FORM ON SUBMIT
 clear form after submit
 */

var formError = function (error) {
    console.log(error)
    alert(error);
}

var formFailure = function (xhr) {
    var errorCode = xhr.status
    var error = JSON.decode(xhr.responseText)
    var binded = this[0];
    // console.log(error.text, error.status);
    if (error.status == 401) {
        window.location.href = error.text;
        error.text = 'Только для авторизованных пользователей';
    }

    // TODO NEED ADD PASS params other then status, error
    /*
     report = error;
     // --d
     if (report.debug) console.log(report);
     if (!report.noretry)
     {
     binded.controlbutton.disabled = false
     //binded.controlbutton.disabled = 0
     //binded.controlbutton.erase('disabled')
     binded.controlbutton.removeProperty('disabled');
     }
     if (report.reload) window.location.reload()
     if (report.download) window.location.href = report.download;
     if (report.text) binded.controlinfo.set('text', report.text)
     if (report.clear)
     {
     binded.form.reset()
     }
     // --d
     */

    binded.controlstatus.dispose()
    binded.controlbutton.disabled = false
    //binded.controlbutton.disabled = 0
    //binded.controlbutton.erase('disabled')
    binded.controlbutton.removeProperty('disabled');
    binded.controlinfo.set('text', error.text)
    binded.controlinfo.removeClass('infogood')
    binded.controlinfo.addClass('infobad')
    if (window['gconformfail']) window['gconformfail'](error);
}

var formSuccess = function (report) {
    var binded = this[0];
    binded.controlstatus.dispose()

    // --d
    if (report.debug) console.log(report);
    if (!report.noretry) {
        binded.controlbutton.disabled = false
        //binded.controlbutton.disabled = 0
        //binded.controlbutton.erase('disabled')
        binded.controlbutton.removeProperty('disabled');
    }
    if (report.reload) window.location.reload()
    if (report.download) window.location.href = report.download;
    if (report.text) binded.controlinfo.set('text', report.text)
    if (report.clear) {
        binded.form.reset()
    }
    // --d

    binded.controlinfo.addClass('infogood')
    binded.controlinfo.removeClass('infobad')
    if (report.callback && window[report.callback]) window[report.callback](report);
    if (report.redirect) window.location.href = report.redirect
}


var takeFormControl = function (e) {
    e.stop();
    var send = {}
    var submit = this.getElements('input[type=submit]')[0]
    var formcontrols = this.getElements('.formcontrols]')[0]
    var controlinfo = formcontrols.getElements('.controlinfo]')[0]
    var controlstatus = formcontrols.getElements('.controlstatus]')[0]
    var controlstatus = new Element('img', {'src': '/goldcut/assets/img/ajax-loader.gif'}).inject(controlstatus);
    var target = this.get('action')
    var binded = {controlbutton: submit, controlinfo: controlinfo, controlstatus: controlstatus, form: this}

    //console.log(target)
    submit.disabled = true
    //console.log(submit)

    var fields = this.getElements('select')
    fields.each(function (fs) {
        var name = fs.get('name');
        //console.log( fs.get('type'),  fs.get('name'), fs.get('value') );
        if (fs.get('type') == 'select-multiple') {
            name = fs.get('name').split('[]').join('');
            fs.getSelected().each(function (el) {
                if (!send[name]) send[name] = [];
                send[name].push(el.get('value'));
            });
        }
        else {
            send[name] = fs.get('value');
        }
    });


    var fields = this.getElements('textarea')
    fields.each(function (fs) {
        var name = fs.get('name');
        var areaId = fs.get('id');
        if (fs.hasClass('richtext')) {
            send[name] = tinyMCE.get(areaId).getContent();
        }
        else if (!fs.hasClass('changemon')) {
            send[name] = fs.get('value');
        }
    });

    var fields = this.getElements('input[name]')
    fields.each(function (fs) {
        var name = fs.get('name');
        //console.log( fs.get('type'),  fs.get('name'), fs.get('value') );
        if (fs.get('type') == 'checkbox') {
            if (fs.get('checked')) {
                if (!send[name]) send[name] = [];
                //console.log(send[name]);
                send[name].push(fs.get('value'))
            }
        }
        else if (fs.get('type') == 'radio') {
            if (fs.get('checked')) {
                if (!send[name]) send[name] = [];
                //console.log(send[name]);
                send[name] = fs.get('value');
            }
        }
        else
            send[name] = fs.get('value')
    });
    //console.log(send);
    controlinfo.set('text', ''); // info area text clear
    new Request.JSON({
        url: target,
        onSuccess: formSuccess.bind([binded]),
        onError: formError,
        onFailure: formFailure.bind([binded])
    }).post(send);
    return false;
}

// USED IN DOUBLE BUTTONS!
try {
    window.addEvent('domready', function () {
        $$('.gcform').each(function (form) {
            form.addEvent('submit', takeFormControl)
        });
        $$('.gcform input[type=submit]').addEvent('click', function (e) {
            var submit = this;
            var f = submit.getParent('form').getChildren('input[name=action]');
            var redefineInputHiddenAction = submit.get('data-action');
            if (redefineInputHiddenAction) {
                f.set('value', submit.get('data-action'));
                console.log('redefined input name action', f, submit);
            }
        });
        //if (document.id('member-login')) document.id('member-login').addEvent('submit', takeFormControl);
    });
} catch (e) {

}

