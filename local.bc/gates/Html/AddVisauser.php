<?php
namespace Html;

class AddVisauser extends \Gate
{

	function gate()
	{
        //Преобразование входящих данных в удобный для обработки формат
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        \Log::info($data, 'capa-AddVisauser');

        $m = new \Message();
        $m->action = 'load';
        $m->urn = $data['user_urn'];
        $user = $m->deliver();

        $html =<<<HTML

        <div class='TM' data-struct='visausers' data-array='item'>
            <p class='name'>{$user->email}</p>
            <p class='post'>Менеджер по работе с клиентами, отдел по работе с клиентами</p>
            <input type="hidden" data-selector="visausers-visauser_urn" value="{$user->urn}">
        </div>
HTML;

        return ['visauser_html' => $html];
	}

}

?>