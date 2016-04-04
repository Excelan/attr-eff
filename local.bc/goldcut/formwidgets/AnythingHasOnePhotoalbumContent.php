<?php 

class AnythingHasOnePhotoalbumContent extends FormWidget
{
	
	protected function build()
	{
	
		$ff = $this->subject->name;
		
		$ENTITY = Entity::ref($ff);
		$m = new Message();
		$m->urn = 'urn:'.$ENTITY->name;
		$m->action = 'load';
		$m->last = 1000; // !!!
		
		if ($ENTITY->defaultorder)
		{
			$m->order = $ENTITY->defaultorder;
		}
		$data = $m->deliver();
		
		//$this->setData($this->eo->$ff);
		$this->setData($data);
		
		$sel = $this->eo->$ff;
		if (count($sel))
		{
			$selected_urns = $sel->asURNs();
		}
	
		$this->html .= Form::category_selectbox($this->subject, $this->data, $selected_urns, true, false);
		
		
	}	
}	

?>