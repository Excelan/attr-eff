<?php 
class General_Graph
{

    public function loadedge($message)
    {
        return EntityGraph::edgeLoad($message);
    }

	public function hasedge($message)
	{
		return EntityGraph::edgeExists($message);
	}
	
	public function newedge($message)
	{
		$response = EntityGraph::edgeCreate($message);
		return $response;
	}
	
	public function unedge($message)
	{
		$response = EntityGraph::edgeRemove($message);
		return $response;
	}
	
	public function unedgeall($message)
	{
		$response = EntityGraph::edgesRemoveAll($message);
		return $response;
	}
	
	public function edgedata($message)
	{
		$response = EntityGraph::edgeSetData($message);
		return $response;
	}

	public function edgesfrom($message)
	{
		return EntityGraph::edgesFrom($message);
	}
	
	public function edgesto($message)
	{
		return EntityGraph::edgesTo($message);
	}
	
	public function edgesbytype($message)
	{
		return EntityGraph::edgesByType($message);
	}
	
	public function edgesbydata($message)
	{
		return EntityGraph::edgesByData($message);
	}
	
}
?>