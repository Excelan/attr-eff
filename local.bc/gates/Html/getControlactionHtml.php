<?php
namespace Html;

class getControlactionHtml extends \Gate
{

	function gate()
	{
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        \Log::info($data, 'html-getControlactionHtml');

        $html =<<<HTML

            <div data-struct='controlactions' data-array='item'>

                <div class="RM0 LM0 entry">
                    <label class="TM w3mw IBLK vt BLD FS15">Контролирующее действие #1:</label>
                    <textarea class="TM w5 IBLK vt" data-selector="controlactions-description" placeholder="Опишиите меры принятые для устранения проблемы"></textarea>
                </div>
                <div class="entry vam">
                    <label for="" class="IBLK w3mw FS15">Периодичность контроля #1:</label>
                    <select data-selector="controlactions-periodicity" name="" id="" class="FS15 IBLK w5">
                        <option selected="selected">Не определен</option>
                    </select>
                </div>

            </div>

HTML;

        return ['controlaction_html' => $html];
	}

}

?>