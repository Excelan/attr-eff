<?php
extract($this->context);


echo "<input type='hidden' id='ticketurn' value='$ticketurn'>";
echo "<input type='hidden' id='mpe' value='$mpe->urn'>";
echo "<input type='hidden' id='mpeid' value='{$mpe->uuid}'>";
echo "<input type='hidden' id='processproto' value='$mpe->prototype'>";
echo "<input type='hidden' id='subjectURN' value='$mpe->subject'>";





$m = new Message();
$m->action = 'load';
$m->urn = 'urn:People:Employee:Internal';
$m->ManagementPostIndividual = 'urn:Management:Post:Individual:'.$this->managementrole->id;
$useremployee = $m->deliver();


//загрузка результатов по ід опросника
$m = new Message();
$m->action = 'load';
$m->urn = $mpe->subject;
$ASR = $m->deliver();

$surn = (string)$ASR->DocumentRegulationsSOP->urn;
$TAUrn = (string)$ASR->DocumentRegulationsTA->urn;

$sURN = new URN((string)$TAUrn);
$study = $sURN->resolve()->current();

$m = new Message();
$m->action = 'load';
$m->urn = 'urn:People:Employee:Internal';
$m->istrener = 1;
$employee = $m->deliver();

if(count($employee) > 1) throw new Exception("too much istrener");


//сразу по открытию тестирования закрываем тикет, для предотвращения поторного открытия тестирования (кроме тренера)
if((string)$employee->ManagementPostIndividual->urn != (string)$this->managementrole->urn) {
    $m = new Message();
    $m->action = 'update';
    $m->urn = (string)$ticketurn;
    $m->isvalid = false;
    $m->allowknowcuurentstage = false;
    $m->allowopen = false;
    $m->allowsave = false;
    $m->allowcomplete = false;
    $m->deliver();
}




?>

<header />


<?if((string)$employee->ManagementPostIndividual->urn == (string)$this->managementrole->urn) {?>

    <div class="main" >
        <div class="content">

            <div class="buttons"><p class="itext">Завершить выполнение этапа процесса</p><input type="button" id="goNextStage" value="Отправить" class="gin"></div>
        </div>
    </div>
    <script>
        goNextStage('closeTicket');
    </script>

<?}else{?>

<div class="main" >
    <div class="content">
        <div class="contentblock">
            <input type="hidden" id="managementroleId" value="<?=$this->managementrole->id?>">
            <input type="hidden" id="urnQuestionnaireId" value="<?=$TAUrn?>">
            <p class="alltime">Общее время    (   <span id="alltime">00:00:-1</span>   )</p>
            <div class="questionnaire" id="questionnaire">

            </div>

            <div id="questionareResult" style="display: none;">
                <div class="participator">
                    <p>
                        <span>ФИО</span>
                        <span><?=$useremployee->title?></span>
                    </p>
                    <p>
                        <span>Должность</span>
                        <span><?=$this->managementrole->title?></span>
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
                    <a href="/">Выйти</a>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="overlay"></div>
<script>
    questionnaire('/Questionnaire/getQA','<?=$TAUrn?>');
</script>
<?}?>