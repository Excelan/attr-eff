var MENUFN = {};


MENUFN['tested_qa'] = function clickMenuItemProcess(id)
{
    window.location.href = "/questionnaire/"+id;
};

MENUFN['tested_qa_result'] = function clickMenuItemProcess(id)
{
    window.location.href = "/questionnaire/result/"+id;
};


MENUFN['view_complaint'] = function clickMenuItemProcess(id)
{
    window.location.href = "/complaint/view/" + id;
};

MENUFN['view_capa_defore_confirm'] = function clickMenuItemProcess(id)
{
    window.location.href = "/capa/confirmproblemsbyboss/" + id;
};

MENUFN['set_realization'] = function clickMenuItemProcess(id)
{
    window.location.href = "/capa/setrealization/" + id;
};

MENUFN['view_authormatching_capa'] = function clickMenuItemProcess(id)
{
    window.location.href = "/capa/authormatching/" + id;
};

MENUFN['vising'] = function clickMenuItemProcess(id)
{
    window.location.href = "/capa/vising/" + id;
};

MENUFN['approving'] = function clickMenuItemProcess(id)
{
    window.location.href = "/capa/approving/" + id;
};


MENUFN['view_sop'] = function clickMenuItemProcess(id)
{
    window.location.href = "/home/viewsop/"+id;
};

MENUFN['view_newprot'] = function clickMenuItemProcess(id)
{
    window.location.href = "/home/newproto/"+id;
};

MENUFN['view_object'] = function clickMenuItemProcess(id)
{
    window.location.href = "/object/view/"+id;
};

MENUFN['view_approvedrisk'] = function clickMenuItemProcess(id)
{
    window.location.href = "/risk/viewapproved/"+id;
};

MENUFN['view_notapprovedrisk'] = function clickMenuItemProcess(id)
{
    window.location.href = "/risk/viewnotapproved/"+id;
};

MENUFN['view_capatask_details'] = function clickMenuItemProcess(id)
{
    window.location.href = "/task/detail/"+id;
};

MENUFN['view_selfinspection_details'] = function clickMenuItemProcess(id)
{
    window.location.href = "/selfinspection/details/"+id;
};





GC.CALLBACKS['alertresult'] = function(data){
    alert(JSON.stringify(data));
};

GC.CALLBACKS['alertresult2'] = function(data){
    alert(JSON.stringify(data));
};

GC.CALLBACKS['solutionDone'] = function(data) {
    //window.location.reload();
};

GC.CALLBACKS['Hello'] = function(data){
    alert(data);
    window.location.reload();
};

GC.CALLBACKS['HelloError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['documentGenerate'] = function(data){
    console.log(data.doc_path.doc_path);
    window.location.href = data.doc_path.doc_path;
};
GC.CALLBACKS['documentGenerateError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['UpdateSolutionDone'] = function(data){
   if(data['status'] == 501){
       notification('GOOD', 'СОХРАНЕНО');
   }else notification('BAD', data['text']);
};


GC.CALLBACKS['changeCommentStatus'] = function(data){
    //console.log(data);
    if(data['idCapa']) var commentBlock = document.getElementById(data['idCapa']);
    else var commentBlock = document.getElementById('commentsid');

    var m = {};
    m.level = data['level'];
    m.urn = data['urn'];
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {
        commentBlock.innerHTML = d;
        //console.log(d);
        displayCommentButton();
        managedFormsProcessor();
    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax('/getcomment?urn='+data['urn']+'&level='+data['level']+'&idCapa='+data['idCapa'],
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen,
            'responseType' : 'plain'
        },
        'GET',
        postdata['responseType'] == 'plain'

    );

};



GC.CALLBACKS['addComment'] = function(data){
    //console.log(data);
    if(data['idCapa']) var commentBlock = document.getElementById(data['idCapa']);
    else var commentBlock = document.getElementById('commentsid');

    var m = {};
    m.level = data['level'];
    m.urn = data['urn'];
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {
        commentBlock.innerHTML = d;
        //console.log(d);
        displayCommentButton();
        managedFormsProcessor();
    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax('/getcomment?urn='+data['urn']+'&level='+data['level']+'&idCapa='+data['idCapa'],
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen,
            'responseType' : 'plain'
        },
        'GET',
        postdata['responseType'] == 'plain'

    );

};

GC.CALLBACKS['capaCreate'] = function(data){
    console.log(data);
    window.location.href = '/capa';
};
GC.CALLBACKS['capaCancel'] = function(data){
    console.log(data);
    alert('Капа отменена !');
    window.location.href = "/capa";
};

GC.CALLBACKS['capa_visauser_add'] = function(data){

    console.log(data);

    var visauserListing = document.getElementById('visauserListing');

    var f = createFrag(data['visauser_html']);
    prependChildToParent(f, visauserListing);

    var popup_el = document.getElementById('popup');
    popup_el.innerHTML = '';
};
GC.CALLBACKS['capaSetRealization'] = function(data){
    window.location.href = '/capa/needtosetrealization';
};
GC.CALLBACKS['ConfirmEventsByDepartmentBoss'] = function(data){
    window.location.href = '/capa/needconfirmofdepartmentboss';
};
GC.CALLBACKS['SendToVising'] = function(data){
    window.location.href = '/capa/needtoauthormatching';
};


GC.CALLBACKS['capaCreateError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};


GC.CALLBACKS['userLogin'] = function(data){
    if(data['status'] == 200){
        notification('GOOD', 'Авторизовано');

        setTimeout(function(){
            notification('GOOD', 'Перенаправление');
        },2000);

        setTimeout(function(){window.location.href = data['redirect'];},4000);
    }
};

GC.CALLBACKS['userLoginError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
    notification('BAD', 'Ошибка входа');
};

GC.CALLBACKS['userChangepassword'] = function(data){
    if(data['status'] == 501){
        notification('GOOD', 'Пароль изменен');

        setTimeout(function(){
            notification('GOOD', 'Перенаправление');
        },2000);

        setTimeout(function(){window.location.href = data['redirect'];},4000);
    }
    else if(data['status'] == 404) notification('BAD', 'Ошибка смена пароля. Проверьте правильность ввода');
    else notification('OTHER', 'Неизвестная ошибка');
};

GC.CALLBACKS['userChangepasswordError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
    //window.location.href = data['redirect'];
};


GC.CALLBACKS['userRegister'] = function(data){
    window.location.href = data['redirect'];
};

GC.CALLBACKS['userForgot'] = function(data){


    if(data['status'] == 200){
        notification('GOOD', 'Новый пароль выслан Вам на почту');

        setTimeout(function(){
            notification('GOOD', 'Перенаправление');
        },2000);

        setTimeout(function(){window.location.href = data['redirect'];},4000);
    }else if(data['status'] == 301){
        notification('BAD', 'Ошибка. Не указан Email');
    }else if(data['status'] == 404){
        notification('BAD', 'Ошибка. Проверьте правильность ввода');
    }


};

GC.CALLBACKS['toVised'] = function(data){
    console.log(data);
    if(data['status'] == 501){
        notification('GOOD', 'Отправлено на визирование');

        setTimeout(function(){
            notification('GOOD', 'Перенаправление');
        },2000);

        setTimeout(function(){window.location.href = data['redirect'];},4000);
    }else{
        notification('BAD', 'Ошибка. Проверьте правильность ввода');
    }
};

GC.CALLBACKS['toVisedError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};


GC.CALLBACKS['createComplaint'] = function(data){
    window.location.href = '/complaint';
};

GC.CALLBACKS['reply_comment'] = function(data){
    window.location.reload();
};

GC.CALLBACKS['createSop'] = function(data){
    window.location.href = '/home/soppp';
};

GC.CALLBACKS['create_personal_copy_and_pdf'] = function(data){

    var segmTop = document.getElementById('tosatelement');
    segmTop.style.display = 'none';

    var win = window.open(data['files_pdf'][0]['path'], '_blank');
    if(win){
        //Browser has allowed it to be opened
        win.focus();
    }else{
        //Broswer has blocked it
        alert('Please allow popups for this site');
    }

};

GC.CALLBACKS['createComplaintError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};


GC.CALLBACKS['ComplaintResolved'] = function(data){

    var idResolt =  document.getElementById('idResolt');
    idResolt.style.display = 'none';

};

GC.CALLBACKS['ErrorComplaintResolved'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};


GC.CALLBACKS['CreateProtocol'] = function(data){
    window.location.href = '/home/newproto';

};

GC.CALLBACKS['objectCreate'] = function(data){
    window.location.href = '/object';

};

GC.CALLBACKS['riskCreate'] = function(data){
    window.location.href = '/risk';

};

GC.CALLBACKS['notApprovedRiskCreate'] = function(data){
    window.location.href = '/risk/notapproved';

};

GC.CALLBACKS['CreateProtocolError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};


GC.CALLBACKS['UpdateProtocol'] = function(data){
    console.log(data);
    window.location.reload();
};

GC.CALLBACKS['UpdateProtocolError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['SendForVising'] = function(data){
    console.log(data);
    window.location.href = '/home/visingproto';
};

GC.CALLBACKS['noviseDoc'] = function(data){
    console.log(data);
    window.location.reload();
};

GC.CALLBACKS['noviseDocError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['yesviseDoc'] = function(data){
    console.log(data);
    window.location.href = '/home/aproveprotocol';
};

GC.CALLBACKS['yesviseDocError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['noapproveDoc'] = function(data){
    console.log(data);
    window.location.reload();
};

GC.CALLBACKS['noapproveDocError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['yesapproveDoc'] = function(data){
    console.log(data);
    window.location.href = '/home/liveprotocol';
};

GC.CALLBACKS['yesapproveDocError'] = function(e, data, sent){
    console.log(e);
    console.log('DATA TO SEND');
    console.log(sent);
    console.log(data);
};

GC.CALLBACKS['RemoveNotApprovedDoc'] = function(data){
    console.log(data);
    window.location.reload();
};

GC.CALLBACKS['saveProt'] = function(data){
    console.log(data);
    alert('OK');
    //window.location.reload();
};

GC.CALLBACKS['complete_task'] = function(data){
    console.log(data);
    window.location.href = '/task';
};

GC.CALLBACKS['createagreement'] = function(data){
    console.log(data);
    alert('OK');
};

