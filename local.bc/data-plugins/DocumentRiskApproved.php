<?php

class RiskManagementRiskApprovedPlugin extends RowPlugin
{

    public function adminview()
    {
        return $this->ROW->title;
    }

    public function title()
    {
        return $this->ROW->title;
    }


}

?>