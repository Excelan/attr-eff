function afterDecision(decisiongate, data)
{
    ajax(decisiongate, function(d) {
        console.log(d);
        console.log(d.globaldecision);

        if (d.globaldecision != 'pending') {
            console.log("We have all decisions");
            var url = 'http://'+document.location.hostname+':8020/completestage/?upn=UPN:P:P:P:' + d.mpeid;
            ajax(url, function (dr) {
                console.log(dr);
                fadeScreen.call();
                notification('GOOD', 'Решение принято, этап завершен');

                setTimeout(function(){
                    window.location.href = '/inbox';
                },2*1000);

            });
        }
        else {
            console.log("We don't have all decisions");
            fadeScreen.call();
            notification('GOOD', 'Решение принято. Ожидаем оставшиеся');
            setTimeout(function(){
                window.location.href = '/inbox';
            },2*1000);
        }

    }, {
        'onError': function(e, d) {
            console.log('Error in '+decisiongate, e, d);
            unFadeScreen.call();
        },
        'onStart': fadeScreen,
        'onDone': function() { unFadeScreen(); }
    }, 'POST', data);

}

//визирование или отмена формы
function visingform() {

    var visingform = document.getElementById('visingform');
    var cancelform = document.getElementById('cancelform');
    var decisiongate = document.getElementById('decisiongate').value;

    console.log(decisiongate);

    // УТВЕРДИТЬ
    Event.add(visingform, 'click', function (e) {
        e.preventDefault();

        var additionalparam = document.getElementById('additionalparam');
        var data = {};

        data.status = visingform.getAttribute('data-param');
        data.param = additionalparam.value;
        data.mpeid = id('mpeid').value;
        data.actor = id('initiator').value;
        data.actorEmployee = id('employee').value;
        data.urn = id('subjectURN').value;
        data.ticketurn = id('ticketurn').value;

        var checkedSolutionVariants = Array.prototype.slice.call(document.querySelectorAll('.visavariant')).filter(function(item) { return item.checked }).map(function(item) { return item.getAttribute('data-visaid') });
        if (checkedSolutionVariants.length)
            data.visedvariants = JSON.stringify(checkedSolutionVariants);

        console.log(JSON.stringify(data));

        afterDecision(decisiongate, data);
    });

    // ОТКЛОНИТЬ
    Event.add(cancelform, 'click', function (e) {
        e.preventDefault();

        var additionalparam = document.getElementById('additionalparam');
        var cancelformtext = document.getElementById('cancelformtext');

        if( trim(cancelformtext.value).length == 0 ){
            notification('BAD', 'Укажите причину отмены');
            return;
        }

        var data = {};

        data.status = cancelform.getAttribute('data-param');
        data.param = additionalparam.value;
        data.text = cancelformtext.value;
        data.mpeid = id('mpeid').value;
        data.actor = id('initiator').value;
        data.actorEmployee = id('employee').value;
        data.urn = id('subjectURN').value;
        data.ticketurn = id('ticketurn').value;
        //console.log(JSON.stringify(data));

        afterDecision(decisiongate, data);

    });

}


function visingformCustom() {

    var visingform = document.getElementById('visingform');
    var cancelform = document.getElementById('cancelform');
    var decisiongate = document.getElementById('decisiongate').value;

    console.log(decisiongate);

    var additionalparam = document.getElementById('additionalparam');
    var data = {};

    data.status = visingform.getAttribute('data-param');
    data.param = additionalparam.value;
    data.mpeid = id('mpeid').value;
    data.actor = id('initiator').value;
    data.actorEmployee = id('employee').value;
    data.urn = id('subjectURN').value;
    data.ticketurn = id('ticketurn').value;

    var checkedSolutionVariants = Array.prototype.slice.call(document.querySelectorAll('.visavariant')).filter(function(item) { return item.checked }).map(function(item) { return item.getAttribute('data-visaid') });
    if (checkedSolutionVariants.length)
        data.visedvariants = JSON.stringify(checkedSolutionVariants);

    console.log(JSON.stringify(data));

    afterDecision(decisiongate, data);


}
