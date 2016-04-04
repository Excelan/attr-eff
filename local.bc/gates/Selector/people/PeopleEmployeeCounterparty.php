<?php

namespace Selector\people;

class PeopleEmployeeCounterparty extends \Gate
{

 function gate()
 {
  $data = $this->data;
  if(!is_array($data)){
    $data=$data->toArray();
  }

  \Log::info($data, 'ui');
  \Log::info($data['mpe'], 'ui');

  $m = new \Message();
  $m->action = 'load';
  $m->urn = 'urn:People:Employee:Counterparty'; //Исполнитель

    $mpeURN = new \URN($data['mpe']);
  $mpe = $mpeURN->resolve();
    $subjectURN = new \URN($mpe->subject);
  $subject = $subjectURN->resolve();
    $client = $subject->CompanyLegalEntityCounterparty;
    $m->CompanyLegalEntityCounterparty = $client->urn;

    \Log::info($m, 'ui');
  $data = $m->deliver();

  $arr = array();
  foreach($data as $d){
    array_push($arr,['value'=>(string)$d->urn,'title'=>$d->title]);
  }
  return ['options' => $arr];
}
}

?>
