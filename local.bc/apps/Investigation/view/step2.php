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
        <div class="partparagraph">
            <label>Клиент:</label>
            <p>ТОВ "ХФК" БИОКОН</p>
        </div>
        <div class="partparagraph">
            <label>№ Склада:</label>
            <p>Секция №9 (Аптечный склад Такеда)</p>
        </div>
        <div class="partparagraph">
            <label>дата начала инцидента:</label>
            <p>17.06.15</p>
        </div>
        <div class="attachedfilepart">
            <label for="" class="IBLK w3mw FS15">Вложения:</label>
            <div class="attachedlist">
                <a href="#">Письмо от клиента.doc</a>
                <a href="#">Фото.jpg</a>
            </div>
        </div>
        <div class="titleinpart">
            Описание проблемы
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
    <div class="part">
        <div class="titleinpart">
            Реагирование
        </div>
        <div class="radiopart">
            <p>Отметка о проведении мер по быстрому устранению проблемы:</p>
            <input type="radio" id="radioyes" value="1" name="respondingmark">
            <label for="radioyes">Да</label>
            <input type="radio" id="radiono" value="0" name="respondingmark" style="margin: 0 0 0 50px;">
            <label for="radiono">Нет</label>
        </div>
        <div class="datepart">
            <label>Дата проведении мер по быстрому устранению проблемы:</label>
            <input type="date" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="2015-01-31">
        </div>
        <div class="textarespart">
            <label>Меры, принятые для устранения проблемы:</label>
            <textarea placeholder="Опишите меры, принятые для устранения проблемы"></textarea>
        </div>
        <div class="edituserlist">
            <label>Ответственный исполнитель:</label>
            <a class="normal" href="#">Выбрать из списка сотрудников</a>
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
    </div>
    <div class="part">
        <div class="titleinpart">
            Расследование
        </div>
        <div class="datepart">
            <label>Дата проведении расследования:</label>
            <input type="date" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="2015-01-31">
        </div>
        <div class="textarespart">
            <label>Выявленные факты:</label>
            <textarea placeholder="Опишите выяленные факты, в ходе расследования"></textarea>
        </div>
        <div class="textarespart">
            <label>Обьекты, которые проходили проверку:</label>
            <textarea placeholder="Введите название обьектов, которые проходили проверку"></textarea>
        </div>
    </div>
    <div class="part">
        <div class="titleinpart">
            Заключение
        </div>
        <div class="radiopart">
            <p>Отметка о статусе жалобы:</p>
            <input type="radio" id="complyes" value="1" name="statecomplaint">
            <label for="complyes">Да</label>
            <input type="radio" id="complno" value="0" name="statecomplaint" style="margin: 0 0 0 50px;">
            <label for="complno">Нет</label>
        </div>
        <div class="textarespart">
            <label>Заключение:</label>
            <textarea placeholder="Введите заключение по служебному расследованию"></textarea>
        </div>
        <div class="textarespart">
            <label>Опись использованных материалов:</label>
            <textarea placeholder="Внесите опись использованных материалов"></textarea>
        </div>
    </div>
    <div class="part">
        <div class="titleinpart">
            Документация
        </div>
        <div class="linkfieldpart">
            <label for="" class="IBLK w3mw FS15">Ссылки на документацию:</label>
            <a href="#">Выбрать из списка связанных документов</a>
            <div class="attachedlist">
<!--                <a href="#">обьект1</a>
                <a href="#">обьект2</a>-->
            </div>
            <br style="clear: both">
        </div>
        <div class="attachedpart">
            <label>Загрузите файл:</label>
            <input type="file">
        </div>
    </div>
    <div class="deviationList">
        <div class="part">
            <div class="titleinpart">
                Отклонение1
            </div>
            <div class="textarespart">
                <label>Описание:</label>
                <textarea placeholder="Опишите выяленную проблему"></textarea>
            </div>
            <div class="titleinpart">
                Риски
            </div>
            <div class="linkfieldpart">
                <label class="IBLK w3mw FS15" for="">Идентифицированные риски:</label>
                <a href="#">выберите из списка рисков</a>
                <div class="attachedlist">
                    <!--                <a href="#">обьект1</a>
                                    <a href="#">обьект2</a>-->
                </div>
                <br style="clear: both">
            </div>
            <div class="textlistparte">
                <label>Недентифицированный риск №1:</label>
                <div class="inputlist">
                    <div class="oneinput">
                        <div class="removeInputText"><img src="/img/remove.png"></div>
                        <input type="text" placeholder="Выберите должность сотрудника">
                    </div>
                </div>
                <a class="addInputText addbut" href="#">+добавить</a>
                <br style="clear: both">
            </div>
            <div class="radiopart">
                <p>Предмет риска:</p>
                <input type="radio" name="subjectofrisk" value="1" id="risk1">
                <label for="risk1">Обьект</label>
                <input type="radio" style="margin: 0 0 0 50px;" name="subjectofrisk" value="0" id="risk2">
                <label for="risk2">Процес</label>
            </div>
            <div class="selectpart">
                <label></label>
                <div class="sbp">
                    <select>
                        <option selected="selected" value="">Выберите обьект</option>
                        <option value="">2</option>
                        <option value="">3</option>
                        <option value="">4</option>
                    </select>
                </div>
            </div>
            <div class="selectpart">
                <label>Департамент:</label>
                <div class="sbp">
                    <select>
                        <option selected="selected" value="">Выберите департамент</option>
                        <option value="">2</option>
                        <option value="">3</option>
                        <option value="">4</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="part">
        <div class="titleinpart">
            Новое отклонение
            <a href="#" class="adda">+добавить</a>
        </div>
    </div>
    <div class="buttonblock">
        <div class="lbutoon">
            <input class="tbut" type="submit" value="СОХРАНИТЬ">
        </div>
        <button class="rbutoon w300 GBTN" type="submit">Отправить</button>
    </div>
</div>
<div class="rightblock">
    <div id="informationid">
        <div class="infopart">
            <div class="titleinfo">
                <p>Информация по документу</p>
            </div>
            <div class="stateinfo">
                <p>
                    Cтатус
                    <span>черновик</span>
                </p>
            </div>
            <div class="stateinfo">
                <p>
                    Дата создания документа
                    <span>2015-01-13</span>
                </p>
            </div>
            <div class="stateinfo">
                <p>
                    ID документа
                    <span>645654654</span>
                </p>
            </div>
        </div>
        <div class="filelistpart">
            <div class="box">
                <label>Инициирующий документ:</label>
                <div class="list">
                    <a href="#">Жалоба №21108545101</a>
                </div>
            </div>
        </div>
        <div class="toptitle">
            <p>Ответственные за документ</p>
        </div>
        <div class="userinfopart">
            <div class="box">
                <label>Инициатор:</label>
                <div class="user">
                    <p class="name">Джоли Анджелина</p>
                    <p class="post">Ответственный за обработку жалоб всех типов</p>
                </div>
            </div>
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
        <div class="userinfopart">
            <div class="box">
                <label>Утверждающий:</label>
                <div class="user">
                    <p class="name">Джоли Анджелина</p>
                    <p class="post">Ответственный за обработку жалоб всех типов</p>
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
            <div class="authorpost">12:34<span>Менеджер</span></div>
            <div class="textcomment">
                <p> С другой стороны постоянный количественный рост и сфера нашей активности позволяет оценить</p>
            </div>
        </div>
        <div class="commentreply">
            <div class="author">Вася Пупкин</div>
            <div class="authorpost">12:34<span>Менеджер</span></div>
            <div class="textcomment">
                <p> С другой стороны постоянный количественный рост и сфера нашей активности позволяет оценить</p>
            </div>
        </div>
    </div>
    <div id="journalid" style="display: none">
        <div class="when">
            <p><span>Сегодня</span></p>
        </div>
        <div class="event">
            <p>Событие 345345 за обработку жалоб</p>
        </div>
        <div class="whouser">
            <p class="name">Джоли Анджелина</p>
            <p class="post">Ответственный за обработку жалоб</p>
        </div>
        <div class="event">
            <p>Был завизирован</p>
        </div>
        <div class="whouser">
            <p class="name">Джоли Анджелина</p>
            <p class="post">Ответственный за обработку жалоб</p>
        </div>
        <div class="when">
            <p><span>12.05.2018</span></p>
        </div>
        <div class="event">
            <p>Отозван</p>
        </div>
        <div class="whouser">
            <p class="name">Джоли Анджелина</p>
            <p class="post">Ответственный за обработку жалоб</p>
        </div>
        <div class="when">
            <p><span>12.05.2018</span></p>
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