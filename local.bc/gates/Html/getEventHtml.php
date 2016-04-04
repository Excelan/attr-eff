<?php
namespace Html;

class getEventHtml extends \Gate
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
        $m->urn = 'urn:department';
        $department = $m->deliver();

        $department_html = "";
        foreach ($department as $d) {
            $department_html .= "<option value='{$d->urn}'>{$d->title}</option>";
        }

        $object_html = "";

/*        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn-object';
        $object = $m->deliver();

        foreach ($object as $o) {
            $object_html .= "<option value='{$o->urn}'>{$o->name}</option>";
        }*/


        $html =<<<HTML

<div class='BLK minheight200 productsInfo' data-struct='problems-events' data-array='item'>

    <!--<div class="g6 BLK TM1"></div>-->
    <div class="">

        <div class="BLK TM1">

            <div class="entry">
                <label class="TM w3mw IBLK vt BLD FS15">Описание мероприятия</label>
                <textarea class='TM w5 IBLK vt' required="required" type='text' id='description' data-selector='problems-events-description'> </textarea>
            </div>
            <div class="TM BLK">
                <div class="entry vam">
                    <label class="IBLK w3mw FS15" for="department_id">Ответственный департамент</label>
                    <select data-selector="problems-events-department_urn" name="department_urn" id="department_urn" class="FS15 IBLK w5">
                        <option selected="selected" value="NULL">Не определен</option>
                        {$department_html}
                    </select>
                </div>
            </div>

            <div class="entry BLK">
                <div class="entry vam">
                    <label class="IBLK w3mw FS15" for="object_urn">Место проведения мероприятия</label>
                    <select data-selector="problems-events-object_urn" name="object_urn" id="object_urn" class="FS15 IBLK w5">
                        <option selected="selected" value="NULL">Не определено</option>
                        {$object_html}
                    </select>
                </div>
            </div>
        </div>
        <div class="lineborder"><div><img src="/img/arrow.png" class="CP"><img src="/img/close.png" class="CP"></div></div>
    </div>

</div>

HTML;

        return ['event_html' => $html];

	}

}

?>