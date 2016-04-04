<?php

class FeedMPETicketInboxItemPlugin extends RowPlugin
{

    public function states()
    {
        return json_encode(["isvalid"=>$this->ROW->isvalid, "allowopen"=>$this->ROW->allowopen, "allowsave"=>$this->ROW->allowsave, "allowcomplete"=>$this->ROW->allowcomplete, "allowcomment"=>$this->ROW->allowcomment,
            "allowreadcomments"=>$this->ROW->allowreadcomments, "allowknowcuurentstage"=>$this->ROW->allowknowcuurentstage, "allowseejournal"=>$this->ROW->allowseejournal, "allowearly"=>$this->ROW->allowearly]);
    }

}

?>