<?php
extract($this->context);
?>

<header />


<div class="main">
    <div class="content">
        <div class="FR" style="margin: 15px 40px; 10px 0">
            <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="GET">
                <input name="q" type="search" value="<?= $_GET['q'] ?>"><input type="submit" value="Поиск">
            </form>
        </div>
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
                    $d[$de] = $de;
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

<div class="faded" style="font-size: 9px; color: #777; margin-top: 20px;margin-left: 20px;">
    <?php echo "Время выборки <span style='color: {$loadtime['color']}'>{$loadtime['time']}</span> с"; ?>
</div>