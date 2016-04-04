<?php

//					$icon = 'chat';
//					$icon = 'delivered';
//					$icon = 'discussion';
//					$icon = 'inprogress';
//					$icon = 'new';
//					$icon = 'probleminprogress';
//					$icon = 'refund';
//					$icon = 'refundrequest';
//					$icon = 'sendmess';
//					$icon = 'shipped';
//                  $icon = 'star';
//					$icon = 'noinstock';
//					$icon = 'partlyinstock';
//					$icon = 'allinstock';
//					$icon = 'readytoship';

class Icon
{
    public $img;
    public $tdcss;
    public $title;
    function __construct($img, $title=null)
    {
        $this->img = $img;
        $this->title = $title;
    }
    function __toString()
    {

        $this->tdcss = 'width: 26px;';

        return "<img title='$this->title' src='/img/{$this->img}.png' width='22'>";
    }
}


class MenuItems
{
    public $items = array();
    function __construct($items)
    {
        $this->items = $items;
    }
    function __toString()
    {
        $s = '<div tabindex="0" class="onclick-menu"><ul class="onclick-menu-content">';
        foreach ($this->items as $mi)
        {
            $s .= "<li class=\"icon1\" style=\"width: auto; background-image: url(/goldcut/assets/icons/{$mi['icon']}.png)\"><button style='width: auto;' onclick=\"MENUFN['{$mi['func']}'].call(this, {$mi['param']})\">{$mi['title']}</button></li>";
        }
        $s .= '</ul></div>';

        $this->tdcss = 'width: 26px;';

        return $s;
    }
    function getTDcss() {
        return '';
    }
}


class IntLine
{
    public $items = array();
    public $tdcss;
    function __construct($items)
    {
        $this->items = $items;
    }
    function __toString()
    {
        $s = '<div class="stats4 BLK">';
        foreach ($this->items as $mi)
        {
            if ($mi == 0) $mi = '&mdash;';
            $s .= "<div class='fourstats FL'>{$mi}</div>";
        }
        $s .= '</div>';

        $this->tdcss = 'padding:0; margin:0; width: 130px; padding-left: 5px;';

        return $s;
    }
}

?>