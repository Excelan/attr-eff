<?php
namespace Html;

class getProblemHtml extends \Gate
{

	function gate()
	{
		$data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        \Log::info($data, 'html-getProblemHtml');

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:problemscope';
        $problemscope = $m->deliver();

        $problemscope_html = "";
        foreach ($problemscope as $ps) {
            $problemscope_html .= "<option value='{$ps->urn}'>{$ps->title}</option>";
        }

        $risk_html = "";
/*        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn-risk';
        $risk = $m->deliver();


        foreach ($risk as $r) {
            $risk_html .= "<option value='{$r->urn}'>{$r->urn}</option>";
        }*/

        $problem_rand_code = mt_rand(0, 1000000);

        $html =<<<HTML

<div class='BLK minheight200 BM4' data-struct='problems' data-array='item'>

        <div class="BLK widget">

            <div class="entry BLK">
                <div class="entry vam">
                    <label for="problemscope_id" class="IBLK w3mw FS14">Сфера проблемы</label>
                    <select data-selector="problems-problemscope_urn" name="problemscope_urn" id="problemscope_urn" class="FS14 IBLK w5">
                        <option selected="selected" value="NULL">Не определена</option>
                        {$problemscope_html}
                    </select>
                </div>
            </div>

            <div class="entry">
                <label class="TM w3mw IBLK vt BLD FS14">Описание проблемы</label>
                <textarea class='TM w5 IBLK vt' required="required" id='description' data-selector='problems-description' placeholder="Введите описание проблемы"></textarea>
            </div>

            <div class="entry BLK">
                <div class="entry vam">
                    <label for="risk_urn" class="IBLK w3mw FS14">Определите тип риска</label>
                    <select data-selector="problems-risk_urn" name="risk_urn" id="risk_urn" class="FS14 IBLK w5">
                        <option selected="selected" value="NULL">Не определен</option>
                        {$risk_html}
                    </select>
                </div>
            </div>

            <div class="BLK TM3 BP3">


                <div class="titleinpart">
                    Список мероприятий
                    <a href="#" class="addNewEvent adda" id="addNewEvent" data-problem="{$problem_rand_code}">+ Добавить мероприятие</a>
                </div>


                    <div class="event_listing" id="event_listing" data-problem="{$problem_rand_code}" data-multiplestruct="problems-events"></div>

            </div>

        </div>

</div>

HTML;

		return ['problem_html' => $html, 'problem_rand_code' => $problem_rand_code];
	}

}

?>