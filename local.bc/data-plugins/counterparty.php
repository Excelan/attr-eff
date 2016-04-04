<?php

class CounterpartyPlugin extends RowPlugin
{

    public function adminview()
    {
        return $this->ROW->counterparty;
    }

}

?>