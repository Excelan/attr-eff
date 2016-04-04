<?php
namespace Comments;

class ChangeCommentStatus extends \Gate
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
        $m->action = 'update';
        $m->urn = $data['comment'];
        $m->appliedautor = $employee->id;
        $m->appliedstatus = $data['status'];
        $comments = $m->deliver();





        return ['status' => 501,'urn'=>$data['urn'], 'level'=>$data['level'],'idCapa'=>$data['idCapa']];
    }

}

?>