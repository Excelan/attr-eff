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
