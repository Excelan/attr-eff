<?php

class DMSDecisionSheetSignedPlugin extends RowPlugin
{

    public function stateofdecisions()
    {
        return json_encode($this->ROW->needsignfrom) . " / " . json_encode($this->ROW->hassignfrom) . " + " . json_encode($this->ROW->hascancelfrom);
    }

}

?>