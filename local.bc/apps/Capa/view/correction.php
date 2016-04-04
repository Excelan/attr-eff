<?php
extract($this->context);
$capaUrn = $deviations->urn;

//если юзер есть в списке должностей исполнителей решения
$saveButton = 0;

//инициатор
if($mpe->initiator == $managementrole->urn) $initiator = true;
//if($managementrole->urn == $ticket->ManagementPostIndividual->urn) $initiator = true;


//права доступа к написанию комментариев
if(true)$permissions = 'FirstLevel';

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
            <div id="informationid" style="display: none">

                <status />

                <rightbox />

                <link />

            </div>

            <!-- COMMENTS TAB -->
            <div id="commentsid" >
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
                            $realizationtype = '';
                            if($solution->realizationtype == 'without_contractor_without_money') $realizationtype = 'Без подрядчиков и покупки материалов';
                            if($solution->realizationtype == 'without_contractor_with_money') $realizationtype = 'Без подрядчиков с покупкой материала';
                            if($solution->realizationtype == 'with_contractor_without_money') $realizationtype = 'С подрядчиками без покупки материала';
                            if($solution->realizationtype == 'with_contractor_with_money') $realizationtype = 'С подрядчиками с покупкой материала';

                            $disabledEdit = 'disabled';
                            $v_counter++;

                            $v_first = $v_counter == 1 && $p_counter == 1 && $e_counter == 1 ? '' : 'hide';
                            $v_last = $v_counter == $v_total ? 'lastcell' : '';

                            $updateFormFlag = '';
                            if($managementrole->urn == $correction->controlresponsible->urn){
                                $disabledEdit = '';//можно редактировать
                                $saveButton++;//кнопка сохранить
                                $updateFormFlag = 'data-allform'; //апдейт только форм, которые можно редактировать
                            }
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
                                            </div>
                                            <form <?=$updateFormFlag?> class="solutionUpdateForm" action="/Capa/UpdateSolution" method="post" data-onsuccess="UpdateSolutionDone" data-managedform="yes">
                                                <input type="hidden"  value="<?=$solution->urn?>" data-selector="urn">
                                                <input type="hidden"  value="<?=$mpe->urn?>" data-selector="mpe">
                                                <div class="infobody">
                                                    <label>Тип решения:</label>
                                                    <select <?=$disabledEdit?> data-selector="realizationtype">
                                                        <?if($disabledEdit == ''){?>
                                                            <option <?if($solution->realizationtype == 'without_contractor_without_money') echo 'selected';?> value="without_contractor_without_money">Без подрядчиков и покупки материалов</option>
                                                            <option <?if($solution->realizationtype == 'without_contractor_with_money') echo 'selected';?> value="without_contractor_with_money">Без подрядчиков с покупкой материала</option>
                                                            <option <?if($solution->realizationtype == 'with_contractor_without_money') echo 'selected';?> value="with_contractor_without_money">С подрядчиками без покупки материала</option>
                                                            <option <?if($solution->realizationtype == 'with_contractor_with_money') echo 'selected';?> value="with_contractor_with_money">С подрядчиками с покупкой материала</option>
                                                        <?}else{?>
                                                        <option value="<?=$solution->realizationtype?>"><?=$realizationtype?></option>
                                                        <?}?>
                                                    </select>
                                                    <div class="infotime">
                                                        <div class="infdt">
                                                            <label>Дата реализации:</label>
                                                            <input type="date" <?=$disabledEdit?> value="<?=$solution->realizationdate?>" data-selector="realizationdate">
                                                        </div>
                                                        <div class="infsum">
                                                            <label>Сумма:</label>
                                                            <input type="number" <?=$disabledEdit?> value="<?=$solution->cost?>" data-selector="cost">
                                                        </div>
                                                    </div>
                                                    <label>Описание решения:</label>
                                                    <textarea data-selector="descriptionsolution" placeholder="Опишите решение" <?=$disabledEdit?>><?=$solution->descriptionsolution?></textarea>
                                                    <label>Исполнитель:</label>
                                                    <?
                                                    $m = new Message();
                                                    $m->action = 'load';
                                                    $m->urn = (string)$solution->executor->urn;
                                                    $exec = $m->deliver();

                                                    $m = new Message();
                                                    $m->action = 'load';
                                                    $m->urn = 'urn:Management:Post:Individual';
                                                    $execPosts = $m->deliver();
                                                    ?>


                                                    <select <?=$disabledEdit?> data-selector="executor">
                                                        <?if($disabledEdit == ''){

                                                            foreach($execPosts as $execPost){

                                                            ?>

                                                            <option <?if((string)$execPost->urn == (string)$exec->urn) echo 'selected';?> value="<?=(string)$execPost->urn?>"><?=$execPost->title?></option>
                                                        <?}}else{?>
                                                            <option value="<?=(string)$exec->urn?>"><?=$exec->nameofemployee?></option>
                                                        <?}?>
                                                    </select>


                                                </div>
                                            </form>
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

    <?if($saveButton > 0){?>

        <div class="content">
            <div class="contentblock">
                <div class="buttons c">
                    <a class="gin c" id="updateSolutionForm" data-allform >Сохранить</a>
                </div>
            </div>
        </div>

    <?}if($initiator){?>
        <div class="content">
            <div class="contentblock">
                <form action="/Capa/ToVised" method="post" data-onsuccess="toVised" data-onerror="toVisedError" data-managedform="yes">


                    <input type='hidden' data-selector='ticketurn' value='<?=$ticketurn?>'>
                    <input type='hidden' data-selector='mpe' value='<?=$mpe->urn?>'>
                    <input type='hidden' data-selector='mpeid' value='<?=$mpe->uuid?>'>
                    <input type='hidden' data-selector='urn' value='<?=$mpe->subject?>'>


                    <div class="buttons">
                        <p class="itext">Завершить выполнение этапа процесса</p>
                        <!--<input type="submit" value="Отправить" class="gin">-->
                        <!--<input type="button" id="nextInternal" value="Отправить" class="gin">-->
                        <input type="submit" value="Отправить" class="gin">
                    </div>
                </form>
            </div>
        </div>
        <script>
//            if (!id('nextInternal')) throw "No dom id nextInternal";
//            var nextStage = id('nextInternal');
//            Event.add(nextStage, "click", function(e){
//                actionCompleteStage();
//            });
        </script>
    <?}?>

<script>
    switchinginfo();
    showHideMoreInfoHeader();
    fixedRightBar();
</script>
</div>