<?php
namespace Comments;

class AddNewComment extends \Gate
{

	function gate()
	{
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $m = new \Message();
        $m->action = 'load';
        $m->urn = 'urn:People:Employee:Internal';
        $m->ActorUserSystem = $data['userid'];
        $employee = $m->deliver();

        $m = new \Message();
        $m->action = 'create';
        $m->urn = "urn:Communication:Comment:Level2withEditingSuggestion";
        $m->document = $data['urn'];
        $m->appliedstatus = 'new';
        $m->content = $data['content'];
        if($data['replyto'])$m->replyto = $data['replyto'];
        $m->autor = $employee->id;
        if($data['toreplyto']) $m->toreplyto = $data['toreplyto'];

        $comments = $m->deliver();





		return ['status' => 501,'urn'=>$data['urn'], 'level'=>$data['level'],'idCapa'=>$data['idCapa']];
	}

}

?>