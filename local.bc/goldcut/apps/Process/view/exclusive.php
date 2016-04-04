<?php
extract($this->context);

echo "<input type='hidden' id='ticketurn' value='$ticketurn'>";
echo "<input type='hidden' id='mpe' value='$mpeurn'>";
echo "<input type='hidden' id='mpeid' value='{$mpeurn->uuid}'>";
echo "<input type='hidden' id='processproto' value='$processproto'>";
echo "<input type='hidden' id='subjectURN' value='$subjectURN'>";
echo "<input type='hidden' id='currentstage' value='$currentstage'>";

?>

<lastcomment />
<header />
<div class="main">
    <div class="content">
        <div class="contentblock">
            <!-- MAIN LEFT -->
            <div class="leftblock">

                <!-- CONTENT -->
                <div class="mform">
                    <form id="managedform"
                          data-structure="/config/form/<?= $formpath ?>.xml?<?= rand(100, 999) ?>"
                          data-load="/universalload/<?= $formpath ?>"
                          data-save=""
                          data-goto=""
                          <?php if ($allowsave && !$decisionScreen) {
    echo 'data-saveenabled="yes"';
} ?>
                          <?php if ($allowcomplete && !$decisionScreen) {
    echo "data-controller=\"{$dataController}\"";
}  ?>
                          action="/universalsave/<?= $formpath ?>"
                          data-managedform="yes"
                          data-onsuccess="alertresult1"
                          <?php if ($_GET['debug']) {
    echo 'data-debug="yes"';
} ?>
                          data-onerror="alerterror">
                    </form>
                </div>


                <?php
                if ($decisionScreen) {
                    if ($decisiontype == 'Visa') {
                        $solutionvariants = $subject->solutionvariants;
                        foreach ($solutionvariants as $solutionvariant) {
                            $sv++;
                            //print '<div style="line-height: 200%; padding-left: 470px;"><input class="visavariant" data-visaid="' . $solutionvariant->urn . '" type="checkbox" style="margin-right: 5px;" name="var' . $sv . '"> Вариант решения №' . $sv . '</div>';
                            print '<div style="padding-left: 470px; border: 1px solid #ddd; border-radius: 2px; margin-bottom: 10px; padding-top: 4px; padding-bottom: 4px;">';
                            foreach ($solutionvariant->visedby as $visant) {
                                echo "<p style='font-size: 90%'>{$visant->title}, <span style='font-size: 100%'>{$visant->employee->title}</span></p>";
                            }
                            print '<p style="line-height: 200%; "><input class="visavariant" data-visaid="' . $solutionvariant->urn . '" type="checkbox" style="margin-right: 5px;" name="visavar"> Вариант решения №' . $sv . '</p></div>';
                        }
                    } elseif ($decisiontype == 'Approve') {
                        $solutionvariants = $subject->solutionvariants;
                        foreach ($solutionvariants as $solutionvariant) {
                            $sv++;
                            print '<div style="padding-left: 470px; border: 1px solid #ddd; border-radius: 2px; margin-bottom: 10px; padding-top: 4px; padding-bottom: 4px;">';
                            foreach ($solutionvariant->visedby as $visant) {
                                echo "<p style='font-size: 90%'>{$visant->title}, <span style='font-size: 100%'>{$visant->employee->title}</span></p>";
                            }
                            print '<p style="line-height: 200%; "><input class="visavariant" data-visaid="' . $solutionvariant->urn . '" type="radio" style="margin-right: 5px;" name="visavar"> Вариант решения №' . $sv . '</p></div>';
                        }
                    }

                    ?>
                    <!-- VISA               -->
                    <div class="buttons">

                        <input type="hidden" id="additionalparam" value="valueParam">
                        <input type="hidden" id="decisiongate" value="<?= $decisiongate ?>">

                        <p class="itext">
                            <a class="rin" href="#" data-param='cancel' id="cancelform">Отклонить</a>
                        </p>
                        <?if($currentstage == 'Approve'){?>
                            <a class="gin" href="#" data-param='vising' id="visingform">Утвердить</a>
                        <?}else{?>
                            <a class="gin" href="#" data-param='vising' id="visingform">Визировать</a>
                        <?}?>
                    </div>
                    <div class="cause">
                        <textarea id="cancelformtext" placeholder="Укажите причину отмены"></textarea>
                    </div>

                    <script src="/js/vising.js?101" type="text/javascript"></script>
                    <script>
                        visingform();
                    </script>

                    <!-- / CONTENT -->
                    <?php

                }
                ?>

            </div>
            <!-- RIGHT -->
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

                    <approver />

                    <link />

                    <center>
                        <br>

                    <?php if ($pdf) {
    ?>

                    <div id="getpdf" class=""><a id="pdfhref" style='color: #555;' href='#WAIT'>PDF</a></div>

                    <script>
                    hide(id('getpdf'));
                    ajax('/HardCopy/GetPDF', function(d) {

                        console.log(d);
                        console.log(d.uri);
                        show(id('getpdf'));
                        id('pdfhref').setAttribute('href', d.uri);

                    }, {
                        'onError': function(e, d) {
                            console.log('Error in get pdf', e, d);
                            //unFadeScreen.call();
                        },
                        //'onStart': fadeScreen,
                        'onDone': function() {  }
                    }, 'POST', {'docurn': '<?= $subjectURN ?>'});

                    </script>



                    </center>

                    <?php
} ?>

                </div>
                <!-- COMMENTS TAB -->
                <div id="commentsid">
                    <comments />
                    <!--

<?php

//права доступа к написанию комментариев
if (true) {
    $permissions = 'FirstLevel';
}


$CAPA = strpos($subjectURN, 'Capa');
    if ($CAPA) {
        $menu = '';
        $m = new Message();
        $m->action = 'load';
        $m->urn = $subjectURN;
        $deviations = $m->deliver();

        $capa_html = <<<HTML
<div class="manyComments" id="{$deviations->id}">
    <script>
        capaCommentsAjax('{$deviations->id}','{$deviations->urn}', '{$permissions}');
    </script>
</div>
HTML;
        $menu_deviation .= "<li><a href='#' data-commid='$deviations->id'>1</a></li>";


        $correction_html = '';
        $solution_html = '';

        if (count($deviations)) {
            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Document:Correction:Capa';
            $m->DocumentCapaDeviation = $deviation->urn;
            $corrections = $m->deliver();

            if (count($corrections)) {
                $menu_correction = '';
                foreach ($corrections as $correction) {
                    $correction_html .= "
                        <div class='manyComments' id='$correction->id'>
                            <script>
                                capaCommentsAjax('$correction->id','$correction->urn', '$permissions');
                            </script>
                        </div>";

                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:Document:Solution:Correction';
                    $m->DocumentCorrectionCapa = $correction->urn;
                    $solutions = $m->deliver();

                    if (count($solutions)) {
                        $menu_solution = '';
                        foreach ($solutions as $solution) {
                            $correction_html .= "
                            <div class='manyComments' id='$solution->id'>
                                <script>
                                    capaCommentsAjax('$solution->id','$solution->urn', '$permissions');
                                </script>
                            </div>";
                            $menu_solution .= "<li><a href='#' data-commid='$solution->id'>1</a></li>";
                        }
                    }


                    $menu_correction .= "<li><a href='#' data-commid='$correction->id'>1</a></li>".$menu_solution;
                }
            }
        }

        $html = $capa_html.$correction_html.$solution_html;

        echo $menu."</ul>";
        echo $html;
    } else {
        echo "<comments />";
    }

?>
                    -->
                </div>
                <!-- JOURNAL TAB -->
                <div id="journalid" style="display: none">

                    <journal />

                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_static" class="windowcenter BLK g8 hide">Static html in div</div>

<script>

    switchinginfo();
    fixedRightBar();
    getLastRedComment();

</script>
