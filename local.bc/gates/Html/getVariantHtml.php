<?php
namespace Html;

class getVariantHtml extends \Gate
{

	function gate()
	{
        $current_date = date('Y-m-d');

        $rand_variant = "variant_".mt_rand(0, 10000);

        $html =<<<HTML

        <div class="entry BLK RM0 LM0" data-struct='events-variants' data-array='item'>
             <div class='entry vam'>
                <label class='IBLK w3mw FS15'>Тип реализации:</label>
                <select data-selector="events-variants-realization" class='FS15 IBLK w5' data-select='{$rand_variant}' onchange=show_hide_price(this)>
                    <option selected="selected" value="">Не определен</option>
                    <option value='without_contractor_without_money'>Без подрядчиков и покупки материалов</option>
                    <option value='without_contractor_with_money'>Без подрядчиков с покупкой материала</option>
                    <option value='with_contractor_without_money'>С подрядчиками без покупки материала</option>
                    <option value='with_contractor_with_money'>С подрядчиками с покупкой материала</option>
                </select>
             </div>
            <div class="entry vam">
                <label class="IBLK w3mw FS15">Дата реализации:</label>
                <select class="FS15 IBLK w5" data-selector="events-variants-realizationdate">
                    <option selected="selected">{$current_date}</option>
                </select>
            </div>
            <div class="entry vam">
                <label class="TM w3mw IBLK vt BLD FS15">Описание реализации:</label>
                <textarea placeholder="Починить схему" data-selector="events-variants-description" required="required" class="TM w5 IBLK vt"></textarea>
            </div>
            <div class="entry vam" data-variant="{$rand_variant}" style="display: none">
                <label class="IBLK w3mw FS15">Стоимость:</label>
                <input type="text" placeholder="Стоимость" data-selector="events-variants-cost" class="FS15 IBLK w5">
            </div>
        </div>
        <div class="lineborder"><div><img class="CP" src="/img/arrow.jpg"><img class="CP" src="/img/close.jpg"></div></div>

HTML;

        return ['variant_html' => $html];
	}

}

?>