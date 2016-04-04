<?php
extract($this->context);
$uri = $this->context;
?>

<header />
<nav class="topmenu">
    <div class="content">
        <ul class="nav">

            <?php
            foreach ($tabs as $tab)
            {
                if($uri['currentURI'] == $tab['link']) $active = 'active';
                echo "<li><a class='{$active}' href=\"{$tab['link']}\">{$tab['title']}</a></li>";
            }

            ?>

        </ul>

        <?php
        if ($floatingButton)
        {
            echo "<div class=\"floatingButton\"><a id='addNewComplaint' href=\"{$floatingButton['title']}\">+</a></div>";
        }
        ?>

    </div>
</nav>




<div class="main">

    <!--
    <div class="FR" style="margin-right: 40px;">
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="GET">
            <input name="q" type="search" value="<?= $_GET['q'] ?>"><input type="submit" value="Поиск">
        </form>
    </div>
    -->

    <?php
    if ($_COOKIE["filterby{$filtercontext}"])
    {
        ?>
        <div class="FR" style="margin-right: 10px;">
            <a id='clearfilters' data-filtercontext="<?= $filtercontext ?>" href="">Сбросить фильтры</a>
        </div>
    <?php
    }
    ?>

    <?php
    if ($tabletitle) echo "<h3>{$tabletitle}</h3>";
    ?>


    <?php

    foreach ($head as $key => $h)
    {
        if ($distinctgroups[$key])
        {
            $d = [];
            foreach ($distinctgroups[$key] as $de)
            {

                if ($key == 'marketplace') $d[$de] = externalData('marketplace')[$de]['name'];
                else if ($key == 'marketseller') $d[$de] = externalData('marketseller')[$de]['name'];
                else if ($key == 'crmclient_id') $d[$de] =  externalData('crmclient', (int)$de)['name'];
                else if ($key == 'buyer_id' || $key == 'seller_id' || $key == 'user_id') {
                    if (is_numeric($de))
//						$d[$de] = \URN::object_by("urn-user-$de")->adminview;
                        $de = "&mdash;";
                    else
                        $de = '&mdash;';
                }
                else if ($key == 'workfloworderout') {
                    $E = Entity::ref('orderout');
                    $F = $E->entityFieldByName('workfloworderout');
                    $val = $F->valueOfIndex((int)$de);
                    $d[$val] = $val;
                }
                else if ($key == 'workfloworderin') {
                    $E = Entity::ref('orderin');
                    $F = $E->entityFieldByName('workfloworderin');
                    $val = $F->valueOfIndex((int)$de);
                    $d[$val] = $val;
                }
                else $d[$de] = $de;
            }
            asort($d);
            $html = '<div class="filterselector hide BLK" data-for="'.$key.'">';
            foreach ($d as $dk => $dn)
            {
                $html .= "<p class='TOF'><a data-id='$dk' data-for='$key' data-filtercontext='$filtercontext' href='#filterby'>$dn</a></p>";
            }
            $html .= '</div>';
            print $html;
        }
    }

    ?>
    <div class="content">
        <section class="" style="clear: both;">
            <div class="container">
                <table class="data">
                    <thead>
                    <tr class="header">

                        <?php
                        foreach ($head as $key => $h)
                        {
                            $s = '';
                            $distinctClass = '';
                            if ($distinctgroups[$key])
                            {
                                $filters = json_decode($_COOKIE['filterby'.$filtercontext], true);
                                //$s = anyToString($distinctgroups[$key]);
                                $distinctClass = 'allowFilterSelect';
                                if (in_array($key, array_keys($filters))) $distinctClass .= ' allowFilterSelectSelected';
                            }
                            echo "<th class='$distinctClass' data-field='$key' data-distinct='$s'>$h</th>"; // mdl-data-table__cell--non-numeric
                        }
                        ?>

                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    //$dataGrouped = $data->group('crmclient_id', 'created');
                    //$dataGrouped = arraygroup($data, 2);
                    //foreach ($dataGrouped as $g => $drg)
                    //{
                    //foreach ($drg as $dr)
                    //{
                    foreach ($data as $dr)
                    {
                        $i = 0;
                        echo '<tr>' . "\n";
                        foreach ($dr as $d)
                        {
                            $i++;
                            $css = '';
                            $wrap = '';
                            $wrapclose = '';
                            if (is_array($d))
                            {
                                if ($d['wrapclass'] || $d['wrapstyle'] || $d['title'] || $d['font'])
                                {
                                    if ($d['font'] == 'bold') $d['wrapstyle'] .= 'font-weight: bold;';
                                    $wrap = "<p class='{$d['wrapclass']}' style='{$d['wrapstyle']}' title='{$d['title']}'>";
                                    $wrapclose = '</p>';
                                }
                                $ditem = (string)$d['data'];
                                // td css
                                if ($d['data']->tdcss) $css .= $d['data']->tdcss;
                                if ($d['css']) $css .= $d['css'];
                            } else
                            {
                                $ditem = $d;
                            }
                            if ($d['bgcolor']) $css .= 'background-color: ' . $d['bgcolor'] . ';';
                            if ($d['color']) $css .= 'color: ' . $d['color'] . ';';
                            // TR COLOR, TD COLOR !
                            echo "<td class='' style='$css'>{$wrap}$ditem{$wrapclose}</td>\n"; // mdl-data-table__cell--non-numeric td_addition
                        }
                        echo '</tr>' . "\n";
                        //}
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>


</div>

<br>
<div style="padding: 20px;">
    <?php
    //if ( $page > 1 ) echo "<a href='/orderout?page=$prev'>назад</a> ";
    for ($i = 1; $i <= $totalPages; $i++)
    {

        if ($i == $page)
        {
            echo "<b><a class='admpager' href='/{$filtercontext}?page=$i'>$i</a></b> ";
        } else
        {
            echo "<a class='admpager' href='/{$filtercontext}?page=$i'>$i</a> ";
        }
    }
    //if ( $page < $totalPages ) echo "<a href='/orderout?page=$next'>вперед</a>";
    ?>
</div>

<div class="faded content" style="font-size: 9px; color: #777; margin-top: 20px;">
    <?php echo "Время выборки <span style='color: {$loadtime['color']}'>{$loadtime['time']}</span> с"; ?>
</div>

<!--
<div id="tosatelement" style="display: none;">
    <div class="toast animated" style="display: none; opacity: 0; position: relative;">
        <div>
            <img id="segmentClose" style="" src="/img/close.png">
            <h4>СОЗДАТЬ ЖАЛОБУ</h4>
        </div>
        <form style="width: 300px; margin: 0 auto; text-align: center;" method="get" action='complaint/creationcomplaint' >
            <label class="toastlabel">Выберите тип жалобы(тест)</label>
            <div class="selectblock">
                <select required="required" class="FS15" id="" name="complainttype">
                    <option value="" selected="selected">Не выбрано</option>
                    <?
                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:boclass';
                    $complainttypes = $m->deliver();

                    foreach($complainttypes as $complainttype){
                        echo "<option value='$complainttype->id'>$complainttype->title</option>";
                    }
                    ?>
                </select>
            </div>
            <label class="toastlabel">Выберите тип жалобы(тест)</label>
            <div class="selectblock">
                <select required="required" class="FS15" id="" name="complainttype">
                    <option value="" selected="selected">Не выбрано</option>
                    <?
                    $m = new Message();
                    $m->action = 'load';
                    $m->urn = 'urn:boclass';
                    $complainttypes = $m->deliver();

                    foreach($complainttypes as $complainttype){
                        echo "<option value='$complainttype->id'>$complainttype->title</option>";
                    }
                    ?>
                </select>
            </div>
            <button style="margin: 40px 0" class="IBLK w200 GBTN" type="submit">Отправить</button>
        </form>
        <span></span>
    </div>
</div>
-->
<div id="tosatelement" style="display: none;">
    <div class="toast animated" style="display: none; opacity: 0; position: relative;">
        <div class="toasttop">
            <img id="segmentClose" style="" src="/img/close.png">

            <div class="leftblock" id="lb">
                <p class="item active">
                    sdfsdfsdf
                </p>
            </div>

            <div class="rightblock" id="rb">
                <p class="item">
                    sdfsdfsdf
                </p>
            </div>

        </div>
        <div id="toastleftblock">
            <form style="width: 400px; margin: 40px auto 0; text-align: left;" method="get" action='complaint/creationcomplaint' >
                <label class="toastlabel">Выберите тип жалобы(тест)</label>
                <div class="selectblock">
                    <select required="required" class="FS15" id="" name="complainttype">
                        <option value="" selected="selected">Не выбрано</option>
                    </select>
                </div>
                <label class="toastlabel">Выберите тип жалобы(тест)</label>
                <div class="selectblock">
                    <select required="required" class="FS15" id="" name="complainttype">
                        <option value="" selected="selected">Не выбрано</option>
                    </select>
                </div>
                <div class="toastradio">
                    <input type="checkbox" name="" id="c">
                    <label class="toastlabel" for='c'>Выберите тип жалобы(тест)</label>
                </div>
                <div class="insidelink">
                    <a href="#">Выберите тип жалобы Выберите тип жалобы Выберите тип жалобы</a>
                </div>
                <button style="margin: 40px 0" class="IBLK w200 GBTN" type="submit">Отправить</button>
            </form>
        </div>
        <div id="toastrightblock" style="display: none">
            <p class="tbox">
                Документ:
                <a href="#">sfdsfsd</a>
            </p>
            <div class="text">
                <p>
                    Выберите тип жалобы Выберите тип жалобы Выберите тип жалобыВыберите тип жалобы Выберите тип жалобы Выберите тип жалобы
                    Выберите тип жалобы Выберите тип жалобы Выберите тип жалобыВыберите тип жалобы Выберите тип жалобы Выберите тип жалобы
                    Выберите тип жалобы Выберите тип жалобы Выберите тип жалобыВыберите тип жалобы Выберите тип жалобы Выберите тип жалобы
                </p>
                <p>
                    Выберите тип жалобы Выберите тип жалобы Выберите тип жалобыВыберите тип жалобы Выберите тип жалобы Выберите тип жалобы
                </p>
                <p>
                    Выберите тип жалобы Выберите тип жалобы Выберите тип жалобыВыберите тип жалобы Выберите тип жалобы Выберите тип жалобы
                    Выберите тип жалобы Выберите тип жалобы Выберите тип жалобыВыберите тип жалобы Выберите тип жалобы Выберите тип жалобы
                </p>
            </div>
            <button style="margin: 40px 0" class="IBLK w200 GBTN" type="submit">Отправить</button>
        </div>
        <span></span>
    </div>
</div>


<script>
    addNewComplaint();

    function toast(x,y){
        var toast = document.getElementsByClassName('toast')[0];
        toast.style.display = 'block';
        toast.querySelectorAll('span')[0].innerHTML = x;
        setTimeout(function(){ toast.style.opacity = '1'},100);
    }

    function addNewComplaint() {
        var segm = document.getElementById('addNewComplaint');
        var segmTop = document.getElementById('tosatelement');
        var x = '';

        Event.add(segm, 'click', function (e) {
            e.preventDefault();
            segmTop.style.display = 'block';
            toast(x);
        });

        var segmentClose = document.getElementById('segmentClose');
        Event.add(segmentClose, 'click', function (e) {
            segmTop.style.display = 'none';
        });


        //переключение меню в модальном окне
        var toastleftblock = document.getElementById('toastleftblock');
        var toastrightblock = document.getElementById('toastrightblock');
        var lb = document.getElementById('lb');
        var rb = document.getElementById('rb');

        Event.add(lb, 'click', function (e) {
            toastleftblock.style.display = 'block';
            toastrightblock.style.display = 'none';
            this.querySelector('.item').className = 'active item';
            rb.querySelector('.item').className = 'item';
        });

        Event.add(rb, 'click', function (e) {
            toastrightblock.style.display = 'block';
            toastleftblock.style.display = 'none';
            this.querySelector('.item').className = 'active item';
            lb.querySelector('.item').className = 'item';
        });

    }

</script>