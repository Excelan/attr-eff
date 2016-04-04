
function questionnaire(file, urn){



    //события закрытия окна или переход по ссылках
    window.onunload = function()
    {
        return 'Вы хотите покинуть сайт?';
    }

    window.onbeforeunload = function(){
        return 'Точно хотите выйти?';
    }



    var questionnaire = document.getElementById('questionnaire');

    var m = {};
    m.urn = urn;
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {

        //console.log(d['options'][0]['questions']);


        //установка времени начала теста

        function addZero(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }


        var date = new Date();

        var H = addZero(date.getHours());
        var I = addZero(date.getMinutes());
        var s = addZero(date.getSeconds());
        var currentTime = document.getElementById('currentTimeId');
        currentTime.innerHTML = H+":"+I+":"+s;
        currentTime.setAttribute("data-time", date.toUTCString());

        //вывод вопросов
        for (var i = d['options'][0]['questions'].length-1; i >= 0; i--) {

            var globdiv = document.createElement('div');

            //щетчик количества
            var countQ = document.createElement('p');
            countQ.classList.add('countQ');
            globdiv.appendChild(countQ);
            countQ.appendChild(document.createTextNode('1/'+d['options'][0]['questions'].length));

            //время на ответ
            var ptime = document.createElement('p');
            var spantime = document.createElement('span');
            spantime.classList.add('answertime');
            spantime.appendChild(document.createTextNode(d['options'][0]['time']));
            ptime.classList.add('qtime');
            ptime.style.float = 'right';
            ptime.appendChild(document.createTextNode('Осталось '));
            ptime.appendChild(spantime);
            globdiv.appendChild(ptime);

            //Вопрос
            var h2 = document.createElement('h2');
            h2.appendChild(document.createTextNode(d['options'][0]['questions'][i]['text']));

            globdiv.appendChild(h2);
            globdiv.setAttribute('urn', d['options'][0]['questions'][i]['urn']);

            //наполнение ответами
            for(var j = 0; j < d['options'][0]['questions'][i]['answers'].length; j++){
                var innerdiv = document.createElement('div');
                innerdiv.classList.add('anwernum');
                var label = document.createElement('label');

                //создание checkbox
                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.setAttribute('urn',d['options'][0]['questions'][i]['answers'][j]['urn']);
                checkbox.setAttribute('id',d['options'][0]['questions'][i]['answers'][j]['urn']);
                innerdiv.appendChild(checkbox);

                //создание label
                label.appendChild(document.createTextNode(d['options'][0]['questions'][i]['answers'][j]['text']));
                label.setAttribute('for',d['options'][0]['questions'][i]['answers'][j]['urn']);
                innerdiv.appendChild(label);

                globdiv.appendChild(innerdiv);
            }

            //кнопка следующий вопрос
            var aglob = document.createElement('a');
            aglob.href = '#';
            aglob.classList.add('nextQuestion');
            aglob.classList.add('gin');
            aglob.appendChild(document.createTextNode('NEXT'));
            globdiv.appendChild(aglob);



            //вывод
            var theFirstChild = questionnaire.firstChild;
            questionnaire.insertBefore(globdiv, theFirstChild);
        }

        //скрытие поврос кроме текущего
        var all = document.getElementById('questionnaire');
        var one = all.querySelectorAll("div[urn]");

        for(var i = 0; i < one.length; i++){
            one[i].style.display = 'none';
        }
        one[0].style.display = 'block';


        GlobTimer();
        startTimer();


    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax(file,
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen
        },
        'POST',
        postdata
    );

}


function questionnaireResult(uri){
    var questionnaire = document.getElementById('questionnaire');
    var question = questionnaire.querySelectorAll("div[urn]");
    var atime;//врямя ответа
    var alltime = document.getElementById('alltime');//общее врямя

    var result = [];

    for(var i = 0; i < question.length; i++){

        var arr = [];
        var input = question[i].querySelectorAll('input[type="checkbox"]');

        for(var j = 0; j < input.length; j++) {

            if(input[j].checked) arr.push(input[j].getAttribute('urn'));
            //if(input[j].checked) arr.push({'urn' : input[j].getAttribute('urn')});  //или {}

        }

        atime = question[i].querySelector('.answertime').innerHTML;

        if(arr.length > 0) result.push({'question':question[i].getAttribute('urn'),"answer":arr, "time":atime});
        if(arr.length == 0) result.push({'question':question[i].getAttribute('urn'),"time":atime});

    }

    //общее оставшееся время
    result.push({"alltime":alltime.innerHTML});

    alltime.classList.add('stop');

    //два нуля во времени
    function addZero(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }

    //установка времени окончания теста
    var date = new Date();
    var H = addZero(date.getHours());
    var I = addZero(date.getMinutes());
    var s = addZero(date.getSeconds());
    var endTestTime = document.getElementById('endTestTimeId');
    endTestTime.innerHTML = H+":"+I+":"+s;


    //конец - -- обработка и вывод результатов

    //console.log(JSON.stringify(result));

    var currentTimeId = document.getElementById('currentTimeId');
    var m = {};
    m.result = result;
    m.endTime = date;
    m.managementrole = document.getElementById('managementroleId').value;
    m.startTime = currentTimeId.getAttribute('data-time');
    m.urn = uri;
    m.subjectURN = id('subjectURN').value;
    m.ticketurn = id('ticketurn').value;
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {
        var wrongAnswer = document.getElementById('wrongAnswer');
        var rightAnswer = document.getElementById('rightAnswer');
        //var percentageRatio = document.getElementById('percentageRatio');
        //var accessToWork = document.getElementById('accessToWork');

        wrongAnswer.innerHTML = d['false'];
        rightAnswer.innerHTML = d['true'];
        //percentageRatio.innerHTML = d['percent'];
        //accessToWork.innerHTML = d['done'];
        if(d['done'] == 'Нет') id('certificationSuccessfully').innerHTML = "<span style='color:red'>Аттестация не пройдена. Доступа к работе нет</span>";

    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax('/Questionnaire/getResult',
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen
        },
        'POST',
        postdata
    );


















}


//таймер для ответов
function startTimer() {

    timer(0);

    function timer(n) {

        var my_timer = document.getElementsByClassName('answertime')[n];
        //alert(my_timer.innerHTML);
        if(my_timer.className != 'answertime stop') {
            var time = my_timer.innerHTML;
            var arr = time.split(":");
            var h = arr[0];
            var m = arr[1];
            var s = arr[2];
            if (s == 0) {
                if (m == 0) {
                    if (h == 0) {
                        //остановка текущего таймера
                        my_timer.classList.add('stop');

                        var all2 = document.getElementById('questionnaire');
                        var one2 = all2.querySelectorAll("div[urn]");

                        //запуск следущего тамймера
                        if(one2.length-1 != n) {
                            var timerId = setTimeout(function () {
                                timer(n + 1)
                            }, 1000);
                        }

                        //скрытие текущего и открытие следующего
                        if(one2.length-1 == n){
                            var done = document.getElementById('questionareResult');
                            var questionnaireHide = document.getElementById('questionnaire');
                            var uri = document.getElementById('urnQuestionnaireId');
                            my_timer.classList.add('stop');
                            one2[n].style.display = 'none';
                            done.style.display = 'block';
                            questionnaireHide.style.display = 'none';
                            questionnaireResult(uri.value);
                            return;
                        }else {
                            one2[n].style.display = 'none';
                            one2[n + 1].style.display = 'block';
                        }


                        return;
                    }
                    h--;
                    m = 60;
                    if (h < 10) h = "0" + h;
                }
                m--;
                if (m < 10) m = "0" + m;
                s = 59;
            }
            else s--;
            if (s < 10) s = "0" + s;

            my_timer.innerHTML = h + ":" + m + ":" + s;

            var ind = Array.prototype.indexOf.call(document.getElementsByClassName('answertime'), document.getElementsByClassName('answertime')[n]);

            var timerId = setTimeout(function () {
                timer(ind)
            }, 1000);
        }

    }



    var nextQ = document.getElementsByClassName('nextQuestion');
    for(var i = 0; i < nextQ.length; i++){
        Event.add(nextQ[i], 'click', function (e) {
            e.preventDefault();

            //остановка текущего таймера
            //timer_is_on=1;
            this.parentNode.querySelector('.answertime').classList.add('stop');
            //clearTimeout(timerId);

            var all = document.getElementById('questionnaire');
            var one = all.querySelectorAll("div[urn]");

            //блок щетчика
            var countQ = all.querySelectorAll('.countQ');

            //запуск следущего тамймера
            var index = Array.prototype.indexOf.call(nextQ, this);
            if(index != one.length-1) var timerId = setTimeout(function(){timer(index+1)}, 1000);

            //скрытие текущего и открытие следующего
            if(index == one.length-1){
                var done = document.getElementById('questionareResult');
                var questionnaireHide = document.getElementById('questionnaire');
                this.parentNode.querySelector('.answertime').classList.add('stop');
                var uri = document.getElementById('urnQuestionnaireId');
                one[index].style.display = 'none';
                done.style.display = 'block';
                questionnaireHide.style.display = 'none';
                questionnaireResult(uri.value);
                return;
            }else {
                one[index].style.display = 'none';
                one[index + 1].style.display = 'block';
                countQ[index+1].innerHTML = (index+2)+"/"+one.length;
            }

        });
    }

}

//общий таймер
function GlobTimer() {
    var my_timer = document.getElementById("alltime");

    if(my_timer.className != "stop") {

        //alert(my_timer.innerHTML);
        var time = my_timer.innerHTML;
        var arr = time.split(":");
        var h = arr[0];
        var m = arr[1];
        var s = arr[2];
        if (s == 0) {
            s++;
            if (s < 10) s = "0" + s;
        } else {
            if(s == 59){
                s = 0+'0';
                if(m != 59) {
                    m++;
                    if (m < 10) m = "0" + m;
                }else{
                    m = 0+"0";
                    h++;
                    if (h < 10) h = "0" + h;
                }
            }else {
                s++;
                if (s < 10) s = "0" + s;
            }
        }

        my_timer.innerHTML = h + ":" + m + ":" + s;
        var timerId = setTimeout(GlobTimer, 1000);
    }
}


//конвертация времени
function dividingTime(el){


    var m = {};
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {

        var element = document.getElementById('alltime');

        var time = d['options'][0]['time'];
        var arr = time.split(":");
        var h = Number(arr[0]);
        var m = Number(arr[1]);
        var s = Number(arr[2]);

        var result = 0;

        if( h > 0){
            var h = h*3600;
        }
        if(m > 0){
            var m = m*60;
        }

        result = (h+m+s);


        var time = result*d['options'][0]['questions'].length;

        var hours = Math.floor(time/3600);

        var minutes = Math.floor((time - (hours*3600))/60);

        var seconds = time - (hours*3600) - (minutes*60);


        if(hours < 10) hours= '0'+hours;
        if(minutes < 10) minutes= '0'+minutes;
        if(seconds < 10) seconds= '0'+seconds;

        element.innerHTML = hours+":"+minutes+":"+seconds;

    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax('/config/form/Examples/question.json',
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen
        },
        'GET',
        postdata
    );

}


//получение пользователей участвующих в обучении
function getUserList(month,year,surn) {
    var m = {};
    m.month = month;
    m.year = year;
    m.surn = surn;
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {
        var userListQA = document.getElementById('userListQA');
        console.log(d);

    for(var n = 0; n <= d['mday']; n++){

        var topDiv = document.createElement('div');
        topDiv.setAttribute('id', n+'DayId');
        if(n != d['day']) topDiv.style.display = 'none'; //если не выбраный день, то скрывать

        for (var i = 0; i < d['status'].length; i++) {

            //вывод дат отсутствия
            var missing = false;
            //console.log(d['status'][0]['missing'].length);

            if (d['status'][i]['missing'].length > 0) {
                for (var j = 0; j < d['status'][i]['missing'].length; j++) {
                    if (d['status'][i]['missing'][j] == n) {
                        missing = true;
                        break;
                    }
                }
            }

            var globDiv = document.createElement('div');
            globDiv.classList.add('user');
            if (missing) globDiv.classList.add('busy'); //если отсутствует в это число

            var input = document.createElement('input');
            input.setAttribute('type', 'checkbox');
            if (!missing)input.setAttribute('checked', 'checked'); //если отсутствует в это число

            var inputHidden = document.createElement('input');
            inputHidden.setAttribute('type','hidden');
            inputHidden.value = d['status'][i]['urn'];
            globDiv.appendChild(inputHidden);

            var inDiv = document.createElement('div');
            inDiv.classList.add('peoplelist');

            var pName = document.createElement('p');
            pName.classList.add('name');

            var pPost = document.createElement('p');
            pPost.classList.add('post');

            for( var c = 0; c < d['status'][i]['replace'].length; c++){
                if(d['status'][i]['replace'][c][0] == n){
                    globDiv.classList.remove('busy');
                    globDiv.classList.add('replace');
                    input.removeAttribute('checked');

                    var inputHiddenInner = document.createElement('input');
                    inputHidden.setAttribute('type','hidden');
                    inputHidden.value = d['status'][i]['replace'][c][1][0]['urn'];
                    inDiv.appendChild(inputHidden);

                    pName.appendChild(document.createTextNode(d['status'][i]['replace'][c][1][0]['name']));
                    pPost.appendChild(document.createTextNode(d['status'][i]['replace'][c][1][0]['post']));
                    break;
                }
            }

            if(pName.innerHTML == '')pName.appendChild(document.createTextNode(d['status'][i]['name']));
            if(pPost.innerHTML == '')pPost.appendChild(document.createTextNode(d['status'][i]['post']));

            input.style.display = 'none'; //TODO скрываем чекбоксы , если надо - закоментить!!!

            globDiv.appendChild(input);
            inDiv.appendChild(pName);
            inDiv.appendChild(pPost);
            globDiv.appendChild(inDiv);

            globDiv.appendChild(inDiv);

            topDiv.appendChild(globDiv);

            //вывод
            var theFirstChild = userListQA.firstChild;
            userListQA.insertBefore(topDiv, theFirstChild);


        }
    }
        getUserInDate();
        //sendDataUser();
        setSelectDateField();
    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax('/Questionnaire/getUserList',
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen
        },
        'POST',
        postdata
    );
}




function GetNecessaryUser(month,year,urn,gate) {
    var m = {};
    m.month = month;
    m.year = year;
    m.urn = urn;
    var json = JSON.stringify(m);
    var postdata = {'json': json};

    var fine = function (d) {
        var userListQA = document.getElementById('userListQA');
        console.log(d);

        for(var n = 0; n <= d['mday']; n++){

            var topDiv = document.createElement('div');
            topDiv.setAttribute('id', n+'DayId');
            if(n != d['day']) topDiv.style.display = 'none'; //если не выбраный день, то скрывать

            for (var i = 0; i < d['status'].length; i++) {

                //вывод дат отсутствия
                var missing = false;
                //console.log(d['status'][0]['missing'].length);

                if (d['status'][i]['missing'].length > 0) {
                    for (var j = 0; j < d['status'][i]['missing'].length; j++) {
                        if (d['status'][i]['missing'][j] == n) {
                            missing = true;
                            break;
                        }
                    }
                }

                var globDiv = document.createElement('div');
                globDiv.classList.add('user');
                if (missing) globDiv.classList.add('busy'); //если отсутствует в это число

                var input = document.createElement('input');
                input.setAttribute('type', 'checkbox');
                if (!missing)input.setAttribute('checked', 'checked'); //если отсутствует в это число

                var inputHidden = document.createElement('input');
                inputHidden.setAttribute('type','hidden');
                inputHidden.value = d['status'][i]['urn'];
                globDiv.appendChild(inputHidden);

                var inDiv = document.createElement('div');
                inDiv.classList.add('peoplelist');

                var pName = document.createElement('p');
                pName.classList.add('name');

                var pPost = document.createElement('p');
                pPost.classList.add('post');

                for( var c = 0; c < d['status'][i]['replace'].length; c++){
                    if(d['status'][i]['replace'][c][0] == n){
                        globDiv.classList.remove('busy');
                        globDiv.classList.add('replace');
                        input.removeAttribute('checked');

                        var inputHiddenInner = document.createElement('input');
                        inputHidden.setAttribute('type','hidden');
                        inputHidden.value = d['status'][i]['replace'][c][1][0]['urn'];
                        inDiv.appendChild(inputHidden);

                        pName.appendChild(document.createTextNode(d['status'][i]['replace'][c][1][0]['name']));
                        pPost.appendChild(document.createTextNode(d['status'][i]['replace'][c][1][0]['post']));
                        break;
                    }
                }

                if(pName.innerHTML == '')pName.appendChild(document.createTextNode(d['status'][i]['name']));
                if(pPost.innerHTML == '')pPost.appendChild(document.createTextNode(d['status'][i]['post']));

                globDiv.appendChild(input);
                inDiv.appendChild(pName);
                inDiv.appendChild(pPost);
                globDiv.appendChild(inDiv);

                globDiv.appendChild(inDiv);

                topDiv.appendChild(globDiv);

                //вывод
                var theFirstChild = userListQA.firstChild;
                userListQA.insertBefore(topDiv, theFirstChild);


            }
        }
        getUserInDate();
        setSelectDateField();
    };

    var Error = function (e, d) {
        console.log(e);
        if (e == 404) alert('Не найден');
        else if (e == 471) alert('Запрос без номера');
        else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
        else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
        console.log(d);
    };

    ajax(gate,
        fine,
        {
            'onError': function (e, d) {
                unFadeScreen.call();
                Error.call(this, e, d)
            }, 'onStart': fadeScreen, 'onDone': unFadeScreen
        },
        'POST',
        postdata
    );
}




function getUserInDate(){
    var calendar = document.getElementsByClassName('calendar');
    var td = calendar[0].querySelectorAll("td");

    for(var i = 0; i < td.length; i++) {
        Event.add(td[i], 'click', function (e) {
            e.preventDefault();
            if(this.querySelector('.num')) var num = this.querySelector('.num').innerHTML;
            if(num) {
                for (var j = 0; j < td.length; j++) {
                    td[j].classList.remove("today");
                }
                this.classList.add("today");


                var user = document.getElementById('userListQA');
                var allUser = user.querySelectorAll('div[id$="DayId"]');

                for (var k = 0; k < allUser.length; k++) {
                    allUser[k].style.display = 'none';
                }

                document.getElementById(num + 'DayId').style.display = 'block';
            }
        });
    }

}

//запись выбраного поля даты в базу, если нету updateUrn, апдейтит subjectURN
function sendDataUser(gate,updateUrn){
    var submitt = document.getElementById('nextInternal');
    Event.add(submitt, 'click', function (e) {
        e.preventDefault();
        var user = document.getElementById('userListQA');
        var allUser = user.querySelectorAll('div[id$="DayId"]');

        var month = id('dateMonthId').value;
        var year = id('dateYearId').value;

        var calendar = document.getElementsByClassName('calendar');
        var td = calendar[0].querySelectorAll("td.today")[0].querySelector('.num').innerHTML;

        var arrUrn =  new Array();

        for (var i = 0; i < allUser.length; i++) {

            if(allUser[i].style.display != 'none') {

                var num = allUser[i].querySelectorAll(".user").length;

                for(var k = 0; k < num; k++){

                    if(allUser[i].querySelectorAll(".user")[k].querySelector("input[type='checkbox']").checked)
                        arrUrn.push(allUser[i].querySelectorAll(".user")[k].querySelector("input[type='hidden']").value);

                }
            }
        }

        var m ={};
        //m.urn = arrUrn;
        if(updateUrn)m.subjecturn = updateUrn;
        else m.subjecturn = id('subjectURN').value;
        m.eventDate = td+'-'+month+'-'+year;
        m.datefield  = id('datefieldId').value;
        m.users  = arrUrn;


        function fine(d){
            console.log(d);
            window.location.reload(true);
        }

        function Error(e,d){
            console.log(e);
            console.log(d);
        }


        ajax(gate,
            fine,
            {
                'onError': function (e, d) {
                    unFadeScreen.call();
                    Error.call(this, e, d)
                }, 'onStart': fadeScreen, 'onDone': unFadeScreen
            },
            'POST',
            m
        );

        console.log(m);

    });
}


function sendDataUserCapa(gate){
    var submitt = document.getElementById('nextInternal');
    Event.add(submitt, 'click', function (e) {
        e.preventDefault();
        var user = document.getElementById('userListQA');
        var allUser = user.querySelectorAll('div[id$="DayId"]');

        var month = id('dateMonthId').value;
        var year = id('dateYearId').value;

        var calendar = document.getElementsByClassName('calendar');
        var td = calendar[0].querySelectorAll("td.today")[0].querySelector('.num').innerHTML;

        var arrUrn =  new Array();

        for (var i = 0; i < allUser.length; i++) {

            if(allUser[i].style.display != 'none') {

                var num = allUser[i].querySelectorAll(".user").length;

                for(var k = 0; k < num; k++){

                    if(allUser[i].querySelectorAll(".user")[k].querySelector("input[type='checkbox']").checked)
                        arrUrn.push(allUser[i].querySelectorAll(".user")[k].querySelector("input[type='hidden']").value);

                }
            }
        }

        var m ={};
        //m.urn = arrUrn;
        m.subjecturn = id('subjectURN').value;
        //m.eventDate = td+'/'+month+'/'+year;
        m.eventDate = year+'-'+month+'-'+td;
        m.datefield  = id('datefieldId').value;
        m.users  = arrUrn;
        m.place  = id('eventPlace').value;
        m.time  = id('eventTime').value;


        function fine(d){
            console.log(d);
            window.location.reload();
        }

        function Error(e,d){
            console.log(e);
            console.log(d);
        }


        ajax(gate,
            fine,
            {
                'onError': function (e, d) {
                    unFadeScreen.call();
                    Error.call(this, e, d)
                }, 'onStart': fadeScreen, 'onDone': unFadeScreen
            },
            'POST',
            m
        );

        console.log(m);

    });
}

//переход на слейдующий етап
function goNextStage(close){

    var goNextStage = id('goNextStage');

    Event.add(goNextStage, 'click', function (e) {
        var mpeid = id('mpeid').value;

        var m = {};
        m.mpeid = mpeid;

        function fine(d){
            if(d['status'] == 404) alert('Ошибка документа. Нет mpeid');
            else window.location.href = '/inbox';
            console.log(d);
        }

        function Error(e,d){
            console.log(e);
            console.log(d);
        }


        ajax('/Events/NextStage',
            fine,
            {
                'onError': function (e, d) {
                    unFadeScreen.call();
                    Error.call(this, e, d)
                }, 'onStart': fadeScreen, 'onDone': unFadeScreen
            },
            'POST',
            m
        );


        if(close = 'closeTicket'){
            closeTicket();
        }
    });

}

//закрытие тикета
function closeTicket(){

    var goNextStage = id('goNextStage');

    Event.add(goNextStage, 'click', function (e) {
        var mpeid = id('ticketurn').value;

        var m = {};
        m.ticketurn = mpeid;

        function fine(d){
            if(d['status'] == 404) alert('Ошибка документа. Нет ticketurn');
            else window.location.href = '/inbox';
            console.log(d);
        }

        function Error(e,d){
            console.log(e);
            console.log(d);
        }


        ajax('/Events/CloseTicket',
            fine,
            {
                'onError': function (e, d) {
                    unFadeScreen.call();
                    Error.call(this, e, d)
                }, 'onStart': fadeScreen, 'onDone': unFadeScreen
            },
            'POST',
            m
        );

    });

}


//вывод сохраненного поля даты
function setSelectDateField(){
    var checkdate = id('checkDate').value;
    if(checkdate.length > 0){
        var calendar = document.getElementsByClassName('calendar');
        var td = calendar[0].querySelectorAll("td");

        for (var i = 0; i < td.length; i++) {
            td[i].classList.toggle("today",false);
        }

        for (var j = 0; j < td.length; j++) {

            if(td[j].querySelector('span') != null) {

                if (td[j].querySelectorAll('span')[0].innerHTML == Number(checkdate)) {
                    td[j].classList.add("today");
                    break;
                }

            }

        }

        var allUser = document.querySelectorAll('div[id$="DayId"]');
        for (var k = 0; k < allUser.length; k++) {
            allUser[k].style.display = 'none';
        }

        var current = id(Number(checkdate)+'DayId');
        current.style.display = 'block';

    }
}