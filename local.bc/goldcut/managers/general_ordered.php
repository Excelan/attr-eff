<?php 
/**

Double linked list. prev, next.

simple list - int order
full list - graph links to left, right
deep list - + links to parent, children
tree not graph cs of only 1 parent int tree nodes 
*/
class General_Ordered
{
	public function reorder($message)
	{
		$i = 0;
		foreach ($message->order->toArray() as $item)
		{
			//printlnd($item);
			assertURN($item);
			$m = new Message();
			$m->urn = $item;
			$m->action = 'update';
			$m->ordered = ++$i;
			$m->deliver();
		}
		$rm = new Message();
		$rm->status = 200;
		$rm->urn = $m->urn->generalize();
		return $rm;
	}
	
	function move($message) {}
	
	/**
	linked 1-2-3. on del 2 > link 1-3
	behavior - on zfs snapshot delete 2 > remove newer - delete 3 (cascade) 
	*/
	function after_crud_remove($message) {}
		
}
?>