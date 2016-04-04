<header />
<?

//$m = new Message();
//$m->action = 'load';
//$m->urn = 'urn:Actor:User:System';
//$user = $m->deliver();
//
//foreach($user as $u){
//    println($u->urn);
//}
//
println($this->managementrole->id);
println($this->user->urn);
?>
<p id="showScroll"></p>
<input id="ddd" type="button" value="SEND">
<div class="main">
    <div class="content">
        <div class="contentblock">
            <div class="leftblock">
                <div class="mform">
                    <form id="managedform"
                          data-structure="/config/form/Claims/considering/Claim_R_UPI_7.xml?9" data-load="" data-save=""
                          action="/echopost" data-managedform="yes" data-onsuccess="alertresult1" data-onerror="alerterror">
                    </form>
                </div>
                <div class="buttonblock">
                    <div class="lbutoon">
                        <input class="fbut" type="submit" value="УДАЛИТЬ">
                        <input class="tbut" type="submit" value="СОХРАНИТЬ">
                    </div>
                    <button class="w200 MC RBTN" type="submit">Отправить</button>
                    <button class="rbutoon w300 GBTN" type="submit">Отправить</button>
                    <p class="subtext">ответственным на обработку</p>
                </div>

                <div class="buttons">
                    <p class="itext">Завершить выполнение этапа процесса</p><!--Если надо кнопка, вместо текста вставляем кнопку-->
                    <input class="bin" type="submit" value="Отправить"/>
                </div>

                <div class="buttons">
                    <p class="itext">
                        <input class="rin" type="submit" value="Отклонить"/>
                    </p>
                    <input class="gin" type="submit" value="Визировать"/>
                </div>


                <div class="buttons">
                    <input class="gin c" type="submit" value="Отправить"/><!--Для кнопки с рамкой добаляем клас "c"-->
                </div>
            </div>
            <div class="rightblock">
                <div id="informationid">
                    <div class="infopart">
                        <div class="titleinfo">
                            <p>Информация по объекту</p>
                        </div>
                        <div class="stateinfo">
                            <p>
                                Cтатус
                                <span>статус</span>
                            </p>
                        </div>
                        <div class="stateinfo">
                            <p>
                                Cтатус
                                <span>статус</span>
                            </p>
                        </div>
                        <div class="stateinfo">
                            <p>
                                Cтатус
                                <span>статус</span>
                            </p>
                        </div>
                    </div>

                    <rightbox />

                    <link />

                </div>
                <div id="commentsid" style="display: none">

                    <comments />

                </div>
                <div id="journalid" style="display: none">

                    <journal />

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    switchinginfo();
    displayCommentButton();



    var ddd = id('ddd');


    Event.add(ddd, 'click', function (e) {






        var m = {};
        m.mpeId = '4554163';
        //var json = JSON.stringify(m);
        //var postdata = {'json': json};
        var postdata = m;

        var fine = function (d) {
            console.log(d);
        };

        var Error = function (e, d) {
            console.log(e);
            if (e == 404) alert('Не найден');
            else if (e == 471) alert('Запрос без номера');
            else if (e > 471 && e < 520) alert('Неполный запрос к серверу');
            else if (e > 520 && e < 600) alert('Неполный ответ от сервера');
            console.log(d);
        };

        ajax('/Events/AutoCreateField',
            fine,
            {
                'onError': function (e, d) {
                    unFadeScreen.call();
                    Error.call(this, e, d)
                }, 'onStart': fadeScreen, 'onDone': unFadeScreen,
                'responseType' : 'plain'
            },
            'POST',
            m

        );



    });





</script>
<div id="overlay"></div>