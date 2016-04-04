<?php

class UISelectDataControl extends AjaxApplication implements ApplicationAccessManaged
{
    function exclusive($path)
    {
        // by rich select Types (Document, RiskManagementRiskApproved, BusinessObject, CompanyLegalEntityCounterparty, ManagementPostIndividual)
        // metadata - fields - equal in all type data (per rich select Type)
        // data - load per user / process stage/form
        // fallback data - all in table (no user/stage rbac)

        Log::info((string) $this->message, 'uiselect');

        $fn = 'UISELECTOR_METADATA_'.$this->message->richtype;
        if ($GLOBALS[$fn])
        {
            $metadata = $GLOBALS[$fn]();
        }
        else
            throw new Exception("$fn not exists");

        $fn = 'UISELECTOR_DATA_'.$this->message->richtype;
        $fnF = 'UISELECTOR_DATA_INVARIANT_'.$this->message->richtype;
        if ($GLOBALS[$fn])
        {
            $data = $GLOBALS[$fn]($this->message->mpe); // this->user, management role, external
        }
        elseif ($GLOBALS[$fnF])
        {
            $data = $GLOBALS[$fnF]($this->message->mpe);
        }
        else
            throw new Exception("$fn or UISELECTOR_DATA_FALLBACK not exists");

        // combine
        $d = [
            "metadata" => $metadata,
            "data" => $data
        ];

        Log::info(json_encode($d), 'uiselect');
        return json_encode($d);
    }
}

?>