<?php
namespace Capa;

class ToVised extends \Gate
{

	function gate()
	{
		$data = $this->data;
		if(!is_array($data)){
			$data=$data->toArray();
		}

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

		$redirect = '/inbox';

		return ['status' => 501,'redirect'=>$redirect];
	}

}

?>