function dms_seen_set() {

    var visingform = document.getElementById('dms_seen');
    var decisiongate = '/DMS/SetDocumentSeen';

    console.log(decisiongate);

    // УТВЕРДИТЬ
    Event.add(visingform, 'click', function (e) {
        e.preventDefault();

        var unidoc = document.getElementById('unidoc');
        var data = {};

        data.status = 'seen';
        data.unidoc = unidoc.value;
        data.actor = id('initiator').value;
        console.log(JSON.stringify(data));

        ajax(decisiongate, function(d) {
            notification('GOOD', 'С документом ознакомлен');
            setTimeout(function(){
                window.location.reload();
            },2*1000);

        }

        , {
            'onError': function(e, d) {
                console.log('Error in '+decisiongate, e, d);
                unFadeScreen.call();
            },
            'onStart': fadeScreen,
            'onDone': function() { unFadeScreen(); }
        }, 'POST', data);


    });

}







//переключение меню в модальном окне
function switching() {
    var toastleftblock = document.getElementById('toastleftblock');
    var toastrightblock = document.getElementById('toastrightblock');
    var lb = document.getElementById('lb');
    var rb = document.getElementById('rb');

    Event.add(lb, 'click', function (e) {
        toastleftblock.style.display = 'block';
        toastrightblock.style.display = 'none';
        this.querySelector('.item').className = 'active item';
        rb.querySelector('.item').className = 'item';
    });

    Event.add(rb, 'click', function (e) {
        toastrightblock.style.display = 'block';
        toastleftblock.style.display = 'none';
        this.querySelector('.item').className = 'active item';
        lb.querySelector('.item').className = 'item';
    });
}


//Переключение между информацией, комментариями и журналом
function switchinginfo() {
    var liinformation = document.getElementById('liinformationid');
    var licomments = document.getElementById('licommentsid');
    var lijournal = document.getElementById('lijournalid');

    var liinformation2 = document.getElementById('liinformationid2');
    var licomments2 = document.getElementById('licommentsid2');
    var lijournal2 = document.getElementById('lijournalid2');

    var information = document.getElementById('informationid');
    var comments = document.getElementById('commentsid');
    var journal = document.getElementById('journalid');

    Event.add(liinformation, 'click', function (e) {
        e.preventDefault();
        information.style.display = 'block';
        comments.style.display = 'none';
        journal.style.display = 'none';

        licomments.className = '';
        liinformation.className = 'active';
        lijournal.className = '';

        licomments2.className = '';
        liinformation2.className = 'active';
        lijournal2.className = '';
    });
    Event.add(licomments, 'click', function (e) {
        e.preventDefault();
        comments.style.display = 'block';
        information.style.display = 'none';
        journal.style.display = 'none';

        licomments.className = 'active';
        liinformation.className = '';
        lijournal.className = '';

        licomments2.className = 'active';
        liinformation2.className = '';
        lijournal2.className = '';
    });
    Event.add(lijournal, 'click', function (e) {
        e.preventDefault();
        journal.style.display = 'block';
        information.style.display = 'none';
        comments.style.display = 'none';

        licomments.className = '';
        liinformation.className = '';
        lijournal.className = 'active';

        licomments2.className = '';
        liinformation2.className = '';
        lijournal2.className = 'active';
    });

    switchinginfo2();
}


//Переключение между информацией, комментариями и журналом для скрытого блока
function switchinginfo2() {
    var liinformation = document.getElementById('liinformationid2');
    if (!liinformation) return;
    var licomments = document.getElementById('licommentsid2');
    var lijournal = document.getElementById('lijournalid2');

    var liinformation2 = document.getElementById('liinformationid');
    var licomments2 = document.getElementById('licommentsid');
    var lijournal2 = document.getElementById('lijournalid');

    var information = document.getElementById('informationid');
    var comments = document.getElementById('commentsid');
    var journal = document.getElementById('journalid');

    Event.add(liinformation, 'click', function (e) {
        e.preventDefault();
        information.style.display = 'block';
        comments.style.display = 'none';
        journal.style.display = 'none';

        licomments.className = '';
        liinformation.className = 'active';
        lijournal.className = '';

        licomments2.className = '';
        liinformation2.className = 'active';
        lijournal2.className = '';

    });
    Event.add(licomments, 'click', function (e) {
        e.preventDefault();
        comments.style.display = 'block';
        information.style.display = 'none';
        journal.style.display = 'none';

        licomments.className = 'active';
        liinformation.className = '';
        lijournal.className = '';

        licomments2.className = 'active';
        liinformation2.className = '';
        lijournal2.className = '';
    });
    Event.add(lijournal, 'click', function (e) {
        e.preventDefault();
        journal.style.display = 'block';
        information.style.display = 'none';
        comments.style.display = 'none';

        licomments.className = '';
        liinformation.className = '';
        lijournal.className = 'active';

        licomments2.className = '';
        liinformation2.className = '';
        lijournal2.className = 'active';
    });
}



//фиксированый правый сайтбар
function fixedRightBar(){
    document.querySelector('body').style.minHeight = document.documentElement.clientHeight+300+'px';


    GetH('commentsid');
    GetH('informationid');
    GetH('journalid');

    function GetH(idEl){


        var thiselem = id(idEl);

        if(thiselem.style.display == 'none')
        show(thiselem);

        var elem = id(idEl).offsetHeight;

        if(elem+50 > document.documentElement.clientHeight) {
            id(idEl).style.height = document.documentElement.clientHeight - 50 + 'px';
            if(idEl == 'informationid'){
                var btn = thiselem.querySelectorAll('.btnblock');
                for(var i = 0; i < btn.length; i++){
                    btn[i].style.width = '312px'
                }
            }
        }
        else
            id(idEl).style.overflow = 'auto';

        if(idEl != 'commentsid')
        hide(thiselem);

    }

    var contentblock = document.getElementsByClassName('contentblock')[0];
    var rightBlock = contentblock.querySelector('.rightblock');
    var hiddenNavButton = document.getElementsByClassName('hiddenNavButton')[0];

    //для позиции при загрузке
    var scroll = window.pageYOffset || document.documentElement.scrollTop;

    if(Number(scroll) > 200){
        if(rightBlock != null) {
            rightBlock.style.position = 'fixed';
            rightBlock.style.top = '0px';
            hiddenNavButton.style.display = 'block';
        }
    }else{
        if(rightBlock != null) {
            rightBlock.style.position = '';
            hiddenNavButton.style.display = 'none';
        }
    }



    Event.add(window, 'wheel', function (e) {


        window.onscroll = function() {
            var scrolled = window.pageYOffset || document.documentElement.scrollTop;

            if(Number(scrolled) > 200){
                if(rightBlock != null) {
                    rightBlock.style.position = 'fixed';
                    rightBlock.style.top = '0px';
                    hiddenNavButton.style.display = 'block';
                }
            }else{
                if(rightBlock != null) {
                    rightBlock.style.position = '';
                    hiddenNavButton.style.display = 'none';
                }
            }
        };

    });
}




//закрытие окна по крестику
function closeModalWindow(x) {

    if(x == 'close')tosatelement.className = 'hide';
    var segmentClose = document.getElementById('segmentClose');
    Event.add(segmentClose, 'click', function (e) {
        e.preventDefault();
        tosatelement.className = 'hide';
    });
}


//Появление и скрытие доп информации по клику на кнопке в хедере
function showHideMoreInfoHeader() {

    var hideEl = document.getElementsByClassName('loadMoreInfo');
    var button = id('showHideMoreInfoHeader');

    Event.add(button, 'click', function (e) {
        e.preventDefault();

        for(var i = 0; i < hideEl.length; i++){
            if(hideEl[i].style.display != 'none') hideEl[i].style.display = 'none';
            else hideEl[i].style.display = 'block';
        }
    });
}





//Кнопки при навидении на комментарий
function displayCommentButton(){
    var comment = document.getElementsByClassName('textcommentFirstLevel');
    var allButton = document.getElementsByClassName('buttonReplyTake');
    var sendForm = document.getElementsByClassName('NewCommentCreate');
    var buttonReply = document.getElementsByClassName('buttonReply');



    if(comment.length > 0 && allButton.length > 0 && sendForm.length>0 && buttonReply.length > 0) {
        for (var i = 0; i < comment.length; i++) {
            Event.add(comment[i], 'mouseover', function (e) {

                var index = Array.prototype.indexOf.call(comment, this);
                allButton[index].style.display = 'block';

            });

            Event.add(comment[i], 'mouseout', function (e) {

                var index = Array.prototype.indexOf.call(comment, this);
                allButton[index].style.display = 'none';


                sendForm[index].style.display = 'none';


            });

            Event.add(buttonReply[i], 'click', function (e) {

                var index = Array.prototype.indexOf.call(buttonReply, this);
                sendForm[index].style.display = 'block';

            });

        }
    }

    if(comment.length > 0 && allButton.length > 0 && sendForm.length>0 && buttonReply.length > 0) {

        for (var j = 0; j < allButton.length; j++) {
            Event.add(allButton[j], 'mouseover', function (e) {
                this.style.display = 'block';
            });
            Event.add(allButton[j], 'mouseout', function (e) {

                var index = Array.prototype.indexOf.call(allButton, this);
                if (sendForm[index].style.display == 'none')
                    this.style.display = 'none';
            });
        }

    }


    if(comment.length > 0 && allButton.length > 0 && sendForm.length>0 && buttonReply.length > 0) {
        for (var k = 0; k < sendForm.length; k++) {

            Event.add(sendForm[k], 'mouseover', function (e) {
                var index = Array.prototype.indexOf.call(sendForm, this);
                allButton[index].style.display = 'block';
                this.style.display = 'block';

                var event = this;


                document.onkeydown = function (evt) {
                    evt = evt || window.event;
                    if (evt.keyCode == 27) {
                        event.style.display = 'none';
                        allButton[index].style.display = 'none';
                    }
                };


            });

        }
    }



}




function capaCommentsAjax(id,urn,level,idCapa){
    var commentBlock = document.getElementById(id);

    var m = {};
    m.level = level;
    m.urn = urn;
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

    ajax('/getcomment?urn='+urn+'&level='+level+'&idCapa='+id,
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
}



function visingStage(){

    var questionnaireHide = document.getElementById('visingform');
    Event.add(questionnaireHide, 'click', function (e) {
        e.preventDefault();

        var radioButtonSolution = document.getElementsByClassName('radioButtonSolution');
        var userUrn = document.getElementById('curentUserPost');

        var radioArr = [];
        for(var i = 0; i < radioButtonSolution.length; i ++){
            if(radioButtonSolution[i].checked){
                radioArr.push({'solutionUrn' : radioButtonSolution[i].getAttribute('id'),'correctionUrn' : radioButtonSolution[i].getAttribute('name')});
            }
        }
        var data = {"selected_variants": radioArr, 'userUrn':userUrn.value, 'surn':id('subjectURN').value};
        var postdata = {'json': JSON.stringify(data) };

        var ok = function (data) {
            if(data['status'] == '404') notification('BAD', 'Пожалуйста, выберите ко всем мероприятим решение');
            else {
                console.log(data);
                visingformCustom();
            }
        };
        var onerror = function (e, data) {
            console.log(e);
            console.log(data);
            alert(e);
        };
        //console.log(postdata);
        ajax('/Capa/Vise', function(data) { ok.call(this, data) } , {'onError': function(e,d) { unFadeScreen.call(); onerror.call(this, e, d) } ,'onStart':fadeScreen, 'onDone': unFadeScreen}, 'POST', postdata);

    });

    var cancel = document.getElementById('cancelform');

    Event.add(cancel, 'click', function (e) {
        e.preventDefault();

        var decisiongate = document.getElementById('decisiongate').value;
        var text = document.getElementById('cancelformtext');
        var additionalparam = document.getElementById('additionalparam');

        var data = {
            "text": text.value,
            'actor' : id('initiator').value,
            'actorEmployee' : id('employee').value,
            'ticketurn' : id('ticketurn').value,
            'param' : additionalparam.value,
            'mpeid' : id('mpeid').value,
            'status' : 'cancel',
            'urn' : id('subjectURN').value
        };

        //var postdata = {'json': JSON.stringify(data) };
        var postdata = data;

        if( trim(text.value).length == 0 ){
            notification('BAD', 'Укажите причину отмены');
            return;
        }

        var ok = function (data) {

            if (data.globaldecision != 'pending') {
                console.log("We have all decisions");
                var url = 'http://'+document.location.hostname+':8020/completestage/?upn=UPN:P:P:P:' + data.mpeid;
                ajax(url, function (dr) {
                    console.log(dr);
                    fadeScreen.call();
                    notification('GOOD', 'Решение принято, этап завершен');
                    window.location.href = '/inbox';
                });
            }
            else {
                console.log("We don't have all decisions");
                fadeScreen.call();
                notification('GOOD', 'Решение принято. Ожидаем оставшиеся');
                window.location.href = '/inbox';
            }


        };
        var onerror = function (e, data) {
            console.log(e);
            console.log(data);
            alert(e);
        };
        //console.log(postdata);
        ajax(decisiongate, function(data) { ok.call(this, data) } , {'onError': function(e,d) { unFadeScreen.call(); onerror.call(this, e, d) } ,'onStart':fadeScreen, 'onDone': unFadeScreen}, 'POST', postdata);

    });

}

function sendToVisaByOne(){
    var data = {};

    data.status = visingform.getAttribute('data-param');
    data.param = additionalparam.value;
    data.mpeid = id('mpeid').value;
    data.actor = id('initiator').value;
    data.actorEmployee = id('employee').value;
    data.urn = id('subjectURN').value;
}

function approvingStage(){

    var cancel = document.getElementById('cancel_vise_btn');
    var questionnaireHide = document.getElementById('send_approve_btn');
    Event.add(questionnaireHide, 'click', function (e) {
        e.preventDefault();

        var radioButtonSolution = document.getElementsByClassName('radioButtonSolution');
        var userUrn = document.getElementById('initiator');

        var radioArr = [];
        for(var i = 0; i < radioButtonSolution.length; i ++){
            if(radioButtonSolution[i].checked){
                radioArr.push({'solutionUrn' : radioButtonSolution[i].getAttribute('id'),'correctionUrn' : radioButtonSolution[i].getAttribute('name')});
            }
        }
        var data = {
            "selected_variants": radioArr,
            'userUrn':userUrn.value,
            'mpeid' : id('mpeid').value,
            'actor' : id('initiator').value,
            'actorEmployee' : id('employee').value,
            'ticketurn' : id('ticketurn').value,
            'urn' : id('subjectURN').value

        };
        var postdata = {'json': JSON.stringify(data) };

        var ok = function (data) {
            if(data['status'] == '404') alert('Выбрано не все! Пожалуйста, выберите ко всем мероприятим решение.');
            else{
                console.log(data);
                window.location.href = '/inbox';
            }
        };
        var onerror = function (e, data) {
            console.log(e);
            console.log(data);
            alert(e);
        };
        //console.log(postdata);
        ajax('/Capa/Approve', function(data) { ok.call(this, data) } , {'onError': function(e,d) { unFadeScreen.call(); onerror.call(this, e, d) } ,'onStart':fadeScreen, 'onDone': unFadeScreen}, 'POST', postdata);

    });


    Event.add(cancel, 'click', function (e) {
        e.preventDefault();

        var text = document.getElementById('cancelText');

        var data = {
            "text": text.value,
            'actor' : id('initiator').value,
            'actorEmployee' : id('employee').value,
            'ticketurn' : id('ticketurn').value,
            'mpeid' : id('mpeid').value,
            'urn' : id('subjectURN').value
        };
        var postdata = {'json': JSON.stringify(data) };

        var ok = function (data) {
            if(data['status'] == '404') notification('BAD', 'Введите причину отмены!');
            else{
                console.log("We have all decisions");
                notification('GOOD', 'Решение принято, этап завершен');
                var url = 'http://'+document.location.hostname+':8020/completestage/?upn=UPN:P:P:P:' + data['mpeid'];
                ajax(url, function (dr) {
                    console.log(dr);
                    fadeScreen.call();
                    window.location.href = '/inbox';
                });

                console.log(data);
                //window.location.href = '/inbox';
            }
        };
        var onerror = function (e, data) {
            console.log(e);
            console.log(data);
            alert(e);
        };

        ajax('/Capa/CancelApproving', function(data) { ok.call(this, data) } , {'onError': function(e,d) { unFadeScreen.call(); onerror.call(this, e, d) } ,'onStart':fadeScreen, 'onDone': unFadeScreen}, 'POST', postdata);

    });

}


function bridgeX(opener, data, openedWindow) {
  this.opener = opener;
  this.data = data;
  this.openedWindow = openedWindow;
};

bridgeX.prototype.setValue = function(v) {
  //console.log("setValue", v)
  //console.log(document.querySelectorAll('.userinfopart .list'))
  // var urn = Object.keys(v); // OLD
  var urn = v.urn;
  // var xx = v[urn].split(';'); // OLD
  var xx = v.title.split(';');
  //var title = xx[0];
  var title = xx[0];
  var subtitle = xx[1].trim();
  var html = "<div class='user'><p class='name'>"+subtitle+"</p><p class='post'>"+title+"</p></div >"
  var df = document.createDocumentFragment();
  var wrapper = document.createElement('div');
  wrapper.innerHTML = html;
  //console.log(document.querySelector('.userinfopart .list'))
  document.querySelector('.userinfopart .list').appendChild(wrapper);

  var ok = function (data) {
      console.log(data);
  };
  var onerror = function (e, data) {
      console.log(e);
      console.log(data);
      alert(e);
  };
  //var initiator = id('initiator').value;
  var subject = id('subjectURN').value;
  var postdata = {postURN: urn, subjectURN: subject};
  // TODO ajax add
  ajax('/Decision/AddAdditionalVisant', function(data) { ok.call(this, data) }, {'onError': function(e,d) { unFadeScreen.call(); onerror.call(this, e, d) }, 'onStart':fadeScreen, 'onDone': unFadeScreen}, 'POST', postdata);
  //
}

function userinfopart_bridge(data, openedWindow)
{
    // console.log('visantaddbridge');
    // console.log(this);
    // console.log(data);
    // console.log(openedWindow);
    // this = opener
    openedWindow.querySelector('main').bridge = new bridgeX(this, data, openedWindow);
    openedWindow.querySelector('main').onComplete = function() {  toggleClass(openedWindow, 'hide') };
}



function bridgeY(opener, data, openedWindow) {
  this.opener = opener;
  this.data = data;
  this.openedWindow = openedWindow;
};

bridgeY.prototype.setValue = function(v) {
  console.log("setValue", v)
  //console.log(document.querySelectorAll('.userinfopart .list'))
  var urn = Object.keys(v);
  var title = v[urn]
  var html = "<a href='#'>"+title+"</a>"
  var df = document.createDocumentFragment();
  var wrapper = document.createElement('div');
  wrapper.innerHTML = html;
  //console.log(document.querySelector('.userinfopart .list'))
  document.querySelector('.filelistpart .list').appendChild(wrapper);

  var ok = function (data) {
      console.log(data);
  };
  var onerror = function (e, data) {
      console.log(e);
      console.log(data);
      alert(e);
  };
  //var initiator = id('initiator').value;
  var subject = id('subjectURN').value;
  var postdata = {documentURN: urn, subjectURN: subject};
  ajax('/Process/AddRelatedDocument', function(data) { ok.call(this, data) }, {'onError': function(e,d) { unFadeScreen.call(); onerror.call(this, e, d) }, 'onStart':fadeScreen, 'onDone': unFadeScreen}, 'POST', postdata);
  //
}

function filelistpart_bridge(data, openedWindow)
{
    openedWindow.querySelector('main').bridge = new bridgeY(this, data, openedWindow);
    openedWindow.querySelector('main').onComplete = function() {  toggleClass(openedWindow, 'hide') };
}


function getLastRedComment(){


    var window = id('lastCommentPopup');

    function offScroll(){
        document.querySelector('body').style.overflow = 'hidden';
    }

    function onScroll(){
        document.querySelector('body').style.overflow = 'auto';
    }

    function closeWindow(){
        var close = id('commentPopupClose');

        Event.add(close, 'click', function (e) {
            hide(window);
            onScroll();
        });

    }


    var m = {};

    if(id('subjectURN').value.length == 0) console.log('Error subjectURN. No subjectURN');
    else m.subjectURN = id('subjectURN').value;

    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {
        var obj = JSON.parse(d);
        console.log(obj.status);

        if(obj.status == 404) return;

        var block = id('lastCommentPopup').querySelector('.cancelComment');

        var time = block.querySelector('.when').querySelector('p');
        var whouser = block.querySelector('.whouser').querySelector('p');
        var post = block.querySelector('.post').querySelector('p');

        time.innerHTML = obj.status.created;
        whouser.innerHTML = obj.status.author;
        post.innerHTML = obj.status.content;
        show(window);
        offScroll();
        closeWindow();

    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax('/Events/GetLastComments',
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen,
            'responseType' : 'plain'
        },
        'POST',
        postdata
    );

}

//
TMCE();
