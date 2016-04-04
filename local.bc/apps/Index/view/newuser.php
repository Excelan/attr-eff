<?php
extract($this->context);

echo "<input type='hidden' id='mpe' value='$mpeurn'>";
echo "<input type='hidden' id='mpeid' value='{$mpeurn->uuid}'>";
echo "<input type='hidden' id='processproto' value='$processproto'>";
echo "<input type='hidden' id='subjectURN' value='$subjectURN'>";

?>
<script>
    GC.CALLBACKS['formsaved'] = function (e) {
        console.log('DONE')
        console.log(e)
        notification('GOOD', 'Данные сохранены');
    }

    GC.CALLBACKS['formsavefailed'] = function (e) {
        console.log('FAIL:', e)
        notification('BAD', 'Данные не сохранены');
    }
</script>
<header />
<div class="main">
    <div class="content">
        <div class="contentblock">
            <div class="leftblock">
                <div class="mform">

                    <input type="hidden" id="initiator" value="<?= $user->urn ?>">

                    <!--  FORM  -->
                    <!-- <textarea placeholder="test" data-selector="claim-claimtext" name="xtext" class="richtext"></textarea>-->

<!--                    data-controller="processnext" -->

                    <form id="managedform"
                          data-structure="/config/form/Examples/newuser.xml"
                          data-load="/universalload/newuser"
                          data-save=""
                          <?php if (true) echo 'data-saveenabled="yes"'; ?>
                          action="/universalsave/newuser"
                          data-managedform="yes"
                          data-onsuccess="formsaved"
                          data-onerror="formsavefailed">
                    </form>

                    <!--  VISA

                    <div class="buttons">
                        <input type="hidden" id="additionalparam" value="valueParam">
                        <p class="itext">
                            <a class="rin" href="#" data-param='cancel' id="cancelform">Отклонить</a>
                        </p>
                        <a class="gin" href="#" data-param='vising' id="visingform">Визировать</a>
                    </div>
                    <div class="cause">
                        <textarea id="cancelformtext" placeholder="Укажите причину отмены"></textarea>
                    </div>

                     -->

                </div>

            </div>

            <!--  RIGHT  -->

            <div class="rightblock">

            </div>



        </div>
    </div>
</div>

<script>
    //switchinginfo();
    //visingform();


/*    setTimeout(function(){
            notification('GOOD', 'Ошибка загрузки');
    }, 3000);*/

</script>

<div id="overlay"></div>