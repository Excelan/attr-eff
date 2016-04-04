<?php

class DashboardUserApp extends WebApplication implements ApplicationAccessManaged
{
    public function request()
    {
        $this->view = false;

        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:Management:Post:Individual';
        $m->order = 'title ASC';
        $posts = $m->deliver();
        echo '<table cellspacing=0 border=1 bordercolor=#DDDDDD>';
        foreach ($posts as $post) {
            $userT = null;
            $ManagementPostGroupT = null;
            $employeeT = null;
            $managedbypostT = null;
            $CompanyStructureDepartmentT = null;

            $ManagementPostGroup = $post->ManagementPostGroup;
            if (count($ManagementPostGroup) == 1) {
                $ManagementPostGroupT = $ManagementPostGroup->title;
            } elseif (count($ManagementPostGroup) > 1) {
                $ManagementPostGroupT = '<font color=red>ManagementPostGroup count > 1</font>';
            } else {
                $ManagementPostGroupT = '<font color=orange>no ManagementPostGroup</font>';
            }

            $employee = $post->employee;
            if (count($employee) == 1) {
                $employeeT = $employee->title;

                $user = $employee->ActorUserSystem;
                if (count($user) == 1) {
                    $userT = $user->email;
                } elseif (count($user) > 1) {
                    $userT = '<font color=red>employee.user count > 1</font>';
                } else {
                    $userT = '<font color=orange>no employee.user</font>';
                }
            } elseif (count($employee) > 1) {
                $employeeT = '<font color=red>employee count > 1</font>';
            } else {
                $employeeT = '<font color=orange>no employee</font>';
            }

            $managedbypost = $post->managedbypost;
            if (count($managedbypost) == 1) {
                $managedbypostT = $managedbypost->title;
            } elseif (count($managedbypost) > 1) {
                $managedbypostT = '<font color=red>managedbypost count > 1</font>';
            } else {
                $managedbypostT = '<font color=orange>нет начальника</font>';
            }

            $CompanyStructureDepartment = $post->CompanyStructureDepartment;
            if (count($CompanyStructureDepartment) == 1) {
                $CompanyStructureDepartmentT = $CompanyStructureDepartment->title;
            } elseif (count($CompanyStructureDepartment) > 1) {
                $CompanyStructureDepartmentT = '<font color=red>$CompanyStructureDepartment count > 1</font>';
            } else {
                $CompanyStructureDepartmentT = '<font color=orange>no $CompanyStructureDepartment</font>';
            }

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Feed:MPETicket:InboxItem';
            $m->ManagementPostIndividual = $post->urn; // Должность
            $m->isvalid = true; // только активные
            $m->order = 'activateat DESC';
            $tickets = $m->deliver();
            $ticketT = '';
            if (count($tickets)) {
                $ticketT = count($tickets);
            }

            print("<tr><td>{$post->title}</td><td>{$ManagementPostGroupT}</td><td>{$CompanyStructureDepartmentT}</td><td>{$managedbypostT}</td><td>{$employeeT}</td><td>{$userT}</td><td>{$ticketT}</td></tr>");
        }
        echo '</table>';
    }
}
