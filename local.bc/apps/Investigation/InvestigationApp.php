<?php

class InvestigationApp extends WebApplication implements ApplicationFreeAccess //ApplicationAccessManaged
{

    function request() {
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследования етап1'));
    }

    function resource($id) {

    }

    function step1() {
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследования етап1'));
    }

    function step2() {
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследование етап2'));
    }

    function step3() {
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследование етап3'));
    }

    function step4() {
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследование етап4'));
    }

    function step5() {
        $this->register_widget('header', 'header', array('stage'=>'Визирование','number'=>'RD_N 125','name'=>'Протокол служебного расследование етап5'));
    }
}