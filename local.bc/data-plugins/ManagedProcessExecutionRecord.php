<?php

class ManagedProcessExecutionRecordPlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->id} {$this->ROW->prototype} {$this->ROW->done} {$this->ROW->currentstage} {$this->ROW->subject}";
    }

}

?>