<!DOCTYPE html>
<html lang="en">
<head>
     <?php $s="32"; ?>
     <meta charset="utf-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>Db</title>
     <script type="text/javascript" src="/goldcut/js/gcdom.js?<?=$s?>"></script>
     <script type="text/javascript" src="/lib/js/when/when.js?<?=$s?>"></script>
     <style>
          body {
               background: #E9E9E9;
               font-family: arial, helvetica, sans-serif;
               }

          .goneItems{
               transform: scale(0.5);
               }
          .relationBlockActive {
               border: 2px solid red !important;
               display: block !important;
               transform: scale(1);
               }
          .homeBlock{
               display: block!important;
               /*top: 0!important;*/
               transform: scale(1);
               }

          .half-bl > p {
               white-space: nowrap;
               overflow: hidden;
               font-size: 15px;
               }

          .half-bl.left-bl {
               padding-right: 5px;
               }

          .half-bl.right-bl {
               padding-left: 5px;
               }
          #db {
               position: relative;
               max-width: 100%;
               width: 100%;
               }
          .white-panel {
               overflow: hidden;
               border: 1px solid #C4C4C4;
               position: absolute;
               background: white;
               box-sizing: border-box;
               -moz-box-sizing: border-box;
               -o-box-sizing: border-box;
               -webkit-box-sizing: border-box;
               transition: all 1s ease 0s;
               -moz-transition: all 1s ease 0s;
               -o-transition: all 1s ease 0s;
               -webkit-transition: all 1s ease 0s;
               }
          .white-panel:hover {
               border-color: #000;
               }

          .line-block {
               float: left;
               width: 100%;
               }

          .header-bl p {
               text-align: center;
               }

          .title-bl {
               font-size: 30px;
               line-height: 40px;

               }

          .subtitle-bl {
               color: #BEBEBE;
               margin-bottom: 5px;
               }
          p {
               margin: 0;
               }

          .line-block.general-line {
               border-bottom: 1px solid #c4c4c4;
               position: relative;
               }


          .line-block.general-line:first-child {
               border-top: 1px solid #c4c4c4;
               }

          .half-bl {
               float: left;
               width: 50%;
               box-sizing: border-box;
               -moz-box-sizing: border-box;
               -o-box-sizing: border-box;
               -webkit-box-sizing: border-box;
               padding: 10px;
               }

          .label {
               position: absolute;
               top: -8px;
               left: 0;
               right: 0;
               margin: auto;
               display: block;
               width: 60px;
               background-color: white;
               padding-left: 10px;
               padding-right: 10px;
               text-align: center;
               color: #BEBEBE;
               }

          .half-bl.right-bl {
               color: #bebebe;
               text-align: right;
               }

          .label > p {
               font-size: 13px;
               cursor: pointer;
               }

          .label > p:hover {
               color: black;
               }
          .fieldsBlock {
               max-height: 200px;
               overflow: auto;
               padding-top: 10px;
               }

          .fieldsBlock .label {
               top: 0;
               }

          /*new*/

          .goneItems{
               display: none!important;
               }

          .homeBlock {
               display: block !important;
               left: 0 !important;
               top: 0 !important;
               }
          /*new*/


     </style>
</head>
<body>

<div class="container marketing">
     <section id="db">

          <?php

               foreach (Entity::each_managed_entity(null, null) as $m => $es) {
                    foreach ($es as $e) {
                        if ( $_GET['sys'] != 1 ) {
                            if ($e->is_system() xor $e->overlayed) continue;
                        }

                         ?>


                         <article class="white-panel">
                              <div class='line-block header-bl'>
                                   <p class="title-bl"><?= $e->name ?></p>

                                   <p class="subtitle-bl"><?= $e->title['ru'] ?></p>
                              </div>
                              <div class='general-lines'>
                                   <div class='line-block general-line'>
                                        <?php
                                             foreach ($e->statuses as $statusid) {
                                                  echo "<div class='label'><p>states</p></div>";
                                                  $status = Status::ref($statusid); //->name . " - " . Status::ref($statusid)->title;

                                                  ?>
                                                  <div data-belogngsto="" class='half-bl left-bl'>
                                                       <p><?= $status->name ?></p></div>
                                                  <div class='half-bl right-bl'><p><?= $status->title ?></p></div>
                                             <?php
                                             }
                                        ?>
                                   </div>

                                   <!--FIELDS-->
                                   <div class='line-block general-line'>
                                        <?
                                             if (count($e->general_fields()) > 0) {
                                                  echo "<div class='label'></p>fields</p></div>";
                                             }
                                        ?>
                                        <div class='fieldsBlock'>
                                             <?php
                                                  foreach ($e->general_fields() as $F) {
                                                       $fields = $F;

                                                       ?>
                                                       <div class='half-bl left-bl'>
                                                            <p><?= $fields->name . " :" . substr($F->type, 0, 1) ?></p></div>
                                                       <div class='half-bl right-bl'><p><?= $fields->title ?></p></div>
                                                       <div style="clear:both;"></div>
                                                  <?}?>
                                        </div>
                                   </div>
                                   <!--FIELDS-->


                                   <!--HAS ONE-->
                                   <?if(count($e->has_one())>0){?><div class='line-block general-line'><?}?>
                                        <?
                                             if (count($e->has_one()) > 0) {
                                                  echo "<div class='label has_one'><p>has one</p></div>";
                                             }
                                        ?>
                                        <div class='fieldsBlock'>
                                             <?php
                                                  $arr = "";
                                                  foreach ($e->has_one() as $as=>$usedas) {
                                                       $EHO = $usedas;
                                                       $arr .= $EHO->name." ";
                                                       ?>
                                                       <div class='half-bl left-bl'><p><?= ($as != $EHO->name) ? $as.'/'.$EHO->name : $EHO->name ?></p></div>
                                                       <div class='half-bl right-bl'><p><?= $EHO->title['ru'] ?></p></div>
                                                       <div style="clear:both;"></div>
                                                  <?}?>
                                             <span class="js_has_one" data-hasone="<?=$arr?>"></span>
                                             <?if(count($e->has_one())>0){?></div><?}?>
                                   </div>
                                   <!--HAS ONE-->

                                   <!--USE ONE-->
                                   <?if(count($e->use_one())>0){?><div class='line-block general-line'><?}?>
                                        <?
                                        if (count($e->use_one()) > 0) {
                                             echo "<div class='label has_one'><p>use one</p></div>";
                                        }
                                        ?>
                                        <div class='fieldsBlock'>
                                             <?php
                                             $arr = "";
                                             foreach ($e->use_one() as $as=>$usedas) {
                                                  $EHO = $usedas;
                                                  $arr .= $EHO->name." ";
                                                  ?>
                                                  <div class='half-bl left-bl'><p><?= ($as != $EHO->name) ? $as.'/'.$EHO->name : $EHO->name ?></p></div>
                                                  <div class='half-bl right-bl'><p><?= $EHO->title['ru'] ?></p></div>
                                                  <div style="clear:both;"></div>
                                             <?}?>
                                             <span class="js_has_one" data-hasone="<?=$arr?>"></span>
                                             <?if(count($e->use_one())>0){?></div><?}?>
                                   </div>
                                   <!--USE ONE-->


                                   <!--belongs_to-->
                                   <?if(count($e->belongs_to())>0){?><div class='line-block general-line'><?}?>
                                        <?php
                                             $arr = "";
                                             if (count($e->belongs_to()) > 0) {
                                                  echo "<div class='label belongs_to'><p>belongs to</p></div>";
                                             }
                                             foreach ($e->belongs_to() as $EBT) {
                                                  $bt = $EBT;
                                                  $arr .= $bt->name." ";
                                                  ?>

                                                  <div class='half-bl left-bl'><p><?= $bt->name ?></p></div>
                                                  <div class='half-bl right-bl'><p><?= $bt->title['ru'] ?></p></div>
                                                  <div style="clear:both;"></div>

                                             <?}?>
                                        <span class="js_belongs_to" data-belongs_to="<?=$arr?>"></span>
                                   <?if(count($e->belongs_to())>0){?></div><?}?>
                                   <!--belongs_to-->


                                   <!--has_many-->
                                   <?if(count($e->has_many())>0){?><div class='line-block general-line'><?}?>
                                        <?php
                                             $arr = "";
                                             if (count($e->has_many()) > 0) {
                                                  echo "<div class='label has_many'><p>has many</p></div>";
                                             }

                                             foreach ($e->has_many() as $EHM) {
                                             $hm = $EHM;
                                             $arr .= $hm->name." ";

                                        ?>
                                             <div class='half-bl left-bl'></p><?= $hm->name ?></p></div>
                                             <div class='half-bl right-bl'><p><?= $hm->title['ru'] ?></p></div>

                                             <div style="clear:both;"></div>

                                        <?}?>
                                        <span class="js_has_many" data-has_many="<?=$arr?>"></span>
                                   <?if(count($e->has_many())>0){?></div><?}?>
                                   <!--has_many-->


                                   <!--lists-->
                                   <?if(count($e->lists())>0){?><div class='line-block general-line'><?}?>
                                        <?php
                                             $arr = "";
                                             if (count($e->lists()) > 0) {
                                                  echo "<div class='label lists'><p>lists</p></div>";
                                             }
                                             foreach ($e->lists() as $list) {
                                                  $rel      = $list['entity'];
                                                  $listname = $list['name'];
                                                  $arr .= substr($rel, 4) ." ";

                                                  ?>


                                                  <div class='half-bl left-bl'><p><?= substr($rel, 4) ?></p></div>
                                                  <div class='half-bl right-bl'><p><?= $listname ?></p></div>

                                                  <div style="clear:both;"></div>

                                             <?}?>
                                        <span class="js_lists" data-lists="<?=$arr?>"></span>
                                   <?if(count($e->lists())>0){?></div><?}?>
                                   <!--lists-->


                              </div>

                         </article>
                    <?
                    }
               }
          ?>
     </section>
</div>

<script src="/lib/js/jQuery.min.js"></script>
<script src="/lib/js/pinterest_grid.js"></script>
<script>
     $(document).ready(function () {
          $('#db').pinterest_grid({
               no_columns: 6,
               padding_x: 15,
               padding_y: 15,
               margin_bottom: 50,
               single_column_breakpoint: 700
          });
     });
</script>

<script>
     function showRelations(relationClass,attr) {
          var blocks = document.getElementsByClassName("white-panel");
          var blocksLength = blocks.length;
          var has_one = document.getElementsByClassName(relationClass);
          var has_oneLength = has_one.length;
          var incNum = 1;

          for (var s = 0; s < has_oneLength; s++) {

               Event.add(has_one[s], 'click', function () {
                    var mainBlock = this.parentNode.parentNode.parentNode;
                    var relations = mainBlock.getElementsByClassName("js_"+relationClass)[0].getAttribute(attr).split(" ");
                    console.log("relations - " + relations);
                    var title = '';

                    for (var g = 0; g < blocksLength; g++) {
                         blocks[g].classList.remove("homeBlock","relationBlockActive");
                         blocks[g].classList.add("goneItems");
                    }
                    mainBlock.classList.add("homeBlock");

                    console.group("Titles");
                    for (var f = 0; f < blocksLength; f++) {
                         title = blocks[f].getElementsByClassName("title-bl")[0].innerHTML;


                         console.log(title);

                         if (relations.indexOf(title) == 0 || relations.indexOf(title) == 1) {

                              blocks[f].classList.remove("goneItems");
                              blocks[f].style.top = "0px";
                              blocks[f].style.left = parseFloat(blocks[f].offsetWidth)*incNum+20*incNum+"px";
                              incNum++;
                         }
                         if(f==(blocksLength-1)) incNum = 1;
                    }
                    console.groupEnd();
               });

               for(var t=0;t<has_one.length;t++){
                    Event.add(has_one[t],'dblclick',function(){
                         window.location.reload();
                    });
               }

          }
     }

     showRelations("has_one","data-hasone");
     showRelations("belongs_to","data-belongs_to");
     showRelations("has_many","data-has_many");
     showRelations("lists","data-lists");


</script>

</body>
</html>