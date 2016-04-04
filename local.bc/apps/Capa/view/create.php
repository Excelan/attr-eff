<header />
<div class="main">
    <div class="content">
        <div class="contentblock">
            <div class="leftblock">
                <div class="mform">
                    <form id="managedform"
                          data-structure="/config/form/CAPA/capa_1.xml?9" data-load="/config/form/detective.json?9" data-save=""
                          action="/echopost" data-managedform="yes" data-onsuccess="alertresult1" data-onerror="alerterror">
                    </form>
                </div>
                <!--
                <div class="buttonblock">
                    <div class="lbutoon">
                        <input class="fbut" type="submit" value="УДАЛИТЬ">
                        <input class="tbut" type="submit" value="СОХРАНИТЬ">
                    </div>
                    <button class="w200 MC RBTN" type="submit">Отправить</button>
                    <button class="rbutoon w300 GBTN" type="submit">Отправить</button>
                    <p class="subtext">ответственным на обработку</p>
                </div>
                -->
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
                    <div class="toptitle">
                        <p>Ответственные за документ</p>
                    </div>
                    <div class="userinfopart">
                        <div class="box">
                            <label>Византы:</label>
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                            <div class="TM BLK btnblock">
                                <div class="FL"></div>
                                <div id="addvizant" class="FR add CP">
                                    <p><a href="#">+Добавить византа</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filelistpart">
                        <div class="box">
                            <label>Документы:</label>
                            <div class="list">
                                <a href="#">Жалоба №21108545101</a>
                                <a href="#">Жалоба №2110854510-2545</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="commentsid" style="display: none">
                    <div class="when">
                        <p><span>Сегодня</span></p>
                    </div>
                    <div class="onecomment">
                        <div class="author">Вася Пупкин</div>
                        <div class="authorpost">Менеджер<span>12:34</span></div>
                        <div class="textcomment">
                            <p> С другой стороны постоянный количественный рост и сфера нашей активности позволяет оценить</p>
                        </div>
                    </div>
                    <div class="when">
                        <p><span>12.01.2018</span></p>
                    </div>
                    <div class="onecomment">
                        <div class="author">Вася Пупкин</div>
                        <div class="authorpost">Менеджер<span>12:34</span></div>
                        <div class="textcomment">
                            <p> С другой стороны постоянный количественный рост и сфера нашей активности позволяет оценить</p>
                        </div>
                    </div>
                    <div class="commentreply">
                        <div class="author">Вася Пупкин</div>
                        <div class="authorpost">Менеджер<span>12:35</span></div>
                        <div class="textcomment">
                            <p> С другой стороны постоянный количественный рост и сфера нашей активности позволяет оценить</p>
                        </div>
                    </div>
                    <div class="commentreply">
                        <div class="author">Вася Пупкин</div>
                        <div class="authorpost">Менеджер<span>12:35</span></div>
                        <div class="textcomment">
                            <p> С другой стороны постоянный количественный рост и сфера нашей активности позволяет оценить</p>
                        </div>
                    </div>
                </div>
                <div id="journalid" style="display: none">
                    <div class="when">
                        <p><span>Сегодня</span></p>
                    </div>
                    <div class="whouser">
                        <p class="name">Джоли Анджелина</p>
                        <p class="post">Ответственный за обработку жалоб<span>12:34</span></p>
                    </div>
                    <div class="event">
                        <p>Событие 345345 за обработку жалоб</p>
                    </div>
                    <div class="whouser">
                        <p class="name">Джоли Анджелина</p>
                        <p class="post">Ответственный за обработку жалоб<span>12:34</span></p>
                    </div>
                    <div class="event">
                        <p>Был завизирован</p>
                    </div>
                    <div class="when">
                        <p><span>12.05.2018</span></p>
                    </div>
                    <div class="whouser">
                        <p class="name">Джоли Анджелина</p>
                        <p class="post">Ответственный за обработку жалоб<span>12:34</span></p>
                    </div>
                    <div class="event">
                        <p>Отозван</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    //добавление инпута путен клонирования существующего
    var addInputText = document.getElementsByClassName('addInputText');

    for(var i = 0; i < addInputText.length; i++){
        Event.add(addInputText[i], 'click', function (e) {
            e.preventDefault();
            var parent = this.parentNode.querySelector('.inputlist');
            var inputcount = this.parentNode.querySelector('.inputlist').querySelectorAll('.oneinput');
            var clone = this.parentNode.querySelector('.inputlist').querySelectorAll('.oneinput')[0].cloneNode(true);
            this.parentNode.querySelector('.inputlist').appendChild(clone);

            var count = inputcount.length;
            this.parentNode.querySelector('.inputlist').querySelectorAll('.oneinput')[count].querySelector('input').value = '';

        });
    }

    //Переключение между информацией, комментариями и журналом
    var liinformation = document.getElementById('liinformationid');
    var licomments = document.getElementById('licommentsid');
    var lijournal = document.getElementById('lijournalid');

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
    });
    Event.add(licomments, 'click', function (e) {
        e.preventDefault();
        comments.style.display = 'block';
        information.style.display = 'none';
        journal.style.display = 'none';

        licomments.className = 'active';
        liinformation.className = '';
        lijournal.className = '';
    });
    Event.add(lijournal, 'click', function (e) {
        e.preventDefault();
        journal.style.display = 'block';
        information.style.display = 'none';
        comments.style.display = 'none';

        licomments.className = '';
        liinformation.className = '';
        lijournal.className = 'active';
    });

</script>