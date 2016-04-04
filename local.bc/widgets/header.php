<?php

function widget_header($options)
{
    /*

    $number - название документа (в рамке);
    $stage - этап обробки документа
    $name - 3 полоска меню --- название и 3 навигационных кнопки меню справа
    $title - 2 полоса название крупнее(если нет 3 полосы и нету $number и $stage)
    $add - кнопка с плюсиком (добавляется снизу справа 3 полосы, !!! если нет menu Tabs) Должна быть полоса 2                                   //масив
    $h1add - кнопка с плюсиком (добавляется справа 2 полосы, если есть $title и нет 3 полосы и нету $number и $stage )               //масив


    Menu(tabs)
    создается передачей 2 обязательных параметров
    1) масива tabs
    2) и поточным УРЛ $currentURI (для подсветки текущего пункта меню)
    и одним дополнительным (кнопка плюс)
    $floatingButton



     modal - масив пареметров для модального окна добавления документа
            ключи:
            1)data-link1 - адрес JSON файла для заполнения 1 селекта
            2)data-link2 - адрес JSON файла для заполнения 2 селекта(после выбора первого)
            3)urndoc - urn Документа на основе которого создается
            3)doctitle - Название документа на основе которого создается


    */


    extract($options);

    $ab = '';
    if ($add && !$tabs) {
        $dc = $add['data-call'];
        $at = $add['title'];
        $ai = $add['data-openwindow'];
        $ab = "<div class='floatingButton'><a data-legacy='yes' data-openwindow='{$ai}' data-call='{$dc}' href='{$at}'>+</a></div>";
    }

    if ($title && !$number && !$stage) {
        $hb = '';
        $moreInfoHeader = '';
        if ($buttonMoreInfoHeader) {
            $moreInfoHeader = "<span id='showHideMoreInfoHeader'>Информация по документу</span>";
        }
        if ($h1add) {
            $ht = $h1add['title'];
            $hi = $h1add['data-openwindow'];
            $hb = "<div class='floatingButton' style='margin-top: -60px'><a data-legacy='yes' data-openwindow='{$hi}' data-call='onclickdynwin' href='{$ht}'>+</a></div>";
        }
        $h1 = "
            <h1>
                    <span style='margin: 0;max-width: 963px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'>{$title}</span>
                    {$moreInfoHeader}
            </h1>
            {$hb}
        ";
    }

    $two = '';
    if ($number || $stage || $title) {
        $h2 = '';
        if ($number) {
            $h2 = "<h2>
                    <span>{$number}</span>
                </h2>";
        } else {
            $first = "style='margin: 0;'";
        }
        $two = "
        <div class='incedenttitle'>
        <div class='content'>
            <div class='nameblk'>
                {$h1}
                {$h2}
                <span {$first}>{$stage}</span>
            </div>
        </div>
    </div>";
    }

    $three = '';
    if ($name && ($title || $number || $stage)) {
        $three = "
                <div class='infomenu'>
                    <div class='content'>
                        {$ab}
                        <div class='boxim'>
                            <div class='leftblock'>
                                <h1>{$name}</h1>
                            </div>
                            <div class='rightblock'>
                                <ul>
                                    <li id='liinformationid'><a href='#'>Информация</a></li>
                                    <li id='licommentsid' class='active'><a href='#'>Комментарии</a></li>
                                    <li id='lijournalid'><a href='#'>Журнал</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
        ";
    }

    if ($tabs) {
        $li = '';
        foreach ($tabs as $tab) {
            if ($currentURI == $tab['link']) {
                $active = 'active';
            }
            $tl = $tab['link'];
            $tt = $tab['title'];
            $li .= "<li><a class='{$active}' href='{$tl}'>{$tt}</a></li>";
            unset($active);
        }

        if ($floatingButton) {
            $t = $floatingButton['title'];
            $ti = $floatingButton['data-openwindow'];
            $tc = $floatingButton['data-call'];
            $FlB = "<div class='floatingButton'><a data-openwindow='{$ti}' data-legacy='yes' data-call='{$tc}' href='{$t}'>+</a></div>";
        }


        $menu = "
            <nav class='topmenu'>
                <div class='content'>
                    <ul class='nav'>
                        {$li}
                    </ul>
                    {$FlB}
                </div>
            </nav>
        ";
    }

    $switching = '';
    $rightside = '';
    $doctitle = '';
    $docblock = '';
    $urn = '';

    if ($modal) {
        foreach ($modal as $mo) {
            $w1 = $mo['data-link1'];
            $w2 = $mo['data-link2'];
            if ($mo['urndoc']) {
                $urn = $mo['urndoc'];
                if ($mo['doctitle']) {
                    $doctitle = $mo['doctitle'];
                }

                $rightside = "
                    <div class='rightblock' id='rb'>
                        <p class='item'>
                            Новая версия документа
                        </p>
                    </div>
                ";

                $switching = "switching();";

                $docblock = <<<DOC

                <div class="toastradio">
                    <input type="checkbox" name="initiation" id="c">
                    <label class="toastlabel" for='c'>Сделать текущий документ инициирующим</label>
                </div>
                <!--
                <div class="toastradio">
                    <input type="checkbox" name="nonprocessed" id="nonprocessedopt">
                    <label class="toastlabel" for='ca'>Вне процесса</label>
                </div>
                -->

                <div class="insidelink">
                    <a href="#">{$doctitle}</a>
                    <input type="hidden" name="urn" value="{$urn}" id="urnwin">
                </div>
DOC;
            }
        }



        $window = <<<HTML
<div id="tosatelement" class="hide" data-link1="{$w1}" data-link2="{$w2}">
    <div class="toast animated" style="position: relative;">
        <div class="toasttop">
            <img id="segmentClose" style="" src="/img/close.png">

            <div class="leftblock" id="lb">
                <p class="item active">
                    Новый документ
                </p>
            </div>
            {$rightside}

        </div>
        <div id="toastleftblock">
            <form   style="width: 400px; margin: 40px auto 0; text-align: left;" action='' >
                <label class="toastlabel">Класс документа</label>
                <div class="selectblock">
                    <select required="required" class="FS14" id="selectid1" name="class">
                        <option selected="selected">Не выбрано</option>
                    </select>
                </div>
                <div id="selblock2" style="display: none">
                    <label class="toastlabel">Тип документа</label>
                    <div class="selectblock">
                        <select required="required" class="FS14" id="selectid2" name="type" >
                            <option value="" selected="selected">Не выбрано</option>
                        </select>
                    </div>
                </div>

                {$docblock}

                <button style="margin: 40px 0" class="IBLK w200 GBTN" type="submit" id="submitwin">Отправить</button>
            </form>
        </div>
        <div id="toastrightblock" style="display: none">
            <div class="toastradio">
                <input type="checkbox" name="initiation2" id="c2">
                <label class="toastlabel" for='c2'>Сделать текущий документ инициирующим</label>
            </div>
            <p class="tbox" style="margin: 0">
                Документ:
                <a href="#">{$doctitle}</a>
                <input type="hidden" name="urn" value="{$urn}" id="urnwin2">
            </p>
            <div style="margin-top: 15px;">
                <label>Причина создания новой версии</label>
                <textarea id="causeNew" placeholder="Введите причину создания новой версии"></textarea>
            </div>
            <div class="text">
                <p>Создавая новую версию документа Вы инициируете процесс его утверждения.</p>
                <p>До утверждения новой версии во всех списках будет появляться только действующая версия.</p>
                <p>Сразу после утверждения новой версии старая становится архивной и пропадает с листингов, но доступна с поиске. В самом документе старая версия имеет ссылку на новую версию.</p>
            </div>
            <button style="margin: 40px 0" class="IBLK w200 GBTN" type="submit" id="submitwin2">Отправить</button>
        </div>
        <span></span>
    </div>
</div>
<script>
    {$switching}
    closeModalWindow();
    collectform();
</script>
HTML;
    }


    echo "
    <div class='topnav'>
        <div class='top content'>
            <div class='FL'>
                <a href='/'><img src='/img/logo.png' style='width: 47px;'></a>
            </div>
            <div class='IBLK vam'>
                <form method='post' action='/'>
                    <input type='text' class='w4'>
                    <input type='submit' value='' class=''>
                </form>
            </div>
            <div class='FR'>
                <img src='/img/account.png' class='CP' style='width: 25px;'>
            </div>
        </div>
    </div>
    <div id='notification' class='animated'>
        <div class='content'>
            <p class='nottext'>Ошибка загрузки</p>
            <p class='notimg'>
                <img src='/img/closebig.png'>
            </p>
        </div>
    </div>
    {$two}
    {$menu}
    {$three}
    {$window}
    ";
}
