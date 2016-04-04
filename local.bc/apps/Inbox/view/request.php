<?php
extract($this->context);

?>
<header />

<br style="clear: both;">

<div class="main">
    <div class="account">
        <div class="column">
            <p class="title">Документы</p>

            <?php
            foreach ($unidocs as $classTitle => $items) {
                ?>
            <div class="group">
                <p class="gname"><?= $classTitle ?></p>
                <ul>
                  <?php

                  if ($classTitle == 'Регламентирующие' && count($tas)>0) {
                      foreach ($tas as $ta) {
                          ?>
                          <li><a href="/questionnaire/result/<?=$ta->id?>">Результат по <?=$ta->DocumentRegulationsSOP->title?></a></li>
                      <?php

                      }
                  }

                foreach ($items as $d) {
                    // d - не dataset, struct!
                    if ($d['seenNeeded']) {
                        $openTag = '<b>';
                        $closeTag = '</b>';
                    } else {
                        $openTag = '';
                        $closeTag = '';
                    }
                    ?>
                    <li><?= $openTag ?><a href="/universaldocument/<?= $d['code'] ?>"><?= $d['code'].' '.$d['title'] ?> <?= $d['id'] ?></a><?= $closeTag ?></li>
                  <?php

                }
                ?>
                 </ul>
            </div>
            <?php

            }
            ?>


        </div>
        <div class="column inside">
            <p class="title">Входящие</p>

            <?php
            foreach ($inbox as $date => $items) {
                ?>
            <div class="group">
                <p class="gname"><?= $date ?></p>
                <ul>
                    <?php
                    foreach ($items as $d) {
                        ?>
                        <li>
                            <div class="liblock">
                                <a href="<?= $d['actHREF'] ?>" <?= $d['actLINK'] ?>><?= $d['subject'] ?></a> <?= $d['force'] ?>
                                <p class="event">
                                    <span><?= $d['stage'] ?></span>
                                    <span class="FR"><?= $d['date'] ?></span>
                                </p>
                            </div>
                        </li>
                        <?php

                    }
                ?>
                </ul>
            </div>
            <?php

            }
            ?>

        </div>
        <div class="column employee">
            <p class="title">Сотрудники</p>

            <?php
            foreach ($myEmployeeInboxes as $employeePostWithInbox) {
                ?>
            <div class="group">
                <p class="gname"><?= $employeePostWithInbox['post']->employee->title ?><span><?= $employeePostWithInbox['post']->title ?></span></p>
                <ul>
                    <?php
                    foreach ($employeePostWithInbox['inboxes'] as $d) {
                        ?>
                    <li>
                      <div class="liblock">
                          <a href="<?= $d['actHREF'] ?>" <?= $d['actLINK'] ?>><?= $d['subject'] ?></a> <?= $d['force'] ?>
                          <p class="event">
                              <span><?= $d['stage'] ?></span>
                              <span class="FR"><?= $d['date'] ?></span>
                          </p>
                      </div>
                    </li>

                    <?php

                    }
                ?>

                </ul>
            </div>


            <?php

            } ?>

        </div>
        <div class="column almanac">
            <p class="title">Календарь</p>

            <?php
            foreach ($processEvents as $monthTitle => $items) {
                ?>
            <div class="group">
                <p class="gname">Январь</p>
                <ul>
                    <?php
                    foreach ($items as $d) {
                        ?>
                      <li>
                          <div class="liblock">
                              <p>22.11.2015 14:00</p>
                              <a href="#">Документ «проол протокол проверки»</a>
                          </div>
                      </li>
                    <?php

                    }
                ?>
                </ul>
            </div>
            <?php

            } ?>


            <!-- next montn -->

        </div>
    </div>
</div>

<script>

    var gname = document.getElementsByClassName('gname');
    var column = document.getElementsByClassName('column');

    for(var j = 0; j < column.length; j++){
        try {
        column[j].querySelectorAll('.group')[0].querySelector('ul').style.display = 'block';
        column[j].querySelectorAll('.group')[0].querySelector('ul').style.visibility = 'visible';
        column[j].querySelectorAll('.group')[0].querySelectorAll('.gname')[0].style.backgroundImage = 'url("/img/arrow-up.png")';
        } catch (e) {}
    }

    for(var i = 0; i < gname.length; i++) {
        Event.add(gname[i], 'click', function (e) {
            e.preventDefault();
            if(this.parentNode.querySelector('ul').style.display != 'block') {
                this.parentNode.querySelector('ul').style.visibility = 'visible';
                this.parentNode.querySelector('ul').style.display = 'block';
                this.style.backgroundImage = 'url("/img/arrow-up.png")';

            }else{
                this.parentNode.querySelector('ul').style.visibility = 'hidden';
                this.parentNode.querySelector('ul').style.display = 'none';
                this.style.backgroundImage = 'url("/img/arrow-down.png")';

            }
        });
    }
</script>
