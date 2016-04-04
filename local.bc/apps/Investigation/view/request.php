<?php
extract($this->context);
?>
<header />
<div class="main">
    <div class="content">
        <div class="contentblock">
            <div class="leftblock">

                <div class="part">
                    <div class="titleinpart">
                        Инцидент
                    </div>
                    <div class="textarespart">
                        <label>Структурное подразделение:</label>
                        <textarea placeholder="Введите номер склада, отдел компании"></textarea>
                    </div>
                    <div class="titleinpart">
                        Инцидент
                    </div>
                    <div class="partparagraph">
                        <label>Структурное подразделение:</label>
                        <p>ТОВ "ХФК" БИОКОН</p>
                    </div>
                    <div class="radiopart">
                        <p>Отметка о проведении мер по быстрому устранению проблемы:</p>
                        <input type="radio" name="respondingmark" value="1" id="radioyes">
                        <label for="radioyes">Да</label>
                        <input style="margin: 0 0 0 50px;" type="radio" name="respondingmark" value="0" id="radiono">
                        <label for="radiono">Нет</label>
                    </div>
                    <div class="selectpart">
                        <label>Структурное подразделение:</label>
                        <div class="sbp">
                            <select>
                                <option value="" selected="selected">1</option>
                                <option value="">2</option>
                                <option value="">3</option>
                                <option value="">4</option>
                            </select>
                        </div>
                    </div>
                    <div class="datepart">
                        <label>Дата инцидента:</label>
                        <input type="date" placeholder="2015-01-31" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                    </div>
                    <div class="linkfieldpart">
                        <label class="IBLK w3mw FS15" for="">Обьекты, которые проходили проверку:</label>
                        <a href="#">+добавить еще обьекты</a>
                        <span> привязанные к </span>
                        <a href="">документу</a>
                        <div class="attachedlist">
                            <a href="#">обьект1</a>
                            <a href="#">обьект2</a>
                        </div>
                        <br style="clear: both">
                    </div>
                    <div class="attachedfilepart">
                        <label class="IBLK w3mw FS15" for="">Обьекты, которые проходили проверку:</label>
                        <div class="attachedlist">
                            <a href="#">обьект1</a>
                            <a href="#">обьект2</a>
                        </div>
                    </div>
                    <div class="attachedpart">
                        <label>Загрузите файл:</label>
                        <input type="file">
                    </div>
                    <div class="textpart">
                        <label>Член комиссии:</label>
                        <input type="text" placeholder="Выберите должность сотрудника">
                    </div>
                    <div class="titleinpart">
                        Инцидент
                        <a class="adda" href="#">+ Добавить проблему</a>
                    </div>
                    <div class="textlistparte">
                        <label>Текстовые поля:</label>
                        <div class="inputlist">
                            <div class="oneinput">
                                <div class="removeInputText"><img src="/img/remove.png"></div>
                                <input type="text" placeholder="Выберите должность сотрудника">
                            </div>
                            <div class="oneinput">
                                <div class="removeInputText"><img src="/img/remove.png"></div>
                                <input type="text" placeholder="Выберите должность сотрудника">
                            </div>
                        </div>
                        <a href="#" class="addInputText addbut">+добавить</a>
                        <br style="clear: both">
                    </div>
                    <div class="editlistpart">
                        <label>Текстовые поля:</label>
                        <a href="#" class="addButton addbut">+добавить</a>
                        <div class="plist">
                            <div class="onep">
                                <div class="removeInputText"><img src="/img/remove.png"></div>
                                <p>Инцидент</p>
                            </div>
                            <div class="onep">
                                <div class="removeInputText"><img src="/img/remove.png"></div>
                                <p>Инцидент2</p>
                            </div>
                        </div>
                        <br style="clear: both">
                    </div>





                    <div class="edituserlist">
                        <label>Текстовые поля:</label>
                        <a href="#" class="addButton addbut">+добавить</a>
                        <div class="ulist">
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                        </div>
                        <br style="clear: both">
                    </div>

                    <div class="edituserlist">
                        <label>Текстовые поля:</label>
                        <a href="#" class="normal">+добавить</a>
                        <div class="ulist">
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                            <div class="user">
                                <p class="name">Джоли Анджелина</p>
                                <p class="post">Ответственный за обработку жалоб всех типов</p>
                            </div>
                        </div>
                        <br style="clear: both">
                    </div>




                    <div class="titleinpart">
                        Инцидент
                    </div>
                    <div class="readtextpart">
                        <p>
                            Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет
                            выполнять важные задания по разработке системы обучения кадров, соответствует насущным потребностям. Товарищи! дальнейшее развитие
                            различных форм деятельности способствует подготовки и реализации соответствующий условий активизации. С другой стороны постоянный
                            количественный рост и сфера нашей активности позволяет оценить значение соответствующий условий активизации.
                        </p>
                        <p>
                            Разнообразный и богатый опыт постоянный количественный рост и сфера нашей активности способствует подготовки и реализации направлений
                            прогрессивного развития. Повседневная практика показывает, что сложившаяся структура организации влечет за собой процесс внедрения и
                            модернизации позиций, занимаемых участниками в отношении поставленных задач. Равным образом новая модель организационной деятельности
                            позволяет оценить значение существенных финансовых и административных условий. Равным образом сложившаяся структура организации в
                            значительной степени обуславливает создание новых предложений.
                        </p>
                    </div>
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