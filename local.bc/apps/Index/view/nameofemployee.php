<?php
extract($this->context);


$m = new Message();
$m->action = 'load';
$m->urn = 'urn:Management:Post:Individual';
$posts = $m->deliver();


$m = new Message();
$m->action = 'load';
$m->urn = 'urn:People:Employee:Internal';
$employees = $m->deliver();

$i=0;
foreach($employees as $employee){

    foreach($posts as $post){

        if((string)$employee->ManagementPostIndividual->urn == (string)$post->urn){
            $m = new Message();
            $m->action = 'update';
            $m->urn = (string)$post->urn;
            $m->nameofemployee = $employee->title;
            $m->deliver();
        }
    }
}


?>
<h1>ADD "ФИО" width employee to post</h1>
