<?php
/**
Default domain for such ENV
Default scheme (SSL, non SSL) for such app /uri
*/
class URL
{
	public $url;
	
	function __construct($url)
	{
		if (substr($url,0,4) != 'http' && substr($url,0,1) != '/')
			$url = 'http://'.$url;	
		$pu = parse_url($url);
		foreach ($pu as $k=>$v) $this->$k = $v;
		if (!$this->host) 
		{
			$this->scheme = 'http';
			$domain = Config::get('site', 'passport.domain');
			$this->host = $domain;
			$env = Config::get('site', 'env.current');
			if ($env == 'development')
			{
				$subdomain = Config::get('site', 'env.development.subdomain');
				$this->host = $subdomain . '.' . $this->host;
			}
			
		}
		if ($this->query) parse_str($this->query, $this->params);
		$this->build();
	}
	
	public function __toString()
	{
		return $this->url;
	}
	
	private function build()
	{
		$this->url = $this->scheme .'://'. $this->host . $this->path;
		if ($this->params)
		{
			foreach($this->params as $key => $key_value) $query_array[] = $key . '=' . urlencode($key_value);
			$this->query = join('&', $query_array);
		}
		if ($this->query) $this->url .= '?'.$this->query;
	}
	
	function getDomain()
	{
		return $this->host;
	}
	
	function setScheme($scheme)
	{
		$this->scheme = $scheme;
		$this->build();
	}
	
	function setParam($param, $value)
	{
		$this->params[$param] = $value;
		$this->build();
	}
	
	function setParamIfExists($param, $value)
	{
		if ($this->params[$param])
		{
			$this->params[$param] = $value;
			$this->build();
		}
	}
	
	function clearQuery()
	{
		$this->query = null;
		unset($this->query);
		$this->params = null;
		unset($this->params);
		$this->build();
	}
	
	function clearPathAndQuery()
	{
		$this->query = null;
		unset($this->query);
		$this->params = null;
		unset($this->params);
		$this->path = null;
		unset($this->path);
		$this->build();
	}
}	
?>