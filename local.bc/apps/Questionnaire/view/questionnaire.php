<?php
extract($this->context);
?>

<header />

<div class="main">
    <div class="content">
        <div class="contentblock">
                <input type="hidden" id="managementroleId" value="<?=$this->managementrole->id?>">
                <p class="alltime">Общее время    (   <span id="alltime">00:00:-1</span>   )</p>
                <div class="questionnaire" id="questionnaire">

                </div>

                <div id="questionareResult" style="display: none;">
                   <div class="participator">
                       <p>
                           <span>ФИО</span>
                           <span>Василий Пупкин</span>
                       </p>
                       <p>
                           <span>Должность</span>
                           <span>Менеджер по поставке</span>
                       </p>
                   </div>
                   <div class="yourResult">
                       <p>Ваш результат</p>
                   </div>
                   <div class="dataCertification">
                       <div class="leftResult">
                           <p>Время старта <span id="currentTimeId">12:00</span></p>
                           <p>Время окончания <span id="endTestTimeId">12:05</span></p>
                       </div>
                       <div class="rightResult">
                           <p>Кол-во неправильных ответов <span id="wrongAnswer"></span></p>
                           <p>Кол-во правильных ответов <span id="rightAnswer"></span></p>
                       </div>
                   </div>
                   <div class="successful">
                       <p id="certificationSuccessfully">
                           Аттестация прошла успешно. Доступ к работе есть
                       </p>
                   </div>
                   <div class="withdraw">
                       <a href="#">Выйти</a>
                   </div>
                </div>
        </div>
    </div>
</div>

<div id="overlay"></div>
<script>
    questionnaire('/Questionnaire/getQA','urn:Document:Regulations:TA:2012273834');
</script>