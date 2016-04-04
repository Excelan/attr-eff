<?php
namespace Html;

class getPopupHtml extends \Gate
{

	function gate()
	{
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        \Log::info($data, 'html-getPopupHtml');

        $data_managedform = strtolower($data['method']) == "post" ? "data-managedform='yes'" : "";

        $select_data = "";

        if ( $data['type'] == 'user' ) {

            $m = new \Message();
            $m->action = 'load';
            $m->urn = 'urn:user';
            $user = $m->deliver();

            foreach ($user as $u) $select_data .= "<option value='{$u->urn}'>{$u->name} | {$u->email}</option>";

        } else if ( $data['type'] == 'document_type' ) {

            $select_data .= "
                <option value='complaint'>жалоба</option>
                <option value='internalprotocol'>служебное расcледование</option>
                <option value='meeting'>зеркало митинга</option>
                <option value='eductationalprogram'>зеркало программы обучения</option>
                <option value='capa'>капа</option>
                <option value='sop'>СОП</option>
                <option value='requisition'>СОП</option>
                <option value='contract'>Договор</option>";
        }

        $html =<<<HTML

        <div id="tosatelement">
            <div class="toast animated" style="display: none; opacity: 0; position: relative;">
                <div>
                    <img id="segmentClose" style="" src="/img/close.png">
                    <h4>{$data['header']}</h4>
                </div>
                <form style="width: 300px; margin: 0 auto; text-align: center;" action="{$data['action']}" method="{$data['method']}" {$data_managedform} data-onsuccess="{$data['onsuccess']}"  data-onerror="capaCreateError" >
                    <label class="IBLK FS15 FL" style="line-height: 30px; color: #000000;">{$data['content']}</label>
                    <select required="required" style="height: 30px; width: 300px" class="FS15" id="" name="{$data['param']}" data-selector="{$data['dataselector']}">
                        <option value="" selected="selected">Не выбрано</option>
                        {$select_data}
                    </select>
                    <button style="margin: 45px 0 0 0" class="IBLK w200 GBTN" type="submit">{$data['button']}</button>
                </form>
                <span></span>
            </div>
        </div>
HTML;

        return ['popup_html' => $html];
    }

}

?>