//load option in select for modal window

var processStarted = function (d) {
    console.log(d);
    notification('GOOD', 'Процес начат');
    closeModalWindow('close');
    setTimeout(function() {
        window.location.href = '/inbox';
    }, 250);
};


//сбор данных с модального окна
function collectform(){
    var submit = document.getElementById('submitwin');
    var submit2 = document.getElementById('submitwin2');

    Event.add(submit, 'click', function (e) {
        e.preventDefault();

        var cl = document.getElementById('selectid1');
        var type = document.getElementById('selectid2');
        var initiation = document.getElementById('c');
        var nonprocessed = document.getElementById('nonprocessedopt');
        var urn = document.getElementById('urnwin');

        var data = {};

        if(cl) data.class = cl.options[cl.selectedIndex].value;
        if(type) data.type = type.options[type.selectedIndex].value;
        if(initiation) data.initiation = initiation.checked; // TODO
        if (nonprocessed) data.nonprocessed = nonprocessed.checked; // TODO
        if(urn) data.urn = urn.value; // TODO

        //console.log(JSON.stringify(data));
        //var PROTO = 'Document'+':'+data.class+':'+data.type;
        var PROTO = data.type;
        var processProto = PROTO.split('/')[0];
        var subjectProto = PROTO.split('/')[1];
        var initiator = id('initiator').value;
        //console.log(PROTO)
        //console.log(initiator)
        if (!initiator) alert('Нет инициатора, сессия истекла');

        var alerterrorReal = function (e,d) {
            //console.log(e);
            //console.log(d);
            notification('BAD', 'Невозможно начать процесс');
        }

        if (data.nonprocessed)
        {
          // создание универсального документа без старта процесса
          console.log('DIRECT CREATE', subjectProto);
          var data = {subjectProto: subjectProto};
          var directCreated = function(d) {
            unFadeScreen.call();
            window.location.href = '/universaldocument/'+d.code
          }
          ajax("/DMS/UniversalDocument/CreateDirect", directCreated, {'onError': unFadeScreen, 'onStart': fadeScreen, 'onDone': debuginput}, 'POST', data);
        }
        else {
          console.log('PROCESS START', subjectProto);
          ajax('http://'+document.location.hostname+':8020/startprocess/?prototype='+processProto+'&subjectPrototype='+subjectProto+'&initiator='+initiator+'&parenturn='+data.urn, processStarted, {
              'onError': function(e, d) {
                  //console.log(e, d);
                  unFadeScreen.call();
                  alerterrorReal.call(this, e, d)
              },
              'onStart': fadeScreen,
              'onDone': function() { unFadeScreen(); }
          }, 'GET');
        }

    });

    /// ????
    Event.add(submit2, 'click', function (e) {
        e.preventDefault();


        var initiation2 = document.getElementById('c2');
        var urn2 = document.getElementById('urnwin2');
        var cause = document.getElementById('causeNew');

        var data = {};

        if(initiation2) data.newversion = initiation2.checked;
        if(urn2) data.urn = urn2.value;
        if(cause) data.cause = cause.value;

        console.log(JSON.stringify(data));

    });
}


function selectchange(a,b) {

    var dataLink1 = b.getAttribute('data-link1');
    var dataLink2 = b.getAttribute('data-link2');

    var m = {};
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {

        if (!d) throw "No data in first select ajax response";
        //console.log('I CAN START', d)

        var sel1 = document.getElementById('selectid1');

        var oparr = [];
        oparr.push("<option selected='selected'>Не выбрано</option>");

        // 1st level
        for (var i = 0; i < d.length; i++) {
            for (var prop in d[i]) {
                //console.log(d[i][prop]);
                oparr.push("<option value='"+prop+"'>" + d[i][prop] + "</option>")
            }
        }
        sel1.innerHTML = oparr.join('');


        Event.add(sel1, 'change', function (e) {

            var sel2 = document.getElementById('selectid2');
            var selblock2 = document.getElementById('selblock2');
            selblock2.style.display = 'block';

            var cl = document.getElementById('selectid1');
            var urn = document.getElementById('urnwin');

            var data = {};
            if(cl) data.class = cl.options[cl.selectedIndex].value;
            if(urn) data.urn = urn;

            //var m = data;
            //var json = JSON.stringify(m);
            //console.log(m)
            //var postdata = {'json': json};

            // 2 level
            var done = function (d) {
                var oparr2 = [];
                //oparr2.push("<option selected='selected'>Не выбрано</option>");

                for (var i = 0; i < d.length; i++) {
                    for (var prop in d[i]) {
                        //console.log(d[i][prop]);
                        oparr2.push("<option value='"+prop+"'>" + d[i][prop] + "</option>")
                    }
                }
                sel2.innerHTML = oparr2.join('');
            };
            var Err = function (e, d) {
                console.log(e);
                if (e == 404) console.log('Не найден');
                else if (e == 471) console.log('Запрос без номера');
                else if (e > 471 && e < 520) console.log('Неполный запрос к серверу ' + d);
                else if (e > 520 && e < 600) console.log('Неполный ответ от сервера ' + d);
                notification('BAD', e+' '+d);
            };
            // DATALINK 2
            ajax(dataLink2, done, {
                'onError': function (e, d) {
                    document.getElementById('selectid2').disabled = false;
                    unFadeScreen.call();
                    Err.call(this, e, d)
                }, 'onStart': function() {fadeScreen(); document.getElementById('selectid2').disabled = true;}, 'onDone': function() {unFadeScreen(); document.getElementById('selectid2').disabled = false; }
            }, 'POST', data); // TODO real app with allowed DCT

        });


    };
    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    // DATALINK 1
    //console.log('AJAX', dataLink1)
    ajax(dataLink1,
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen
        },
        'POST', postdata
    );

}
