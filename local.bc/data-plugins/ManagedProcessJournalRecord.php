<?php

class ManagedProcessJournalRecordPlugin extends RowPlugin
{
    public function adminview()
    {
        return "{$this->ROW->urn}";
    }

    public function dirview()
    {
        if ($this->ROW->stagedirection == "in")
            return "<span style='color: blue;'>-> IN</span>";
        else if ($this->ROW->stagedirection == "out")
            return "<span style='color: green;'><- OUT</span>";
        else
            return "NOT IN/OUT!";
    }
}

?>