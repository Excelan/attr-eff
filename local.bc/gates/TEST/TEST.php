<?php
namespace TEST;

class TEST extends \Gate
{

	function gate()
	{
        $data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

		return ['data' => json_Encod_Decod($data)];
	}

}

?>