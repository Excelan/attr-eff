<?php
namespace Selector\document\risks;

class RiskApproved extends \Gate
{

	function gate()
	{
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:RiskManagement:Risk:Approved'; //Идентифицированный риск
        $risks = $m->deliver();

        $arrRisk = array();
        foreach($risks as $risk){
            array_push($arrRisk,['value'=>$risk->urn,'title'=>$risk->title]);
        }



        return [
            'options' => $arrRisk
        ];
	}

}

?>