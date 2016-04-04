<?php
extract($this->context);


echo "<input type='hidden' id='ticketurn' value='$ticketurn'>";
echo "<input type='hidden' id='mpe' value='$mpe->urn'>";
echo "<input type='hidden' id='mpeid' value='{$mpe->uuid}'>";
echo "<input type='hidden' id='processproto' value='$mpe->prototype'>";
echo "<input type='hidden' id='subjectURN' value='$mpe->subject'>";



$surn = $mpe->subject;//передаваемый урн в гейт (Если надо)

$m= new Message();
$m->action = 'load';
$m->urn = $surn;
$thisSub = $m->deliver();

if(isset($_GET['datefield'])){
    $param =  $_GET['datefield'];
    echo date('Y-m-d H:i',$thisSub->$param);
}





?>

<header />

<div class="main">
    <div class="content">
        <div class="contentblock">
            <div class="leftblock">
                <div class="calendarbox">
                    <?
                    if (isset($_GET['date'])) echo "выбрана дата ".$_GET['date'];
                    my_calendar(array(date("Y-m-d")),$surn);
                    ?>
                    <?
                    function my_calendar($fill=array(),$surn) {
                        $month_names=array("январь","февраль","март","апрель","май","июнь",
                            "июль","август","сентябрь","октябрь","ноябрь","декабрь");
                        if (isset($_GET['y'])) $y=$_GET['y'];
                        if (isset($_GET['m'])) $m=$_GET['m'];
                        if (isset($_GET['date']) AND strstr($_GET['date'],"-")) list($y,$m)=explode("-",$_GET['date']);
                        if (!isset($y) OR $y < 1970 OR $y > 2037) $y=date("Y");
                        if (!isset($m) OR $m < 1 OR $m > 12) $m=date("m");

                        $month_stamp=mktime(0,0,0,$m,1,$y);
                        $day_count=date("t",$month_stamp);
                        $weekday=date("w",$month_stamp);
                        if ($weekday==0) $weekday=7;
                        $start=-($weekday-2);
                        $last=($day_count+$weekday-1) % 7;
                        if ($last==0) $end=$day_count; else $end=$day_count+7-$last;
                        $today=date("Y-m-d");
                        $prev=date('?\m=m&\y=Y',mktime (0,0,0,$m-1,1,$y));
                        $next=date('?\m=m&\y=Y',mktime (0,0,0,$m+1,1,$y));
                        $i=0;
                        ?>
                        <table class="calendar">
                            <thead>
                            <tr class="calendar-month">
                                <th>
                                    <div class="calendar-month-navigation">
                                        <a href="<? echo $prev ?>">
                                            <span class="chevron-left"></span>
                                        </a>
                                    </div>
                                </th>
                                <th colspan="5">
                                    <span><? echo $month_names[$m-1]," ",$y ?></span>
                                </th>
                                <th>
                                    <div class="calendar-month-navigation">
                                        <a href="<? echo $next ?>">
                                            <span class="chevron-right"> </span>
                                        </a>
                                    </div>
                                </th>
                            </tr>
                            <tr class="week">
                                <th>ПН</th>
                                <th>ВТ</th>
                                <th>СР</th>
                                <th>ЧТ</th>
                                <th>ПТ</th>
                                <th>СБ</th>
                                <th>НД</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?
                            $g = new Message();
                            $g->gate = 'Events/GetNecessaryUser';
                            $g->month = date('F',strtotime('15-'.$m.'-'.$y));
                            $g->year = $y;
                            $g->urn = $surn;
                            $gate = $g->send();

                            for($d=$start;$d<=$end;$d++) {

                                $now="$y-$m-".sprintf("%02d",$d);

                                if (!($i++ % 7)) echo " <tr>\n";

                                if ($d < 1 OR $d > $day_count) echo '<td class="past prev-month">';
                                else{
                                    if (is_array($fill) AND in_array($now,$fill)){

                                        $coof = 0;
                                        foreach($gate['globMiss'] as $val){
                                            if($val == $d){ echo '<td class="today absence">'; $coof++;}
                                        }
                                        if($coof == 0) echo '<td class="today">';
                                    }
                                    else {
                                        $coof2 = 0;
                                        foreach($gate['globMiss'] as $v) {
                                            if($v == $d){ echo '<td class="absence">'; $coof2++;}
                                        }
                                        if($coof2 == 0){
                                            if($gate['day'] == 1 && $d == 1) $fd = 'today';
                                            echo '<td class="'.$fd.'">';
                                            unset($fd);
                                        }
                                    }
                                }

                                if ($d < 1 OR $d > $day_count) {
                                    echo "&nbsp";
                                } else {
                                    if (is_array($fill) AND in_array($now,$fill)) {
                                        echo '<h3 class="day"><span class="num">'.$d.'</span></h3>';
                                    } else {
                                        echo '<h3 class="day"><span class="num">'.$d.'</span></h3>';
                                    }
                                }
                                echo "</td>\n";
                                if (!($i % 7))  echo " </tr>\n";
                            }
                            ?>
                            </tbody>
                        </table>
                    <? } ?>

                </div>
                <div class="calendar-people">
                    <div class="toptitle">
                        <p>Участники события</p>
                    </div>
                    <div class="box" id="userListQA">
                    </div>
                </div>
                <div class="buttons c"><input type="submit" id="nextInternal" value="Сохранить" class="gin c"></div>
                <div class="buttons">
                    <p class="itext">Завершить выполнение этапа процесса</p>
                    <input type="button" id="goNextStage" value="Отправить" class="gin">
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

                <!-- RIGHT INFO TAB -->
                <div id="informationid">

                    <status />

                    <rightbox />

                    <link />

                </div>
                <!-- COMMENTS TAB -->
                <div id="commentsid" style="display: none">
                    <comments />
                </div>
                <!-- JOURNAL TAB -->
                <div id="journalid" style="display: none">

                    <journal />

                </div>

            </div>
        </div>
    </div>
</div>
<?
if (isset($_GET['y'])) $y=$_GET['y'];
if (!isset($y) OR $y < 1970 OR $y > 2037) $y=date("Y");

if (isset($_GET['m'])){
    $m = date('F',strtotime('15-'.$_GET['m'].'-'.$y));
    $g=$_GET['m'];
}
if (!isset($m) || $g < 1 || $g > 12) $m=date("F");


?>
<input id="dateMonthId" type="hidden" value="<?=date('m',strtotime('15-'.$m.'-'.$y))?>">
<input id="dateYearId" type="hidden" value="<?=date('Y',strtotime('15-'.$m.'-'.$y))?>">
<input id="datefieldId" type="hidden" value="<?=$_GET['datefield']?>">

<input id="checkDate" type="hidden" value="<?if(!is_null($thisSub->$_GET['datefield'])) echo date('d',$thisSub->$_GET['datefield']);?>">

<script>

    var urn = "<?=$surn?>";
    var gate = "/Events/GetNecessaryUser";
    var month = "<?=$m?>";
    var year = "<?=$y?>";
    switchinginfo();
    GetNecessaryUser(month,year,urn,gate);

    var sendGate = '/Events/SetEventDay';
    sendDataUser(sendGate);
    goNextStage();


    fixedRightBar();

</script>