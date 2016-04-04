<?php
namespace Events;

class NextStage extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}


		if(!$data['mpeid']) return ['status' => 404];

		// Call Java complete stage
		$url = 'http://localhost:8020/completestage/?upn=UPN:P:P:P:' . $data['mpeid'];
		try {
			$r = httpRequest($url, null, [], 'GET', 5);
			\Log::debug($r, 'decision');
		}
		catch (\Exception $e)
		{
			\Log::debug($e->getMessage(), 'decision');
		}


		return ['status' => 501];
	}

}

?>