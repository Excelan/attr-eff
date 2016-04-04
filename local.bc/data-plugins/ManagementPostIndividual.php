<?php

class ManagementPostIndividualPlugin extends RowPlugin
{

    public function adminview()
    {
      $employee = $this->ROW->employee;
      if (count($employee) == 1)
      {
        $ActorUserSystem = $employee->ActorUserSystem;
        if (count($ActorUserSystem) == 1)
        {
          $email = $ActorUserSystem->email;
        }
        elseif (count($ActorUserSystem) > 1)
        {
          $email = "MULTIPLE ActorUserSystem PER EMPLOYEE!";
        }
      }
      else if (count($employee) > 1)
      {
        $email = "MULTIPLE EMPLOYEE PER POST!";
      }
      return "{$this->ROW->id}/{$this->ROW->title} ({$email}))";
    }

    public function employee()
    {
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:People:Employee:Internal';
        $m->ManagementPostIndividual = $this->ROW->urn;
        return $m->deliver();
    }

    public function PeopleEmployeeInternal()
    {
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:People:Employee:Internal';
        $m->ManagementPostIndividual = $this->ROW->urn;
        return $m->deliver();
    }

}

?>
