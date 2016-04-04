<?php
extract($this->context);

$IdTA = $this->uri(2);

echo "<input type='hidden' id='subjectURN' value='urn:Document:Regulations:TA:$IdTA'>";

?>

<header />

<div class="main">
    <div class="content">
        <div class="contentblock">
            <div class="leftblock">
                <?if($_GET['post']){?>
                <div class="contentblock topbox">
                    <div class="part">
                        <div class="titleinpart">
                            Документация по обучению
                        </div>

                        <div class="attachedfilepart">
                            <label>Документ ПО:</label>
                            <div class="attachedlist">
                                <a href="#">Программа обучения 54654654 к 65465</a>
                            </div>
                        </div>

                        <div class="titleinpart" style="margin: 70px 0 0">
                            Участник аттестации
                            <a class="adda" href="<?=$this->context['currentURI']?>">Вернуться к списку участников</a>
                        </div>
                        <table class="data">
                            <thead>
                                <tr class="header">
                                    <th>ФИО</th>
                                    <th>Должность</th>
                                    <th>Результат</th>
                                    <th>Допуск к работе</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?=$useremployee->title?></td>
                                    <td><?=$userpost->title?></td>
                                    <td><?if($results->done == '1') echo "Успешно"; else echo "Не успешно";?></td>
                                    <td ><?if($results->done == '1') echo "Есть"; else echo "Нет";?></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="resultQuestionnaire">
                            <p>Время старта <span><?=date('Y/m/d H:i:s',$results->starttime)?></span></p>
                            <p>Время окончания аттестации <span><?=date('Y/m/d H:i:s',$results->endtime)?></span></p>
                            <p>Количество правильных <span><?=$results->trua?></span></p>
                            <p>Количество неправильных <span><?=$results->falsea?></span></p>
                            <p>Процентное соотношение <span><?=round(($results->trua*100)/($results->trua + $results->falsea), 2)."/".round(($results->falsea)*100/($results->trua + $results->falsea), 2)?></span></p>
                        </div>
                    </div>
                    <div class="questionnaireBlock">
                        <div>
                            <div class="questionnaireTitle">
                                <div class="title">Вопросы к освещению</div>
                                <div class="good">
                                    Отметка правильный(е) ответ(ы)
                                </div>
                                <div class="bad">
                                    Отметка неправильный(е) ответ(ы)
                                </div>
                            </div>
                            <div class="qTheme"><span>Тема: </span><p><?=$questionnaire->questiondescription?></p></div>

                            <?
                            $qnum = 0;
                            foreach($questions as $question){?>

                            <div class="questionnaireQA">
                                <div class="question">
                                    <p class="text">Текст вопроса</p>
                                    <p class="qtext"><?=++$qnum?>.<?=$question->content?></p>
                                </div>
                                <div class="answer">
                                    <p class="text">Ответ:</p>
                                    <?
                                        $m = new \Message();
                                        $m->action = 'load';
                                        $m->urn = 'urn:Study:RegulationStudy:A';
                                        $m->StudyRegulationStudyQ = $question->urn;
                                        $answers = $m->deliver();


                                        foreach($answers as $answer){

                                            $res = $results->useranswer;
                                            $statA = 'empty';
                                            for($i = 0; $i<count($res)-1; $i++) {

                                                foreach ($res[$i]->answer as $v) {
                                                    if ($answer->urn == $v && $answer->correctly == 'yes') $statA = 'good';
                                                    if ($answer->urn == $v && $answer->correctly != 'yes') $statA = 'bad';
                                                }
                                            }
                                    ?>
                                    <div class="atext">
                                        <p class="bgqa <?=$statA?>"></p>
                                        <p class="answertext <?=$statA?>"><?=$answer->content?></p>
                                    </div>
                                    <?}?>
                                </div>
                            </div>

                            <?}?>

                        </div>
                    </div>
                </div>
                <?}else{?>
                    <div class="contentblock topbox">
                        <div class="part">
                            <div class="titleinpart">
                                Документация по обучению
                            </div>

                            <div class="attachedfilepart">
                                <label>Документ ПО:</label>
                                <div class="attachedlist">
                                    <a href="#">Программа обучения 54654654 к 65465</a>
                                </div>
                            </div>

                            <div class="titleinpart">
                                Участник аттестации
                            </div>
                            <table class="data">
                                <thead>
                                <tr class="header">
                                    <th>ФИО</th>
                                    <th>Должность</th>
                                    <th>Результат</th>
                                    <th>Допуск к работе</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?

                                $allUserTest = [];
                                //получем программу обучения
                                $m = new Message();
                                $m->action = 'load';
                                $m->urn = 'urn:Document:Regulations:TA:'.$id;
                                $TA = $m->deliver();

                                //грузим участинков через СОП принедлежащий программе
                                $m = new Message();
                                $m->action = 'members';
                                $m->urn = new URN((string)$TA->DocumentRegulationsSOP->urn.':userprocedure');
                                $listMembers = $m->deliver();

                                $m = new Message();
                                $m->action = 'load';
                                $m->urn = 'urn:Management:Post:Individual';
                                $m->in = $listMembers;
                                $userposts = $m->deliver();//список участников


                                //добавляем в общий масив
                                foreach($userposts as $up){
                                    array_push($allUserTest,$up);
                                }

                                //грузим участинков по типу должности через СОП принедлежащий программе
                                $m = new Message();
                                $m->action = 'members';
                                $m->urn = new URN((string)$TA->DocumentRegulationsSOP->urn.':userproceduregroup');
                                $listMembers = $m->deliver();

                                $m = new Message();
                                $m->action = 'load';
                                $m->urn = 'urn:Management:Post:Group';
                                $m->in = $listMembers;
                                $postgroup = $m->deliver();//список Типов должностей

                                foreach($postgroup as $pg) {
                                    $m = new Message();
                                    $m->action = 'load';
                                    $m->urn = 'urn:Management:Post:Individual';
                                    $m->ManagementPostGroup = (string)$pg->urn;
                                    $userpostsMG = $m->deliver();//участник по Типу должности

                                    //добавляем в общий масив
                                        foreach($userpostsMG as $umg) {
                                            array_push($allUserTest, $umg);
                                        }
                                }



//                                    $m = new Message();
//                                    $m->action = 'load';
//                                    $m->urn = 'urn:Management:Post:Individual';
//                                    $userposts = $m->deliver();

                                    foreach($allUserTest as $userpost){

                                        //загрузка результатов по ід опросника
                                        $m = new Message();
                                        $m->action = 'load';
                                        $m->urn = 'urn:Study:RegulationStudy:R';
                                        $m->questionnaire = $id;
                                        $m->user = $userpost->id;
                                        $m->order = 'created desc';
                                        $m->last = 1;
                                        $results = $m->deliver();

                                        if(count($results) == 1){
                                            //загрузка сотрудников
                                            $m = new Message();
                                            $m->action = 'load';
                                            $m->urn = 'urn:People:Employee:Internal';
                                            $m->ManagementPostIndividual = 'urn:Management:Post:Individual:'.$userpost->id;
                                            $useremployee = $m->deliver();
                                            ?>
                                            <tr>
                                                <td><?=$useremployee->title?></td>
                                                <td><?=$userpost->title?></td>
                                                <td><?if($results->done == '1') echo "Успешно"; else echo "Не успешно";?></td>
                                                <td <?if($results->done != '1') echo "style='background-color: #ffbfbf'"; else echo "style='background-color: #cfffbf'";?>><?if($results->done == '1') echo "Есть"; else echo "Нет";?></td>
                                                <td><a href="?post=<?=$userpost->id?>">i</a></td>
                                            </tr>
                                        <?}else{
                                            //загрузка сотрудников
                                            $m = new Message();
                                            $m->action = 'load';
                                            $m->urn = 'urn:People:Employee:Internal';
                                            $m->ManagementPostIndividual = 'urn:Management:Post:Individual:'.$userpost->id;
                                            $useremployee = $m->deliver();
                                            ?>
                                            <tr>
                                                <td><?if(count($useremployee)>0) echo $useremployee->title; else echo 'No';?></td>
                                                <td><?=$userpost->title?></td>
                                                <td>Не проходил</td>
                                                <td style='background-color: #ffecbf'>Не проходил</td>
                                                <td><a href="#">i</a></td>
                                            </tr>
                                        <?}}?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?}?>
                <div class="buttonAblock">
                    <div class="lbutoon">
                        <a href="/questionnaire">Выйти</a>
                    </div>
                </div>
            </div>
            <div class="rightblock">
                <div class="hiddenNavButton" style="display: none;">
                    <ul>
                        <li id="liinformationid2" class=""><a href="#">Информация2</a></li>
                        <li class="active" id="licommentsid2"><a href="#">Комментарии</a></li>
                        <li id="lijournalid2"><a href="#">Журнал</a></li>
                    </ul>
                </div>
                <div id="informationid">

                    <status />

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

<div id="overlay"></div>
<script>
    switchinginfo();
    fixedRightBar();
</script>