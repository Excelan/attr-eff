<?php
extract($this->context);


//Люди которым позволено утверждать
$approvePeople = array();
//если юзер в списке тогда ->
if(true) $allowed = true;


//права доступа к написанию комментариев
if(true) $permissions = 'FirstLevel';

echo "<input type='hidden' id='ticketurn' value='$ticketurn'>";
echo "<input type='hidden' id='mpe' value='$mpe->urn'>";
echo "<input type='hidden' id='mpeid' value='{$mpe->uuid}'>";
echo "<input type='hidden' id='processproto' value='$mpe->prototype'>";
echo "<input type='hidden' id='subjectURN' value='$mpe->subject'>";

?>

<header />

<div class="infomenu loadMoreInfo" style="display: none">
    <div class="content">

        <div class="boxim">
            <div class="leftblock">
                <h1><span style="">Информационный блок -&gt;</span></h1>
            </div>
            <div class="rightblock">
                <ul>
                    <li id="liinformationid"><a href="#">Информация</a></li>
                    <li class="active" id="licommentsid"><a href="#">Комментарии</a></li>
                    <li id="lijournalid"><a href="#">Журнал</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="main">

    <div class="underheaderinfo loadMoreInfo" style="display: none">
        <div class="rightblock">

            <div class="hiddenNavButton" style="display: none;">
                <ul>
                    <li id="liinformationid2" class=""><a href="#">Информация2</a></li>
                    <li class="active" id="licommentsid2"><a href="#">Комментарии</a></li>
                    <li id="lijournalid2"><a href="#">Журнал</a></li>
                </ul>
            </div>

            <!-- RIGHT INFO TAB -->
            <div id="informationid">

                <status />

                <rightbox />

                <link />

            </div>

            <!-- COMMENTS TAB -->
            <div id="commentsid">
                <comments />
            </div>

            <!-- JOURNAL TAB -->
            <div id="journalid" style="display: none">
                <journal />
            </div>
        </div>
    </div>



    <div class="content">
        <div class="contentblock topbox">
            <div class="part">

                <div class="titleinpart">
                    Отклонение 1
                </div>

                <div class="partparagraph">
                    <label>Описание:</label>
                    <p><?=$deviations->descriptiondeviation?></p>
                </div>

                <div class="titleinpart">
                    Риски
                </div>

                <div class="attachedfilepart">
                    <label class="IBLK w3mw FS15" for="">Идентифицированные риски:</label>
                    <div class="attachedlist">
                        <?
                        if(count($RiskManagementRiskApproved)){
                            foreach($RiskManagementRiskApproved as $risk){
                                ?>
                                <a href="#"><?=$risk->title?></a>
                            <?}}?>
                    </div>
                </div>

                <div class="attachedfilepart">
                    <label class="IBLK w3mw FS15" for="">Неидентифицированные риски:</label>
                    <div class="attachedlist">
                        <?
                        if(count($DocumentRiskNotApproved)){
                            foreach($DocumentRiskNotApproved as $NotRisk){
                                ?>
                                <a href="#"><?=$NotRisk->riskdescription?></a>
                            <?}}?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div style="max-width: 1200px; margin: 0 auto">

        <?php

        $p_counter = 0;
        $p_total = count($deviations);
        foreach ($deviations as $deviation) {
            $p_counter++;

            $p_first = $p_counter == 1 ? '' : 'hide';
            $p_last = $p_counter == $p_total ? 'lastcell' : '';
            ?>
            <div style="width: 1200px;" class="actionProgramm T">
                <div class="lineProgramm RR">

                    <?php

                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:Document:Correction:Capa';
                    $m->DocumentCapaDeviation = $deviation->urn;
                    $corrections = $m->deliver();

                    $e_counter = 0;
                    $e_total = count($corrections);
                    foreach ($corrections as $correction) {
                        $e_counter++;

                        $e_first = $e_counter == 1 && $p_counter == 1 ? '' : 'hide';
                        $e_last = $e_counter == $e_total ? 'lastcell' : '';
                        ?>

                        <div class="actionProgramm T2">
                            <div class="w4 lineProgramm R2 end <?=$e_last?>">
                                <div class="titlecolumn <?=$e_first?>">
                                    <p>Мероприятия</p>
                                </div>
                                <div class="problemlist">
                                    <div class="infoblock">
                                        <div class="infotitle">
                                            <p><?=$p_counter?>.<?=$e_counter?>-Мероприятие</p>
                                        </div>
                                        <div class="infobody">
                                            <label>Описание мероприятия:</label>
                                            <textarea placeholder="Опишите мероприятие" disabled><?=$correction->descriptioncorrection?></textarea>
                                            <label>Ответственный:</label>
                                            <select disabled>
                                                <option><?=$correction->controlresponsible->title?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="lineProgramm R2">

                                <?php

                                $m = new Message();
                                $m->action = 'load';
                                $m->urn = 'urn:Document:Solution:Correction';
                                $m->DocumentCorrectionCapa = $correction->urn;
                                $solutions = $m->deliver();

                                $v_counter = 0;
                                $v_total = count($solutions);
                                foreach ($solutions as $solution) {
                                    $v_counter++;

                                    $v_first = $v_counter == 1 && $p_counter == 1 && $e_counter == 1 ? '' : 'hide';
                                    $v_last = $v_counter == $v_total ? 'lastcell' : '';
                                    ?>

                                    <div class="actionProgramm T3">
                                        <div class="w4 lineProgramm R3 end <?if($e_last == 'lastcell') echo $v_last?>">
                                            <div class="titlecolumn <?=$v_first?>">
                                                <p>Решения</p>
                                            </div>
                                            <div class="problemlist">
                                                <div class="infoblock">
                                                    <div class="infotitle vam">
                                                        <p class="IBLK"><?=$p_counter?>.<?=$e_counter?>.<?=$v_counter?>-Решение</p>
                                                        <?if($allowed){?>
                                                            <div class="labelinline FR">
                                                                <input type="radio" id="<?=$solution->urn?>" name="<?=$correction->urn?>" class="IBLK radioButtonSolution">
                                                                <label for="<?=$solution->urn?>">Утвердить</label>
                                                            </div>
                                                        <?}?>
                                                    </div>
                                                            <?
                                                                $m = new Message();
                                                                $m->action = 'members';
                                                                $m->urn = new URN($solution->urn.':visauser');
                                                                $listMembers = $m->deliver();


                                                                $m = new Message();
                                                                $m->action = 'load';
                                                                $m->urn = 'urn:Management:Post:Individual';
                                                                $m->in = $listMembers;
                                                                $resut = $m->deliver();

                                                                if(count($resut)>0){?>

                                                        <div class="recomendetuserblock TAR">
                                                            <div class="IBLK FL">Рекомендовано</div>
                                                            <div class="IBLK">
                                                                <?
                                                                foreach($resut as $v){
                                                                    echo "<p>".$v->title."</p>";
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    <?}?>
                                                    <div class="infobody">
                                                        <label>Тип решения:</label>
                                                        <select disabled>
                                                            <option><?=$solution->realizationtype?></option>
                                                        </select>
                                                        <div class="infotime">
                                                            <div class="infdt">
                                                                <label>Дата реализации:</label>
                                                                <select disabled>
                                                                    <option><?=$solution->realizationdate?></option>
                                                                </select>
                                                            </div>
                                                            <div class="infsum">
                                                                <label>Сумма:</label>
                                                                <input type="text" disabled value="<?=$solution->cost?>">
                                                            </div>
                                                        </div>
                                                        <label>Описание решения:</label>
                                                        <textarea placeholder="Опишите решение" disabled><?=$solution->descriptionsolution?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w4 lineProgramm R3 end <?if($v_last == 'lastcell' && $e_last == 'lastcell') echo 'lastcell';?>">
                                            <div class="titlecolumn <?=$v_first?>">
                                                <p>Комментарии</p>
                                            </div>
                                            <div class="problemlist">
                                                <div class="infoblock">
                                                    <div class="infotitle">
                                                        <p><?=$p_counter?>.<?=$e_counter?>.<?=$v_counter?>-Комментарии</p>
                                                    </div>
                                                    <div class="capaComments" id="<?=$solution->id?>">
                                                        <script>
                                                            capaCommentsAjax('<?=$solution->id?>','<?=$solution->urn?>', '<?=$permissions?>');
                                                        </script>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?}?>

                            </div>
                        </div>

                    <?}?>

                </div>
            </div>

        <?}?>

    </div>

    <?if($allowed){?>

    <div class="underform">
        <div class="box" style="margin: 0">
            <div class="titleblock">Принять решение по документу</div>
            <div class="innerbox">
                <div class="entry">
                    <label>Оставте свой комментарий или укажите причину отмены:</label>
                    <div class="values" >

                        <input type="hidden" value="valueParam" id="additionalparam">
                        <input type="hidden" value="/Decision/VisaByOne" id="decisiongate">

                        <textarea class="w0 TM0" placeholder="Введите свой комментарий" id="cancelText"></textarea>
                    </div>
                </div>
                <div class="buttonblock">
                    <button class="IBLK w200 RBTN FL" type="submit" capa-urn='<?='urn:Document:Capa:Deviation:'.$id?>' id="cancel_vise_btn">Отклонить</button>

                    <button class="IBLK w300 GBTN FR" id="send_approve_btn">Утвердить</button>
                    <p class="w200 subbtext">вернуть документ на стадию редактирования</p>
                </div>
            </div>
        </div>
    </div>

    <?}?>
</div>


<script>
    <?if($allowed){?>
    approvingStage();
    <?}?>

    switchinginfo();
    showHideMoreInfoHeader();
    fixedRightBar();
</script>