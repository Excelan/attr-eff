<?php 
class OEmbed
{
	public static function processText($text)
	{
		$oeps = Config::value("/manager/oembed/providers");//->structs(array('name'));
		foreach ($oeps->structs() as $name => $oep)
		{
			foreach ($oep->urlschemes as $usm)
			{
				$host = parse_url($usm, PHP_URL_HOST);
				$hosts[] = $host;
				$hostRev[$host] = $name;
			}
		}
		
	   $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
	   $callback = function($matches) use ($hosts, $hostRev) 
	   {
		   $url = array_shift($matches);
		   $host = parse_url($url, PHP_URL_HOST);
		   if (in_array($host, $hosts))
		   {
			$oep = Config::value("/manager/oembed/providers.{$hostRev[$host]}");
			$endpointRequest = $oep->endpoint.'?url='.$url.'&format=json'; // &maxwidth=600
			$embedT = file_get_contents($endpointRequest); // httpRequest problems on twitter
			//var_dump($endpointRequest, $embedT);
			$embed = json_decode($embedT);
			if (!count($embed)) $embed = simplexml_load_string($embedT);
			if ($embed->type == 'rich' || $embed->type == 'video')
				return $embed->html;
			else if ($embed->type == 'photo')
				return "<img src='{$embed->url}' class=\"oembed\">";
			else if ($embed->type == 'link')
				return $embed->url;
			else 
				return json_encode($embed);
		   }
		   else return $url;
	   };
	   return preg_replace_callback($pattern, $callback, $text);
	}

}
?>