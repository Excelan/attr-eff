<?php
extract($this->context);

echo "<input type='hidden' id='unidocURN' value='{$unidoc->urn}'>";
echo "<input type='hidden' id='subjectURN' value='$subjectURN'>";

?>

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

                  <?php
                  //println($unidoc);
                  foreach ($unidoc->hardmaster as $hardmasterurns) {
                      $hardmasterURN = new URN($hardmasterurns);
                      $hardmaster = $hardmasterURN->resolve()->current();
                      //println($hardmaster);
                      print "<img src='{$hardmaster->image['uri']}'>";
                  }
                   ?>

                  <form id='hardcopyuploader' data-upload="urn:Media:UniversalImage:Container" data-eachuploaded="onEachUploadedHardCopy" data-alluploaded="noop">
                      <input type="file" multiple />
                      <div id='uploadpreview'></div>
                  </form>
                  <script>
                  function onEachUploadedHardCopy(d)
                  {
                    console.log(d.response);
                    var preview = document.createElement("img");
                    preview.src = d.response.destination.image.uri;
                    preview.classList.add('uploadedPreview');
                    id('uploadpreview').appendChild(preview);

                    var data = {UniversalDocumentURN: id('unidocURN').value, hardmasterURN: d.response.destination.urn};
                    ajax('/DMS/UniversalDocument/AttachHardMaster', debuginput, {'onError': debuginput, 'onStart': noop, 'onDone': noop}, 'POST', data);
                  }
                  new GCFileUpload(id('hardcopyuploader'));
                  </script>





                </div>


                <?php

                $seenNeededDS = $unidoc->DMSAcquaintanceDocument;
                $seenNeeded = null;
                if (count($seenNeededDS)) {
                    $seenNeeded = !$seenNeededDS->done;
                }
                if ($seenNeeded) {
                    ?>
                    <div class="buttons">
                        <input type="hidden" id="unidoc" value="<?= $unidoc->urn ?>">
                        <a class="gin" href="#" data-param='vising' id="dms_seen">Ознакомлен</a>
                    </div>
                    <script>
                    dms_seen_set();
                    </script>

                    <?php

                }

              ?>

            </div>
            <!-- RIGHT -->
            <div class="rightblock">
                <!-- RIGHT INFO TAB -->
                <div id="informationid" style="display: none">

                    <status />

                    <rightbox />

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

</script>
