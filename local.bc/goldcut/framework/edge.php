<?php
/**
EdgeList. 
	Traverse can return not Nodes but EdgeList of links to Nodes 
EdgeList to Listing (directedTo, directedFrom) if all links are of same entity 
*/
class Edge 
{
	public $nodeFrom;
	public $nodeTo;
	public $type;
	public $weight;
	public $metadata; // one level array
	public $created;
	
	public function __construct($nodeFrom, $nodeTo, $type=0, $weight=null, $metadata=null, $created=null)
	{
		$this->nodeFrom = $nodeFrom;
		$this->nodeTo = $nodeTo;
		$this->type = $type;
		$this->weight = $weight;
		$this->metadata = $metadata;
		$this->created = ($created) ? $created : time();
	}
	
	public function __toString()
	{
		$md = ($this->metadata) ? ' '.json_encode($this->metadata) : '';
		$type = ($this->type) ? '('.$this->type.') ' : '';
		return "$this->nodeFrom {$type}-{$this->weight}-> $this->nodeTo".$md;
	}
}

?>