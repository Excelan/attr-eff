<?php

class GetcommentApp extends WebApplication implements ApplicationAccessManaged //ApplicationFreeAccess
{

    function request(){
        $this->context['user'] = $this->user;

        $this->layout = 'empty';
        if($_GET['level']) $level = $_GET['level'];
        if($_GET['urn']) $urn = $_GET['urn'];
        if($_GET['comment']) $urn = $_GET['comment'];
        if($_GET['idCapa']) $idCapa = $_GET['idCapa'];

        $this->register_widget('comments', 'comments', array(
            'urn'=>$urn,
            'user'=>$this->user->id,
            'level'=>$level,
            'idCapa'=>$idCapa,
            'readcomments'=>'FirstLevel'
        ));
    }

}